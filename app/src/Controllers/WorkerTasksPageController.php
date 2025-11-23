<?php

namespace app\Controllers;

use app\Models\ProjectTask;
use SilverStripe\Forms\Form;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use app\Models\ProjectTaskHours;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\AssetAdmin\Forms\UploadField;

class WorkerTasksPageController extends \PageController
{

    private static $allowed_actions = [
        'WorkerTasksTimeForm',
        //'WorkerTasksTimeFormLink',
    ];

    // get all asigned ProjectTasks for the logged in worker
    public function getAssignedTasks()
    {
        $currentUser = $this->currentUser();
        if ($currentUser) {
            $myTasks = ProjectTask::get()
                ->filter([
                    'AssignedTo.ID' => $currentUser->ID,
                    'Status:not' => '2' // exclude completed tasks
                ]);
            return $myTasks;
        }
        return null;
    }

    /*public function WorkerTasksTimeFormLink() {
        $taskID = $this->getRequest()->param('ID');
        return $this->renderWith(['Forms/WorkerTasksTimeForm'], ['TaskID' => $taskID]);
    }*/

    public function checkIfTaskIDProvided()
    {
        $taskID = $this->getRequest()->getVar('TaskID');
        return $taskID ? true : false;
    }

    public function WorkerTasksTimeForm()
    {
        $taskID = $this->getRequest()->getVar('TaskID');
        if (!$taskID) {
            if ($this->getRequest()->isPOST()) {
                $taskID = $this->getRequest()->postVar('ProjectTaskID');
            } else {
                return null;
            }
        }

        $task = ProjectTask::get()->byID($taskID);
        if ($task) {
            if (!$task->canSignHours()) {
                $this->redirect($this->Link());
            }
            // hours max value 10 
            $fields = FieldList::create(
                NumericField::create('Hours', 'Geleistete Stunden')->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-control')->setAttribute('step', '0.5')->setAttribute('min', '0')->setAttribute('max', '10')->setScale('1')->setHTML5(true),
                TextareaField::create('Description', 'Beschreibung')->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-control'),
                $TaskImagesField = FileField::create('TaskImages[]', 'Bilder (optional)')->addExtraClass('form-control'),
                TextareaField::create('Signature', 'Unterschrift')->setFieldHolderTemplate('SignatureFormField_customholder')->addExtraClass('form-control signature-field'),
                HiddenField::create('ProjectTaskID', 'ProjectTaskID', $taskID)
            );
            
            $taskFolder = $task->Project()->getUploadFolders()['task']. '/' . $taskID . '/images/' . $this->currentUser()->ID;
            $TaskImagesField->setAllowedExtensions(['jpg', 'jpeg', 'png'])->setFolderName($taskFolder)->setAttribute('multiple', true);

            if ($task->NeedsConfirmation) {
                $fields->fieldByName('Signature')->setDescription('Bitte unterschreiben Sie hier, um die geleisteten Stunden zu bestätigen.');
                $requiredFields = RequiredFields::create([
                    'Hours',
                    'Signature',
                ]);

            } else {
                $fields->removeByName('Signature');
                $requiredFields = RequiredFields::create([
                    'Hours',
                ]);

            }
            
            $actionFields = FieldList::create(
                FormAction::create('doWorkerTasksTimeForm', 'Stunden erfassen')->addExtraClass('btn btn-success px-4 mb-2 w-100'),
                LiteralField::create('doCancel', '<a href="'.$this->Link().'" class="btn btn-secondary px-4 mb-2 w-100">Abbrechen</a>')
            );

            $form = Form::create(
                $this,
                'WorkerTasksTimeForm',
                $fields,
                $actionFields,
                $requiredFields
            );

            return $form;
        }
        return null;

    }

    public function doWorkerTasksTimeForm($data, $form) {
        // save data
        $workerHours = ProjectTaskHours::create();
        $form->saveInto($workerHours);
        $workerHours->LoggedByID = $this->currentUser()->ID;
        $workerHours->write();

        // upload images
        if (isset($data['TaskImages']) && is_array($data['TaskImages'])) {

            $taskFolder = $workerHours->ProjectTask()->Project()->getUploadFolders()['task']. '/' . $workerHours->ProjectTask()->ID . '/images/' . $this->currentUser()->ID . '/' . date('Ymd_His');
            $keys = array_keys($data['TaskImages']);
            $file_count = count($data['TaskImages']['name']);
            for ($i = 0; $i < $file_count; $i++) {
                $fileData = [];
                foreach ($keys as $key) {
                    $fileData[$key] = $data['TaskImages'][$key][$i];
                }

                if ($fileData['tmp_name'] == '') {
                    continue;
                }
                // Handle each uploaded file
                $file = new Image();
                $file->setFromLocalFile($fileData['tmp_name'], $taskFolder . '/' . $fileData['name']);
                $file->Title = $fileData['name'];
                $file->write();

                // Associate file with project document
                $workerHours->TaskImages()->add($file);
            }

            $workerHours->write();
        }

        $this->redirect($this->Link());
    }

    

}