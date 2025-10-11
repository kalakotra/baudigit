<?php

namespace app\Controllers;

use app\Models\Article;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\GraphQL\Schema\Field\Field;

class ArticlePageController extends \PageController
{
    /**
     * An array of actions that can be accessed via a request. Each array element should be an action name, and the
     * permissions or conditions required to allow the user to access it.
     *
     * <code>
     * [
     *     'action', // anyone can access this action
     *     'action' => true, // same as above
     *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
     *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
     * ];
     * </code>
     *
     * @var array
     */
    private static $allowed_actions = [
        'add',
        'edit'
    ];

    protected function init()
    {
        parent::init();
        // You can include any CSS or JS required by your project here.
        // See: https://docs.silverstripe.org/en/developer_guides/templates/requirements/
    }

    public function getSummaryFields() {
        return Article::singleton()->summaryFields();
    }

    public function readAllInputs() {
        return Article::get();
    }

    public function add() {

        $formFields = FieldList::create(
            TextField::create('InternalID', 'Interne ID')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('Title', 'Titel')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            DropdownField::create('Unit', 'Einheit', Article::singleton()->getUnitOptions())->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select'),
            TextField::create('BuyPriceNetto', 'Einkaufspreis netto')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            //TextField::create('BuyPriceBrutto', 'Einkaufspreis brutto')->setReadonly(true)->setDescription('Wird automatisch aus dem Nettopreis und der MwSt. berechnet.')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('SellPriceNetto', 'Verkaufspreis netto')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            //TextField::create('SellPriceBrutto', 'Verkaufspreis brutto')->setReadonly(true)->setDescription('Wird automatisch aus dem Nettopreis und der MwSt. berechnet.')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextField::create('Description', 'Beschreibung')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')
        );

        $action = FieldList::create(
            FormAction::create('doAdd', 'Hinzufügen')->addExtraClass('btn btn-primary px-4')
        );

        $required = RequiredFields::create([
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
        $article = Article::create();
        $form->saveInto($article);
        $article->write();

        $form->sessionMessage('Produkt erfolgreich hinzugefügt.', 'good');

        return $this->redirectBack();
    }

    public function edit() {
        // check if there is an ID in the query string
        $id = $this->checkEdit();
        if ($id && is_numeric($id)) {
            $article = Article::get()->byID($id);
            if ($article) {
                $formFields = FieldList::create(
                    TextField::create('InternalID', 'Interne ID')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
                    TextField::create('Title', 'Titel')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
                    DropdownField::create('Unit', 'Einheit', Article::singleton()->getUnitOptions())->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select'),
                    TextField::create('BuyPriceNetto', 'Einkaufspreis netto')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
                    //TextField::create('BuyPriceBrutto', 'Einkaufspreis brutto')->setReadonly(true)->setDescription('Wird automatisch aus dem Nettopreis und der MwSt. berechnet.')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
                    TextField::create('SellPriceNetto', 'Verkaufspreis netto')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
                    //TextField::create('SellPriceBrutto', 'Verkaufspreis brutto')->setReadonly(true)->setDescription('Wird automatisch aus dem Nettopreis und der MwSt. berechnet.')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
                    TextField::create('Description', 'Beschreibung')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
                    HiddenField::create('e', 'ID')->setValue($id)
                );

                $action = FieldList::create(
                    FormAction::create('doEdit', 'Speichern')->addExtraClass('btn btn-primary px-4')
                );

                $required = RequiredFields::create([
                    'Title',
                    'Unit',
                    'BuyPriceNetto',
                    'SellPriceNetto',
                ]);

                $form = Form::create($this, 'edit', $formFields, $action, $required);
                $form->loadDataFrom($article);

                return $form;
            } else {
                return "Artikel nicht gefunden.";
            }
        } else {
            return "Ungültige ID.";
        }
    }

    public function doEdit($data, $form)
    {
        $id = $this->checkEdit(); //$this->getRequest()->getVar('ID');
        if ($id && is_numeric($id) && $id == $data['e']) {
            $article = Article::get()->byID($id);
            if ($article) {
                $form->saveInto($article);
                $article->write();

                $form->sessionMessage('Produkt erfolgreich gespeichert.', 'good');

                return $this->redirectBack();
            } else {
                $form->sessionMessage('Artikel nicht gefunden.', 'bad');
                return $this->redirectBack();
            }
        } else {
            $form->sessionMessage('Ungültige ID.', 'bad');
            return $this->redirectBack();
        }
    }
}