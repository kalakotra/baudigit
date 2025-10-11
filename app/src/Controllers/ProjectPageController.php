<?php

namespace app\Controllers;

use app\Models\Project;
use app\Models\Client;
use app\Models\ClientObject;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\DataList;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\GroupedDropdownField;

class ProjectPageController extends \PageController
{
    private static $allowed_actions = [
        'add',
        'edit'
    ];

    public function readAllInputs() {
        return Project::get();
    }

    public function actProject() {
        $id = $this->checkEdit();
        if ($id && is_numeric($id)) {
            $project = Project::get()->byID($id);
            if ($project) {
                return $project;
            }
        }

        return false;
    }

    public function add() {
        $allClients = Client::get()->map('ID', 'Company')->toArray();
        $clientGroupedList = [];
        foreach ($allClients as $id => $company) {
            $clientObjects = ClientObject::get()->filter('ClientID', $id);
            if ($clientObjects->count() > 0) {
                $clientGroupedList[$company] = $clientObjects->map('ID', 'getFullName')->toArray();
            }
        }

        $formFields = FieldList::create(
            TextField::create('Title', 'Titel')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextareaField::create('Description', 'Beschreibung')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setRows(4),
            GroupedDropdownField::create('ClientObjectID', 'Kunde', $clientGroupedList)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select')->setEmptyString('-- bitte wählen --'),
            DropdownField::create('Status', 'Status', [
                0 => 'Neu',
                1 => 'Laufend',
                2 => 'Abgeschlossen',
                3 => 'Abgerechnet',
                4 => 'Storniert',
            ])->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select')->setValue(0)
        );

        $action = FieldList::create(
            FormAction::create('doAdd', 'Speichern')->addExtraClass('btn btn-primary px-4  mb-2')
        );

        $required = RequiredFields::create([
            'Title',
            'ClientObjectID',
        ]);

        $form = Form::create($this, 'add', $formFields, $action, $required);

        return $form;
    }

    public function doAdd($data, $form) {
        $project = new Project();
        $form->saveInto($project);
        $project->write();

        // redirect to edit page of the newly created project
        return $this->redirect($this->Link('?e=' . $project->ID));
    }

    public function edit() {
        $project = $this->actProject();
        if ($project) {
            $addForm = $this->add();

            // add hidden field with id
            $addForm->Fields()->push(
                HiddenField::create('e', 'ID')->setValue($project->ID)
            );

            $action = FieldList::create(
                FormAction::create('doEdit', 'Speichern')->addExtraClass('btn btn-primary px-4 mb-2')
            );

            $required = RequiredFields::create([
                'Title',
                'ClientObjectID',
            ]);

            $form = Form::create($this, 'edit', $addForm->Fields(), $action, $required);

            $form->loadDataFrom($project);

            return $form;
        }
    }

    public function doEdit($data, $form) {
        $project = $this->actProject();
        if ($project) {
            $form->saveInto($project);
            $project->write();

            $form->sessionMessage('Projekt erfolgreich gespeichert.', 'good');
            return $this->redirectBack();
        }

        $form->sessionMessage('Fehler beim Speichern des Projekts.', 'bad');
        return $this->redirectBack();
    }
}