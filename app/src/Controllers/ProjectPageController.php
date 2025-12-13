<?php

namespace app\Controllers;

use app\Models\Offer;
use app\Models\Client;
use app\Models\Invoice;
use app\Models\Project;
use app\Models\ProjectTask;
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
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\RequiredFields;
use app\Models\ProjectDocumentCategory;
use SilverStripe\Forms\CheckboxSetField;
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
        'uploadDocuments',
        'addDocumentForm',
        'editDocumentForm',
        'deleteDocument',
        'addTaskForm',
        'editTaskForm',
        'deleteTask',
    ];

    public function readAllInputs() {
        $showOnlyStatus = $this->data()->ShowOnlyStatus;
        $statusValue = $this->data()->StatusValue;
        if ($showOnlyStatus) {
            return Project::get()->filter('Status', $statusValue);
        }
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
            GroupedDropdownField::create('ClientObjectID', 'Kunde', $clientGroupedList)->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-select select2me')->setEmptyString('-- bitte wählen --'),
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
            FormAction::create('doAdd', 'Speichern')->addExtraClass('btn btn-success px-4  mb-2')
        );

        $required = RequiredFields::create([
            'Title',
            'ClientObjectID',
        ]);

        $form = Form::create($this, 'add', $formFields, $action, $required);

        if ($this->getRequest()->getVar('o')) {
            if ($offer = Offer::get()->byID($this->getRequest()->getVar('o'))) {
                $form->loadDataFrom([
                    'Title' => 'Projekt zu Angebot ' . $offer->OfferNumber,
                    'ClientObjectID' => $offer->ClientObjectID,
                ]);

                $formFields->push(
                    HiddenField::create('OfferID', 'Angebot')->setValue($offer->ID)
                );
            }

            // add cancel button/link
            $action->push(
                LiteralField::create('cancel', '<a href="javascript:history.back()" class="btn btn-secondary px-4 mb-2 ms-2">Abbrechen</a>')
            );
            
        }

        return $form;
    }

    public function doAdd($data, $form) {
        $project = new Project();
        $form->saveInto($project);
        

        if (isset($data['OfferID']) && is_numeric($data['OfferID'])) {
            $offer = Offer::get()->byID($data['OfferID']);
            if ($offer && $offer->exists()) {
                $offer->Status = 'Project';
                $offer->write();
                $project->OfferID = $offer->ID;
            }
        }

        $project->write();
        $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Projekt erfolgreich erstellt.');
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
                $submitAction = FormAction::create('doEdit', 'Speichern')->addExtraClass('btn btn-success px-4 mb-2')
            );

            if ($project->Status == 2 || $project->Status == 3 || $project->Status == 4) {
                //$submitAction->setDisabled(true); // to be cleared
            }

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
        //if ($project && !in_array($project->Status, [0, 1, 2, 3, 4])) {
            $form->saveInto($project);
            $project->write();

            $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Projekt erfolgreich gespeichert.');
            return $this->redirectBack();
        //}

        //$this->setNotifySessionData('error', 'bi bi-x-circle', 'Fehler beim Speichern des Projekts.');
        //return $this->redirectBack();
    }

    public function getAllProjectMembers() {
        $project = $this->actProject();
        if ($project) {
            //return $project->ProjectMembers();
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

            $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Mitarbeiter erfolgreich hinzugefügt.');
            return $this->redirect($this->Link('?e=' . $project->ID.'&em=' . $projectMember->ID));
        }
        
        $this->setNotifySessionData('error', 'bi bi-x-circle', 'Fehler beim Hinzufügen des Mitarbeiters.');
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
                    FormAction::create('doEditMember', 'Speichern')->addExtraClass('btn btn-success px-4 mb-2')
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

                $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Mitarbeiter erfolgreich gespeichert.');
                return $this->redirect($this->Link('?e=' . $projectMember->ProjectID.'&em=' . $projectMember->ID));
            }
        }
        
        $this->setNotifySessionData('error', 'bi bi-x-circle', 'Fehler beim Speichern des Mitarbeiters.');
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

    public function uploadDocuments() {
        $project = $this->actProject();
        if (!$project) {
            return false;
        }

        $formFields = FieldList::create(
            HiddenField::create('e', 'Projekt')->setValue($this->checkEdit()),
            DropdownField::create('Category', 'Dokumenten-Kategorie', 
                ProjectDocumentCategory::get()->map('ID', 'Name'))->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-select')->setEmptyString('-- bitte wählen --'),
        );

        $uploadFolder = $project->getUploadFolders()['doc'];
        $formFields->push(
            FileField::create('Documents[]', 'Dateien')
                ->setFolderName($uploadFolder)
                ->setAttribute('multiple', true)
                ->addExtraClass('form-upload')
                //->setAttribute('accept', '.xlsx,.xls,image/*,.doc,audio/*,.docx,video/*,.ppt,.pptx,.txt,.pdf')
        );

        $action = FieldList::create(
            FormAction::create('doUploadDocuments', 'Hochladen')->addExtraClass('btn btn-success px-4 mb-2')
        );

        $required = RequiredFields::create([
            'Category',
            'Documents'
        ]);
        
        $form = Form::create($this, 'uploadDocuments', $formFields, $action, $required);

        return $form;
    }

    public function doUploadDocuments($data, $form) {
        $project = $this->actProject();
        if ($project) {
            if (isset($data['Documents']) && is_array($data['Documents'])) {

                $documentsFolder = $project->getUploadFolders()['doc'];

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
                    $file->setFromLocalFile($fileData['tmp_name'], $documentsFolder. '/' . $fileData['name']);
                    $file->Title = $fileData['name'];
                    $file->write();

                    $project->Documents()->add($file, ['Category' => $data['Category']]);
                }

                $project->write();

                $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Dateien erfolgreich hochgeladen.');
                return $this->redirect($this->Link('?e=' . $project->ID));
            }
        }

        $this->setNotifySessionData('error', 'bi bi-x-circle', 'Fehler beim Hochladen der Dateien.');
        return $this->redirectBack();
    }

    public function addDocumentForm() {
        $project = $this->actProject();
        if (!$project) {
            return false;
        }

        $formFields = FieldList::create(
            DropdownField::create('ProjectDocumentCategoryID', 'Dokumenten-Kategorie', 
                ProjectDocumentCategory::get()->map('ID', 'Name'))->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-select')->setEmptyString('-- bitte wählen --'),
            TextField::create('Title', 'Dokumentationstitel')->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-control'),
            CheckboxField::create('VisibleToWorker', 'Sichtbar für Arbeiter')->setFieldHolderTemplate('FormField_customholder')->setValue(true),
            HiddenField::create('e', 'Projekt')->setValue($this->checkEdit())
        );

        $uploadFolder = $project->getUploadFolders()['doc'];
        $formFields->push(
            FileField::create('Documents[]', 'Dateien')
                ->setFolderName($uploadFolder)
                ->setAttribute('multiple', true)
                ->addExtraClass('form-upload')
                //->setAttribute('accept', '.xlsx,.xls,image/*,.doc,audio/*,.docx,video/*,.ppt,.pptx,.txt,.pdf')
        );

        $action = FieldList::create(
            FormAction::create('doAddDocument', 'Speichern')->addExtraClass('btn btn-success px-4 mb-2')
        );
        
        $required = RequiredFields::create([
            'ProjectDocumentCategoryID',
            'Title'
        ]);

        $form = Form::create($this, 'addDocumentForm', $formFields, $action, $required);

        return $form;
    }

    public function doAddDocument($data, $form) {
        print_r($data);
        die();
        $project = $this->actProject();
        if ($project) {
            $projectDocument = new ProjectDocument();
            $form->saveInto($projectDocument);
            $projectDocument->ProjectID = $project->ID;
            
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

            $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Dokumenten-Kategorie erfolgreich hinzugefügt.');
            return $this->redirect($this->Link('?e=' . $project->ID . '&ed=' . $projectDocument->ID));
        }
        
        $this->setNotifySessionData('error', 'bi bi-x-circle', 'Fehler beim Hinzufügen der Dokumenten-Kategorie.');
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
                $uploadFolder = $projectDocument->Project()->getUploadFolders()['doc']. '/' . $id;
                $formFields->push(
                    FileField::create('Documents[]', 'Dateien')
                        ->setFolderName($uploadFolder)
                        ->setAttribute('multiple', true)
                );

                $action = FieldList::create(
                    FormAction::create('doEditDocument', 'Speichern')->addExtraClass('btn btn-success px-4 mb-2')
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

                $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Dokumenten-Kategorie erfolgreich gespeichert.');
                
                return $this->redirectBack();
            }
        }
        
        $this->setNotifySessionData('error', 'bi bi-x-circle', 'Fehler beim Speichern der Dokumenten-Kategorie.');
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

    // task management methods

    public function getAllProjectTasks() {
        $project = $this->actProject();
        if ($project) {
            return $project->ProjectTasks();
        }
        
        return false;
    }

    public function checkEditTask() {
        if ($this->getRequest()->getVar('et')) {
            return $this->getRequest()->getVar('et');
        } else if ($this->getRequest()->postVar('et')) {
            return $this->getRequest()->postVar('et');
        }
        return false;
    }

    public function addTaskForm() {
        $project = $this->actProject();
        if (!$project) {
            return false;
        }

        // Get available members, only from worker group
        $allMembers = Member::get()->filter('Groups.ID', '3')->sort('Surname, FirstName'); //->map('ID', 'Name');

        $formFields = FieldList::create(
            DropdownField::create('Status', 'Status', [
                0 => 'Zu erledigen',
                1 => 'In Bearbeitung',
                2 => 'Erledigt',
            ])->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select')->setValue(0),
            TextField::create('Title', 'Titel')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextareaField::create('Description', 'Beschreibung')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setRows(4),
            DateField::create('StartDate', 'Startdatum')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            DateField::create('EndDate', 'Enddatum')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('EstimatedHours', 'Geschätzte Stunden')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            CheckboxSetField::create('AssignedTo', 'Zugewiesen an', $allMembers),/*
                ->setFieldHolderTemplate('FormField_customholder')
                ->setAttribute('class', 'form-select')
                ->setMultiple(true),*/
            CheckboxField::create('NeedsConfirmation', 'Benötigt Bestätigung'),
            HiddenField::create('e', 'Projekt')->setValue($this->checkEdit())
        );

        $action = FieldList::create(
            FormAction::create('doAddTask', 'Speichern')->addExtraClass('btn btn-success px-4 mb-2')
        );
        
        $required = RequiredFields::create([
            'Title',
            'StartDate',
            'EndDate',
            'AssignedTo'
        ]);

        $form = Form::create($this, 'addTaskForm', $formFields, $action, $required);

        return $form;
    }

    public function doAddTask($data, $form) {
        $project = $this->actProject();
        if ($project) {
            $projectTask = new ProjectTask();
            $form->saveInto($projectTask);
            $projectTask->ProjectID = $project->ID;
            
            $projectTask->write();

            $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Aufgabe erfolgreich hinzugefügt.');
            return $this->redirect($this->Link('?e=' . $project->ID . '&dt=' . $projectTask->ID));
        }
        
        $this->setNotifySessionData('error', 'bi bi-x-circle', 'Fehler beim Hinzufügen der Aufgabe.');
        return $this->redirectBack();
    }

    public function editTaskForm() {
        $id = $this->checkEditTask();
        if ($id && is_numeric($id)) {
            $projectTask = ProjectTask::get()->byID($id);
            if ($projectTask) {
                $allMembers = Member::get()->filter('Groups.ID', '3')->sort('Surname, FirstName'); //->map('ID', 'Name');
                $selectedMembers = $projectTask->AssignedTo()->map('ID', 'ID')->toArray();

                $addFields = $this->addTaskForm()->Fields();
                $formFields = $addFields;
                $formFields->push(
                    HiddenField::create('et', 'ID')->setValue($id)
                );

                $action = FieldList::create(
                    // add cancel button/link
                    LiteralField::create('cancel', '<a href="'.$this->Link("?e=" . $projectTask->ProjectID).'" class="btn btn-secondary px-4 mb-2 me-2">Abbrechen</a>'),
                    FormAction::create('doEditTask', 'Speichern')->addExtraClass('btn btn-success px-4 mb-2')
                );
                
                $required = RequiredFields::create([
                    'Title',
                    'StartDate',
                    'EndDate',
                    'AssignedTo'
                ]);

                $form = Form::create($this, 'editTaskForm', $formFields, $action, $required);
                $form->loadDataFrom($projectTask);
                //$form->Fields()->dataFieldByName('AssignedTo')->setValue($selectedMembers);

                return $form;
            }
        }
    }

    public function doEditTask($data, $form) {
        $id = $this->checkEditTask();
        if ($id && is_numeric($id)) {
            $projectTask = ProjectTask::get()->byID($id);
            if ($projectTask) {
                $form->saveInto($projectTask);
                
                $projectTask->write();

                // Handle assigned to members
                /*if (isset($data['AssignedTo']) && is_array($data['AssignedTo'])) {
                    $projectTask->AssignedTo()->setByIDList($data['AssignedTo']);
                } else {
                    $projectTask->AssignedTo()->removeAll();
                }*/

                $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Aufgabe erfolgreich gespeichert.');
                return $this->redirectBack();
            }
        }
        
        $this->setNotifySessionData('error', 'bi bi-x-circle', 'Fehler beim Speichern der Aufgabe.');
        return $this->redirectBack();
    }


    public function getProjectDocumentCategories() {
        return ProjectDocumentCategory::get();
    }
}