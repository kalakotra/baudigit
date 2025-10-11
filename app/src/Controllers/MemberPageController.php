<?php

namespace app\Controllers;

use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\PasswordField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Security\Member;
use SilverStripe\Security\Group;
use SilverStripe\Security\Security;
use SilverStripe\Security\DefaultAdminService;

class MemberPageController extends \PageController
{
    private static $allowed_actions = [
        'add',
        'edit'
    ];

    public function readAllInputs() {
        $currentUser = Security::getCurrentUser();
        if ($currentUser) {
            return Member::get()->exclude('ID', $currentUser->ID);
        }
        return Member::get();
    }

    public function actMember() {
        $id = $this->checkEdit();
        if ($id && is_numeric($id)) {
            $member = Member::get()->byID($id);
            if ($member) {
                return $member;
            }
        }

        return false;
    }

    public function getGroupOptions() {
        return [
            2 => 'Administrator',
            3 => 'Arbeiter'
        ];
    }

    public function add() {
        $formFields = FieldList::create(
            TextField::create('FirstName', 'Vorname')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('Surname', 'Nachname')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            EmailField::create('Email', 'E-Mail')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            PasswordField::create('Password', 'Passwort')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setAttribute('autocomplete', 'new-password'),
            PasswordField::create('PasswordConfirm', 'Passwort bestätigen')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setAttribute('autocomplete', 'new-password'),
            DropdownField::create('GroupID', 'Benutzergruppe', $this->getGroupOptions())->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select')->setEmptyString('-- bitte wählen --')
        );

        $action = FieldList::create(
            FormAction::create('doAdd', 'Speichern')->addExtraClass('btn btn-primary px-4 mb-2')
        );

        $required = RequiredFields::create([
            'FirstName',
            'Surname',
            'Email',
            'Password',
            'PasswordConfirm',
            'GroupID'
        ]);

        $form = Form::create($this, 'add', $formFields, $action, $required);

        return $form;
    }

    public function doAdd($data, $form) {
        // Check if passwords match
        if ($data['Password'] !== $data['PasswordConfirm']) {
            $form->sessionMessage('Die Passwörter stimmen nicht überein.', 'bad');
            return $this->redirectBack();
        }

        // Check if email already exists
        $existingMember = Member::get()->filter('Email', $data['Email'])->first();
        if ($existingMember) {
            $form->sessionMessage('Ein Benutzer mit dieser E-Mail-Adresse existiert bereits.', 'bad');
            return $this->redirectBack();
        }

        // Create new member
        $member = new Member();
        $member->FirstName = $data['FirstName'];
        $member->Surname = $data['Surname'];
        $member->Email = $data['Email'];
        $member->changePassword($data['Password']);
        $member->write();

        // Add member to selected group
        if (isset($data['GroupID']) && is_numeric($data['GroupID'])) {
            $group = Group::get()->byID($data['GroupID']);
            if ($group) {
                $member->Groups()->add($group);
            }
        }

        $form->sessionMessage('Benutzer erfolgreich erstellt.', 'good');
        return $this->redirect($this->Link('?e=' . $member->ID));
    }

    public function edit() {
        $member = $this->actMember();
        if ($member) {
            // Get current group ID
            $currentGroupID = null;
            $memberGroups = $member->Groups()->filter('ID', [2, 3]);
            if ($memberGroups->count() > 0) {
                $currentGroupID = $memberGroups->first()->ID;
            }

            $formFields = FieldList::create(
                TextField::create('FirstName', 'Vorname')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
                TextField::create('Surname', 'Nachname')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
                EmailField::create('Email', 'E-Mail')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
                PasswordField::create('Password', 'Neues Passwort (leer lassen für unverändert)')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setAttribute('autocomplete', 'new-password'),
                PasswordField::create('PasswordConfirm', 'Neues Passwort bestätigen')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setAttribute('autocomplete', 'new-password'),
                DropdownField::create('GroupID', 'Benutzergruppe', $this->getGroupOptions())->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select')->setEmptyString('-- bitte wählen --')->setValue($currentGroupID),
                HiddenField::create('e', 'ID')->setValue($member->ID)
            );

            $action = FieldList::create(
                FormAction::create('doEdit', 'Speichern')->addExtraClass('btn btn-primary px-4 mb-2')
            );

            $required = RequiredFields::create([
                'FirstName',
                'Surname',
                'Email',
                'GroupID'
            ]);

            $form = Form::create($this, 'edit', $formFields, $action, $required);

            $form->loadDataFrom($member);

            return $form;
        }
    }

    public function doEdit($data, $form) {
        $member = $this->actMember();
        if ($member) {
            // Check if passwords match (if provided)
            if (!empty($data['Password']) && $data['Password'] !== $data['PasswordConfirm']) {
                $form->sessionMessage('Die Passwörter stimmen nicht überein.', 'bad');
                return $this->redirectBack();
            }

            // Check if email already exists (excluding current member)
            $existingMember = Member::get()->filter('Email', $data['Email'])->exclude('ID', $member->ID)->first();
            if ($existingMember) {
                $form->sessionMessage('Ein anderer Benutzer mit dieser E-Mail-Adresse existiert bereits.', 'bad');
                return $this->redirectBack();
            }

            // Update member data
            $member->FirstName = $data['FirstName'];
            $member->Surname = $data['Surname'];
            $member->Email = $data['Email'];

            // Update password if provided
            if (!empty($data['Password'])) {
                $member->changePassword($data['Password']);
            }

            $member->write();

            // Update group membership
            if (isset($data['GroupID']) && is_numeric($data['GroupID'])) {
                // Remove from existing admin/worker groups
                $adminGroup = Group::get()->byID(2);
                $workerGroup = Group::get()->byID(3);
                if ($adminGroup) {
                    $member->Groups()->remove($adminGroup);
                }
                if ($workerGroup) {
                    $member->Groups()->remove($workerGroup);
                }

                // Add to selected group
                $newGroup = Group::get()->byID($data['GroupID']);
                if ($newGroup) {
                    $member->Groups()->add($newGroup);
                }
            }

            $form->sessionMessage('Benutzer erfolgreich gespeichert.', 'good');
            return $this->redirectBack();
        }

        $form->sessionMessage('Fehler beim Speichern des Benutzers.', 'bad');
        return $this->redirectBack();
    }

    public function getMemberGroupName($member) {
        $groups = $member->Groups()->filter('ID', [2, 3]);
        if ($groups->count() > 0) {
            $group = $groups->first();
            switch ($group->ID) {
                case 2:
                    return 'Administrator';
                case 3:
                    return 'Arbeiter';
                default:
                    return 'Unbekannt';
            }
        }
        return 'Keine Gruppe';
    }
}