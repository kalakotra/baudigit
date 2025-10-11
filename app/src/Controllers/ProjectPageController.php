<?php

namespace app\Controllers;

use app\Models\Client;
use app\Models\Project;
use app\Models\ClientObject;
use SilverStripe\Forms\Form;
use app\Models\ProjectMember;
use SilverStripe\Assets\File;
use SilverStripe\ORM\DataList;
use app\Models\ProjectDocument;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\TextField;
use SilverStripe\Security\Member;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\GroupedDropdownField;
use UncleCheese\Dropzone\FileAttachmentField;
use SilverStripe\AssetAdmin\Forms\UploadField;

class ProjectPageController extends \PageController
{
    private static $allowed_actions = [
        'add',
        'edit',
        'addMemberForm',
        'editMemberForm',
        'deleteMember',
        'addDocumentForm',
        'editDocumentForm',
        'deleteDocument'
    ];

    public function readAllInputs() {
        return Project::get();
    }

    public function checkActiveAccordion() {
        if ($this->checkEditDocument()) {
            return 'documents';
        } else if ($this->checkEditMember()) {
            return 'members';
        } else {
            return 'details';
        }
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
            DateField::create('StartDate', 'Startdatum')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            DateField::create('EndDate', 'Enddatum')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
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

    public function getAllProjectMembers() {
        $project = $this->actProject();
        if ($project) {
            return $project->ProjectMembers();
        }
        
        return false;
    }

    public function checkEditMember() {
        if ($this->getRequest()->getVar('em')) {
            return $this->getRequest()->getVar('em');
        } else if ($this->getRequest()->postVar('em')) {
            return $this->getRequest()->postVar('em');
        }
        return false;
    }

    public function addMemberForm() {
        $project = $this->actProject();
        if (!$project) {
            return false;
        }

        // Get available members, only from worker group
        $allMembers = Member::get()->filter('Groups.ID', '3')->map('ID', 'Name')->toArray();

        // min and max date based on project start and end date; format dd.mm.yyyy; if no end date use +5 year
        $minDate =  $project->StartDate ? $project->StartDate : date('Y-m-d');
        $maxDate = $project->EndDate ? $project->EndDate : date('Y-m-d', strtotime('+5 year'));
        $formFields = FieldList::create(
            DropdownField::create('MemberID', 'Mitarbeiter', $allMembers)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select')->setEmptyString('-- bitte wählen --'),
            DateField::create('StartDate', 'Startdatum')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setMinDate($minDate)->setMaxDate($maxDate),
            DateField::create('EndDate', 'Enddatum')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setMinDate($minDate)->setMaxDate($maxDate),
            HiddenField::create('e', 'Projekt')->setValue($this->checkEdit())
        );

        $action = FieldList::create(
            FormAction::create('doAddMember', 'Hinzufügen')->addExtraClass('btn btn-primary px-4 mb-2')
        );
        
        $required = RequiredFields::create([
            'MemberID',
            'StartDate',
            'EndDate'
        ]);

        $form = Form::create($this, 'addMemberForm', $formFields, $action, $required);

        return $form;
    }

    public function doAddMember($data, $form) {
        $project = $this->actProject();
        if ($project) {
            $projectMember = new ProjectMember();
            $projectMember->ProjectID = $project->ID;
            $projectMember->MemberID = $data['MemberID'];
            $projectMember->StartDate = $data['StartDate'];
            $projectMember->EndDate = $data['EndDate'];
            
            // Handle work days
            if (isset($data['WorkDaysArray']) && is_array($data['WorkDaysArray'])) {
                $projectMember->WorkDays = implode(',', $data['WorkDaysArray']);
            } else {
                $projectMember->WorkDays = '1,2,3,4,5'; // Default Monday-Friday
            }
            
            $projectMember->write();

            $form->sessionMessage('Mitarbeiter erfolgreich hinzugefügt.', 'good');
            return $this->redirect($this->Link('?e=' . $project->ID.'&em=' . $projectMember->ID));
        }
        
        $form->sessionMessage('Fehler beim Hinzufügen des Mitarbeiters.', 'bad');
        return $this->redirectBack();
    }

    public function editMemberForm() {
        $id = $this->checkEditMember();
        if ($id && is_numeric($id)) {
            $projectMember = ProjectMember::get()->byID($id);
            if ($projectMember) {
                $allMembers = Member::get()->map('ID', 'Name')->toArray();
                $selectedDays = $projectMember->WorkDays ? explode(',', $projectMember->WorkDays) : ['1', '2', '3', '4', '5'];

                $addFields = $this->addMemberForm()->Fields();
                $formFields = $addFields;
                $formFields->push(
                    HiddenField::create('em', 'ID')->setValue($id)
                );

                $action = FieldList::create(
                    FormAction::create('doEditMember', 'Speichern')->addExtraClass('btn btn-primary px-4 mb-2')
                );
                
                $required = RequiredFields::create([
                    'MemberID',
                    'StartDate',
                    'EndDate'
                ]);

                $form = Form::create($this, 'editMemberForm', $formFields, $action, $required);
                $form->loadDataFrom($projectMember);

                return $form;
            }
        }
    }

    public function doEditMember($data, $form) {
        $id = $this->checkEditMember();
        if ($id && is_numeric($id)) {
            $projectMember = ProjectMember::get()->byID($id);
            if ($projectMember) {
                $projectMember->MemberID = $data['MemberID'];
                $projectMember->StartDate = $data['StartDate'];
                $projectMember->EndDate = $data['EndDate'];
                
                // Handle work days
                if (isset($data['WorkDaysArray']) && is_array($data['WorkDaysArray'])) {
                    $projectMember->WorkDays = implode(',', $data['WorkDaysArray']);
                } else {
                    $projectMember->WorkDays = '1,2,3,4,5'; // Default Monday-Friday
                }
                
                $projectMember->write();

                $form->sessionMessage('Mitarbeiter erfolgreich gespeichert.', 'good');
                return $this->redirect($this->Link('?e=' . $projectMember->ProjectID.'&em=' . $projectMember->ID));
            }
        }
        
        $form->sessionMessage('Fehler beim Speichern des Mitarbeiters.', 'bad');
        return $this->redirectBack();
    }

    public function deleteMember() {
        $projectId = $this->checkEdit();
        $memberId = null;
        if ($this->getRequest()->getVar('dm')) {
            $memberId = $this->getRequest()->getVar('dm');
        } else if ($this->getRequest()->postVar('dm')) {
            $memberId = $this->getRequest()->postVar('dm');
        }

        if ($projectId && is_numeric($projectId) && $memberId && is_numeric($memberId)) {
            $project = Project::get()->byID($projectId);
            if ($project) {
                $projectMember = ProjectMember::get()->byID($memberId);
                if ($projectMember && $projectMember->ProjectID == $project->ID) {
                    $projectMember->delete();
                    $projectMember->destroy();
                    return $this->redirect($this->Link('?e=' . $project->ID));
                }
            }
        }

        return $this->redirectBack();
    }

    // ProjectDocument management methods

    public function getAllProjectDocuments() {
        $project = $this->actProject();
        if ($project) {
            return $project->ProjectDocuments();
        }
        
        return false;
    }

    public function checkEditDocument() {
        if ($this->getRequest()->getVar('ed')) {
            return $this->getRequest()->getVar('ed');
        } else if ($this->getRequest()->postVar('ed')) {
            return $this->getRequest()->postVar('ed');
        }
        return false;
    }

    public function addDocumentForm() {
        $project = $this->actProject();
        if (!$project) {
            return false;
        }

        $formFields = FieldList::create(
            TextField::create('Title', 'Dokumentationstitel')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            CheckboxField::create('VisibleToWorker', 'Sichtbar für Arbeiter')->setFieldHolderTemplate('FormField_customholder')->setValue(true),
            HiddenField::create('e', 'Projekt')->setValue($this->checkEdit())
        );

        $action = FieldList::create(
            FormAction::create('doAddDocument', 'Speichern')->addExtraClass('btn btn-primary px-4 mb-2')
        );
        
        $required = RequiredFields::create([
            'Title'
        ]);

        $form = Form::create($this, 'addDocumentForm', $formFields, $action, $required);

        return $form;
    }

    public function doAddDocument($data, $form) {
        $project = $this->actProject();
        if ($project) {
            $projectDocument = new ProjectDocument();
            $form->saveInto($projectDocument);
            $projectDocument->ProjectID = $project->ID;
            
            $projectDocument->write();

            $form->sessionMessage('Dokumenten-Kategorie erfolgreich hinzugefügt.', 'good');
            return $this->redirectBack();
        }
        
        $form->sessionMessage('Fehler beim Hinzufügen der Dokumenten-Kategorie.', 'bad');
        return $this->redirectBack();
    }

    public function editDocumentForm() {
        $id = $this->checkEditDocument();
        if ($id && is_numeric($id)) {
            $projectDocument = ProjectDocument::get()->byID($id);
            if ($projectDocument) {
                $addFields = $this->addDocumentForm()->Fields();
                $formFields = $addFields;
                $formFields->push(
                    HiddenField::create('ed', 'ID')->setValue($id)
                );

                $formFields->push(
                    FileField::create('Documents[]', 'Dateien')
                        ->setFolderName('project-documents/' . $projectDocument->ProjectID)
                        ->setAttribute('multiple', true)
                );

                $action = FieldList::create(
                    FormAction::create('doEditDocument', 'Speichern')->addExtraClass('btn btn-primary px-4 mb-2')
                );
                
                $required = RequiredFields::create([
                    'Title',
                ]);

                $form = Form::create($this, 'editDocumentForm', $formFields, $action, $required);
                $form->loadDataFrom($projectDocument);

                return $form;
            }
        }
    }

    public function doEditDocument($data, $form) {
        $id = $this->checkEditDocument();
        if ($id && is_numeric($id)) {
            $projectDocument = ProjectDocument::get()->byID($id);
            if ($projectDocument) {
                $form->saveInto($projectDocument);
                
                $projectDocument->write();

                if (isset($data['Documents']) && is_array($data['Documents'])) {

                    $keys = array_keys($data['Documents']);
                    $file_count = count($data['Documents']['name']);
                    for ($i = 0; $i < $file_count; $i++) {
                        $fileData = [];
                        foreach ($keys as $key) {
                            $fileData[$key] = $data['Documents'][$key][$i];
                        }

                        if ($fileData['tmp_name'] == '') {
                            continue;
                        }
                        // Handle each uploaded file
                        $file = new File();
                        $file->setFromLocalFile($fileData['tmp_name'], 'project-documents/' . $projectDocument->ProjectID . '/' . $fileData['name']);
                        $file->Title = $fileData['name'];
                        $file->write();

                        // Associate file with project document
                        $projectDocument->Documents()->add($file);
                    }

                    $projectDocument->write();
                }

                $form->sessionMessage('Dokumenten-Kategorie erfolgreich gespeichert.', 'good');
                return $this->redirectBack();
            }
        }
        
        $form->sessionMessage('Fehler beim Speichern der Dokumenten-Kategorie.', 'bad');
        return $this->redirectBack();
    }

    public function deleteDocument() {
        $projectId = $this->checkEdit();
        $documentId = null;
        if ($this->getRequest()->getVar('dd')) {
            $documentId = $this->getRequest()->getVar('dd');
        } else if ($this->getRequest()->postVar('dd')) {
            $documentId = $this->getRequest()->postVar('dd');
        }

        if ($projectId && is_numeric($projectId) && $documentId && is_numeric($documentId)) {
            $project = Project::get()->byID($projectId);
            if ($project) {
                $projectDocument = ProjectDocument::get()->byID($documentId);
                if ($projectDocument && $projectDocument->ProjectID == $project->ID) {
                    $projectDocument->delete();
                    $projectDocument->destroy();
                    return $this->redirect($this->Link('?e=' . $project->ID));
                }
            }
        }

        return $this->redirectBack();
    }
}