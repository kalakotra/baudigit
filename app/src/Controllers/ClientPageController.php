<?php

namespace app\Controllers;

use app\Models\Client;
use app\Models\Article;
use app\Models\ClientObject;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\RequiredFields;

class ClientPageController extends \PageController
{
    private static $allowed_actions = [
        'add',
        'edit',
        'addLocation',
        'editLocation'
    ];

    public function readAllInputs() {
        return Client::get();
    }

    public function readAllLocationInputs() {
        $clientID = $this->checkEdit();
        if ($clientID && is_numeric($clientID)) {
            $client = Client::get()->byID($clientID);
            if ($client) {
                return $client->ClientObjects();
            }
        }
        return null;
    }

    public function checkLocationList() {
        if ($this->getRequest()->getVar('el') === '0') {
            return true;
        } else if ($this->getRequest()->postVar('el') === '0') {
            return true;
        }
        return false;
    }

    public function checkEditLocation() {
        if ($this->getRequest()->getVar('el')) {
            return $this->getRequest()->getVar('el');
        } else if ($this->getRequest()->postVar('el')) {
            return $this->getRequest()->postVar('el');
        }
        return false;
    }

    public function checkAddLocation() {
        if ($this->getRequest()->getVar('al')) {
            return $this->getRequest()->getVar('al');
        } else if ($this->getRequest()->postVar('al')) {
            return $this->getRequest()->postVar('al');
        }
        return false;
    }

    public function add() {
        $formFields = FieldList::create(
            TextField::create('Company', 'Unternehmen')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('Name', 'Kontakt Person')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            EmailField::create('Email', 'Email')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('Phone', 'Telefon')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('Address', 'Adresse')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('PostalCode', 'Postleitzahl')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('City', 'Stadt')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')
        );

        $action = FieldList::create(
            FormAction::create('doAdd', 'Hinzufügen')->addExtraClass('btn btn-sm btn-success px-4')
        );

        $required = RequiredFields::create([
            'InternalID',
            'Title',
            'Unit',
            'BuyPriceNetto',
            'SellPriceNetto',
        ]);

        $form = Form::create($this, 'add', $formFields, $action, $required);

        return $form;
    }

    public function doAdd($data, $form)
    {
        $client = Client::create();
        $form->saveInto($client);
        $client->write();

        $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Kunde erfolgreich gespeichert.');
        return $this->redirect($this->Link('?e=' . $client->ID));
    }

    public function edit() {
        $id = $this->checkEdit();
        if ($id && is_numeric($id)) {
            $client = Client::get()->byID($id);
            if ($client) {
                $addForm = $this->add();

                // add hidden field with id
                $addForm->Fields()->push(
                    HiddenField::create('e', 'ID')->setValue($id)
                );

                $action = FieldList::create(
                    FormAction::create('doEdit', 'Speichern')->addExtraClass('btn btn-sm btn-success px-4')
                );

                $required = RequiredFields::create([
                    'InternalID',
                    'Title',
                    'Unit',
                    'BuyPriceNetto',
                    'SellPriceNetto',
                ]);

                $form = Form::create($this, 'edit', $addForm->Fields(), $action, $required);

                $form->loadDataFrom($client);

                return $form;
            }
        }
    }

    public function doEdit($data, $form) {
        $id = $this->checkEdit();
        if ($id && is_numeric($id)) {
            $client = Client::get()->byID($id);
            if ($client) {
                $form->saveInto($client);
                $client->write();

                $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Kunde erfolgreich gespeichert.');
                return $this->redirectBack();
            }
        }
    }

    public function addLocation() {
        $formFields = FieldList::create(
            HiddenField::create('e', 'Kunden ID')->setValue($this->checkEdit()),
            TextField::create('Name', 'Standort Name')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('Address', 'Adresse')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('PostalCode', 'Postleitzahl')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('City', 'Stadt')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('ContactPerson', 'Ansprechpartner')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('ContactTelephone', 'Telefon')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            EmailField::create('ContactEmail', 'Email')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextareaField::create('Description', 'Beschreibung')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')
        );

        $action = FieldList::create(
            FormAction::create('doAddLocation', 'Hinzufügen')->addExtraClass('btn btn-sm btn-success px-4')
        );

        $required = RequiredFields::create([
            'ClientID',
            'Name',
            'Address',
            'PostalCode',
            'City'
        ]);

        $form = Form::create($this, 'addLocation', $formFields, $action, $required);

        return $form;
    }

    public function doAddLocation($data, $form) {
        $clientID = $this->checkEdit();
        if ($clientID && is_numeric($clientID)) {
            $client = Client::get()->byID($clientID);
            if ($client) {
                $location = ClientObject::create(); //$client->ClientObjects()->create();
                $location->ClientID = $clientID;
                $form->saveInto($location);
                $location->write();

                $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Standort erfolgreich gespeichert.');
                return $this->redirect($this->Link('?e=' . $clientID . '&el=' . $location->ID));
            }
        }
    }

    public function editLocation() {
        $locationID = $this->checkEditLocation();
        $clientID = $this->checkEdit();
        if ($locationID && is_numeric($locationID) && $clientID && is_numeric($clientID)) {
            $client = Client::get()->byID($clientID);
            if ($client) {
                $location = $client->ClientObjects()->byID($locationID);
                if ($location && $location->ClientID == $clientID) {
                    $addForm = $this->addLocation();

                    // add hidden field with id
                    $addForm->Fields()->push(
                        HiddenField::create('e', 'KundenID')->setValue($clientID)
                    );
                    $addForm->Fields()->push(
                        HiddenField::create('el', 'StandortID')->setValue($locationID)
                    );
                    

                    $action = FieldList::create(
                        FormAction::create('doEditLocation', 'Speichern')->addExtraClass('btn btn-sm btn-success px-4')
                    );

                    $required = RequiredFields::create([
                        'ClientID',
                        'Name',
                        'Address',
                        'PostalCode',
                        'City'
                    ]);

                    $form = Form::create($this, 'editLocation', $addForm->Fields(), $action, $required);

                    $form->loadDataFrom($location);

                    return $form;
                }
            }
        }

    }

    public function doEditLocation($data, $form) {
        $locationID = $this->checkEditLocation();
        $clientID = $this->checkEdit();
        if ($locationID && is_numeric($locationID) && $clientID && is_numeric($clientID)) {
            $client = Client::get()->byID($clientID);
            if ($client) {
                $location = $client->ClientObjects()->byID($locationID);
                if ($location && $location->ClientID == $clientID) {
                    $form->saveInto($location);
                    $location->write();

                    $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Standort erfolgreich gespeichert.');
                    return $this->redirectBack();
                }
            }
        }
    }

}