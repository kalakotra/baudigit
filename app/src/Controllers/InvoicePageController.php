<?php

namespace app\Controllers;

use app\Models\Offer;
use app\Models\Client;
use app\Models\Article;
use app\Models\Invoice;
use app\Models\Project;
use app\Pages\InvoicePage;
use app\Models\ClientObject;
use SilverStripe\Forms\Form;
use app\Models\InvoiceArticle;
use SilverStripe\ORM\DataList;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Forms\GroupedDropdownField;

class InvoicePageController extends \PageController
{
    private static $allowed_actions = [
        'add',
        'edit',
        'addArticleForm',
        'editArticleForm',
        'deleteArticle',
        'previewPDF',
        'send',
        'cancellation',
        'changeInvoiceStatus'
    ];

    public function readAllInputs()
    {
        $showOnlyStatus = $this->data()->ShowOnlyStatus;
        $statusValue = $this->data()->StatusValue;
        if ($showOnlyStatus) {
            return Invoice::get()->filter('Status', $statusValue);
        }
        return Invoice::get();
    }

    public function getAllArticles()
    {
        return Article::get();
    }

    public function actInvoice()
    {
        $id = $this->checkEdit();
        if ($id && is_numeric($id)) {
            $invoice = Invoice::get()->byID($id);
            if ($invoice) {
                return $invoice;
            }
        }

        return false;
    }

    public function checkEditArticle()
    {
        if ($this->getRequest()->getVar('ea')) {
            return $this->getRequest()->getVar('ea');
        } elseif ($this->getRequest()->postVar('ea')) {
            return $this->getRequest()->postVar('ea');
        }
        return false;
    }

    public function changeInvoiceStatus()
    {
        $id = $this->checkEdit();
        $newStatus = $this->getRequest()->getVar('status');

        if ($id && is_numeric($id) && is_numeric($newStatus)) {
            $invoice = Invoice::get()->byID($id);
            if ($invoice && $invoice->Status != 3 && $invoice->CancellationID == 0) { // do not change status if invoice is cancelled or connected to a cancellation
                $invoice->Status = intval($newStatus);
                $invoice->write();

                $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Rechnungsstatus erfolgreich geändert.');
                return $this->redirectBack();
            }
        }

        $this->setNotifySessionData('danger', 'bi bi-exclamation-triangle', 'Fehler beim Ändern des Rechnungsstatus.');
        return $this->redirectBack();
    }

    public function add()
    {
        // get last invoice to suggest next invoice number, format YYYY-0001
        $lastInvoice = Invoice::get()->sort('Created', 'DESC')->first();
        $nextInvoiceNumber = $lastInvoice ? $lastInvoice->getNextInvoiceNumber() : date('Y') . '-0001';
        $allCLients = Client::get()->map('ID', 'Company')->toArray();
        $clientGroupedList = [];

        if (!empty($allCLients)) {
            $clientIDs = array_keys($allCLients);

            $clients = Client::get()->filter('ID', $clientIDs);
            $clientsMap = [];
            foreach ($clients as $client) {
                $clientsMap[$client->ID] = $client;
            }

            $allClientObjects = ClientObject::get()->filter('ClientID', $clientIDs);
            $clientObjectsByClient = [];

            foreach ($allClientObjects as $clientObject) {
                if (isset($clientsMap[$clientObject->ClientID])) {
                    $clientObject->setComponent('Client', $clientsMap[$clientObject->ClientID]);
                }
                $clientObjectsByClient[$clientObject->ClientID][] = $clientObject;
            }

            foreach ($allCLients as $id => $company) {
                if (isset($clientObjectsByClient[$id])) {
                    $mapped = [];
                    foreach ($clientObjectsByClient[$id] as $obj) {
                        $mapped[$obj->ID] = $obj->getFullName();
                    }
                    if (!empty($mapped)) {
                        $clientGroupedList[$company] = $mapped;
                    }
                }
            }
        }

        $config = SiteConfig::current_site_config();

        // due date next 14 days
        $dueDate = date('Y-m-d', strtotime('+14 days'));
        $formFields = FieldList::create(
            TextField::create('InvoiceNumber', 'Rechnungsnummer', $nextInvoiceNumber)->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-control')->setReadonly(true)->setDisabled(true),
            DateField::create('DueDate', 'Fälligkeitsdatum', $dueDate)->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-control'),
            GroupedDropdownField::create('ClientObjectID', 'Kunde', $clientGroupedList)->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-select select2me')->setEmptyString('-- bitte wählen --'),
            TextareaField::create('IntroText', 'Einleitungstext', $config->InvoiceIntroText)->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-control'),
            TextareaField::create('OutroText', 'Schlusstext', $config->InvoiceOutroText)->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-control'),
        );

        $action = FieldList::create(
            FormAction::create('doAdd', 'Speichern')->addExtraClass('btn btn-sm btn-success px-4  mb-2')
        );

        $required = RequiredFields::create([
            'InvoiceNumber',
            'ClientObjectID',
        ]);

        // check if invoice is created from project
        if ($this->getRequest()->getVar('p') && is_numeric($this->getRequest()->getVar('p'))) {
            $projectID = $this->getRequest()->getVar('p');
            if ($project = Project::get()->byID($projectID)) {
                // set clientobjectid from project
                $formFields->dataFieldByName('ClientObjectID')->setValue($project->ClientObjectID);

                if ($project->OfferID) {
                    // set offer if project has one
                    $formFields->push(
                        HiddenField::create('OfferID', 'Angebot')->setValue($project->OfferID)
                    );
                }
            }
            $formFields->push(
                HiddenField::create('ProjectID', 'Projekt')->setValue($projectID)
            );
            // add cancel button/link
            /*$action->push(
                LiteralField::create('cancel', '<a href="javascript:history.back()" class="btn btn-sm btn-secondary px-4 mb-2 ms-2">Abbrechen</a>')
            );*/
        }

        $form = Form::create($this, 'add', $formFields, $action, $required);

        return $form;
    }

    public function doAdd($data, $form)
    {
        $invoice = new Invoice();
        $form->saveInto($invoice);
        $invoice->write();

        if ($data['ProjectID'] && $data['ProjectID'] != '') {
            // if invoice is created from project, copy all offer articles to invoice articles
            if ($project = Project::get()->byID($data['ProjectID'])) {
                $invoice->ProjectID = $project->ID;
                $invoice->write();
                if ($project->OfferID) {
                    $this->offerToInvoice($project->OfferID, $invoice->ID);
                }
            }
        }

        // redirect to edit page of the newly created invoice
        return $this->redirect($this->Link('?e=' . $invoice->ID));
    }

    public function edit()
    {
        $invoice = $this->actInvoice();
        if ($invoice) {
            $addForm = $this->add();

            $editFields = $addForm->Fields();

            

            // add hidden field with id
            $editFields->push(
                HiddenField::create('e', 'ID')->setValue($invoice->ID)
            );

            /*$editFields->unshift(
                DropdownField::create('Status', 'Status', Invoice::singleton()->getGermanStatusOptions())->setFieldHolderTemplate('FormField_customholder')->addExtraClass('form-select')
            );*/

            $action = FieldList::create(
                FormAction::create('doEdit', 'Speichern')->addExtraClass('btn btn-sm btn-success px-4 mb-2')
            );
            if (!$invoice->checkEditingStatus()) {
                foreach ($editFields as $field) {
                    $field->setReadonly(true)->setDisabled(true);
                }

                $action = FieldList::create(
                );
            }

            $required = RequiredFields::create([
                'InvoiceNumber',
                'ClientObjectID',
            ]);

            $form = Form::create($this, 'edit', $editFields, $action, $required);

            $form->loadDataFrom($invoice);

            return $form;
        }
    }

    public function doEdit($data, $form)
    {
        $invoice = $this->actInvoice();
        if ($invoice) {
            $form->saveInto($invoice);
            $invoice->write();

            $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Rechnung erfolgreich gespeichert.');
            return $this->redirectBack();
        }

        $this->setNotifySessionData('danger', 'bi bi-exclamation-triangle', 'Fehler beim Speichern der Rechnung.');
        return $this->redirectBack();
    }

    public function getAllInvoiceArticles()
    {
        $invoice = $this->actInvoice();
        if ($invoice) {
            return $invoice->InvoiceArticles();
        }
        
        return false;
    }

    public function deleteArticle()
    {
        $id = $this->checkEdit();
        $da = null;
        if ($this->getRequest()->getVar('da')) {
            $da = $this->getRequest()->getVar('da');
        } elseif ($this->getRequest()->postVar('da')) {
            $da = $this->getRequest()->postVar('da');
        }

        if ($id && is_numeric($id) && $da && is_numeric($da)) {
            $invoice = Invoice::get()->byID($id);
            if ($invoice) {
                $invoiceArticle = InvoiceArticle::get()->byID($da);
                if ($invoiceArticle && $invoiceArticle->InvoiceID == $invoice->ID) {
                    $invoiceArticle->delete();
                    $invoiceArticle->destroy();
                    return $this->redirect($this->Link('?e=' . $invoice->ID. '&activetab=invoice_article'));
                }
            }
        }

        return $this->redirectBack();
    }

    public function addArticleForm()
    {
        $allArticles = Article::get();
        $config = SiteConfig::current_site_config();

        $formFields = FieldList::create(
            //AutoCompleteField::create('Article', 'Produkt', null, Article::class, 'Title')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select searchableField'),
            NumericField::create('Quantity', 'Menge', 1)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setAttribute('type', 'number')->setAttribute('min', '1')->setAttribute('step', '1')->setValue(1),
            NumericField::create('Discount', 'Rabatt (%)', '0')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setScale(2),
            HiddenField::create('e', 'Rechnung')->setValue($this->checkEdit()),
            TextField::create('Title', 'Titel')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setAttribute('list', 'articleList')->setAttribute('autocomplete', 'off'),
            DropdownField::create('Unit', '', Article::singleton()->getUnitOptions())->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select'),
            HiddenField::create('BuyPriceNetto', 'Einkaufspreis netto')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            //HiddenField::create('BuyPriceBrutto', 'Einkaufspreis brutto')->setReadonly(true)->setDescription('Wird automatisch aus dem Nettopreis und der MwSt. berechnet.')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            NumericField::create('SellPriceNetto', 'Preis (netto)')->setScale(2)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            //NumericField::create('SellPriceBrutto', 'Preis')->setScale(2)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            NumericField::create('TaxRate', 'MwSt. (%)', $config->InvoiceTax)->setScale(2)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setReadonly(true),
            TextareaField::create('Description', 'Beschreibung')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setRows(2),
            HiddenField::create('SourceArticleID'),
            NumericField::create('FinalPrice', 'Betrag', '0,00')->setScale(2)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setReadonly(true)
        );

        $action = FieldList::create(
            FormAction::create('doAddArticle', 'Hinzufügen')->addExtraClass('btn btn-sm btn-success px-4 mb-2')
        );
        $required = RequiredFields::create([
            'Quantity',
            'Title',
            'InvoiceID',
            'SellPriceNetto'
        ]);

        $form = Form::create($this, 'addArticleForm', $formFields, $action, $required);

        $form->setTemplate('InvoiceArticleForm');

        return $form;
    }

    public function doAddArticle($data, $form)
    {
        $id = $this->checkEdit();
        if ($id && is_numeric($id)) {
            $invoice = Invoice::get()->byID($id);
            if ($invoice) {
                $article = InvoiceArticle::create();
                $form->saveInto($article);
                $article->InvoiceID = $invoice->ID;

                $config = SiteConfig::current_site_config();
                $article->TaxRate = $config->InvoiceTax;

                $article->write();

                $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Artikel erfolgreich gespeichert.');
                return $this->redirect($this->Link('?e=' . $invoice->ID. '&activetab=invoice_article'));
            }
        }
        $this->setNotifySessionData('danger', 'bi bi-exclamation-triangle', 'Fehler beim Hinzufügen des Artikels.');
        return $this->redirectBack();
    }

    public function editArticleForm()
    {
        $id = $this->checkEditArticle();
        if ($id && is_numeric($id)) {
            $invoiceArticle = InvoiceArticle::get()->byID($id);
            if ($invoiceArticle) {
                $formFields = $this->addArticleForm()->Fields();
                $formFields->push(
                    HiddenField::create('ea', 'ID')->setValue($id)
                );

                $action = FieldList::create(
                    FormAction::create('doEditArticle', 'Speichern')->addExtraClass('btn btn-sm btn-success px-4  mb-2')
                );
                $required = RequiredFields::create([
                    'Quantity',
                    'Title',
                    'InvoiceID',
                    'SellPriceNetto'
                ]);

                $form = Form::create($this, 'editArticleForm', $formFields, $action, $required);

                $form->setTemplate('InvoiceArticleForm');

                $form->loadDataFrom($invoiceArticle);

                return $form;
            }
        }
    }

    public function doEditArticle($data, $form)
    {
        $id = $this->checkEditArticle();
        if ($id && is_numeric($id)) {
            $invoiceArticle = InvoiceArticle::get()->byID($id);
            if ($invoiceArticle) {
                $form->saveInto($invoiceArticle);
                $invoiceArticle->write();

                $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Artikel erfolgreich gespeichert.');
                return $this->redirect($this->Link('?e=' . $invoiceArticle->InvoiceID. '&activetab=invoice_article'));
            }
        }
        $this->setNotifySessionData('danger', 'bi bi-exclamation-triangle', 'Fehler beim Speichern des Artikels.');
        return $this->redirectBack();
    }

    public function previewPDF()
    {
        $invoice = $this->actInvoice();
        if ($invoice) {
            return $invoice->getPDF();
        }
        return false;
    }

    public function send()
    {
        $invoice = $this->actInvoice();
        if ($invoice) {
            if ($invoice->sendInvoiceEmail()) {
                $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Rechnung erfolgreich versendet.');
                return $this->redirect($this->Link('?e=' . $invoice->ID.'&sent=1'));
            } else {
                $this->setNotifySessionData('danger', 'bi bi-exclamation-triangle', 'Fehler beim Versenden der Rechnung.');
                return $this->redirect($this->Link('?e=' . $invoice->ID.'&sent=0'));
            }
        }
        return $this->redirectBack();
    }

    public function getSentStatus()
    {
        if ($this->getRequest()->getVar('sent')) {
            return $sent = $this->getRequest()->getVar('sent');
        }
        return null;
    }


    public function offerToInvoice($offerID, $invoiceID)
    {
        $offer = Offer::get()->byID($offerID);
        $invoice = Invoice::get()->byID($invoiceID);
        if ($offer && $invoice) {
            // Copy all offer articles to invoice articles
            $offerArticles = $offer->OfferArticles();
            foreach ($offerArticles as $offerArticle) {
                $invoiceArticle = new InvoiceArticle();
                $invoiceArticle->InvoiceID = $invoice->ID;
                $invoiceArticle->Title = $offerArticle->Title;
                $invoiceArticle->Description = $offerArticle->Description;
                $invoiceArticle->Quantity = $offerArticle->Quantity;
                $invoiceArticle->Unit = $offerArticle->Unit;
                $invoiceArticle->SellPriceNetto = $offerArticle->SellPriceNetto;
                $invoiceArticle->Discount = $offerArticle->Discount;
                $invoiceArticle->TaxRate = $offerArticle->TaxRate;
                $invoiceArticle->FinalPrice = $offerArticle->FinalPrice;
                $invoiceArticle->SourceArticleID = $offerArticle->SourceArticleID;
                $invoiceArticle->write();
            }
        }
    }


    // cancellation of invoice. Set status to cancelled (3) and add new negative invoice
    public function cancellation()
    {
        $invoice = $this->actInvoice();
        if ($invoice) {
            // set status to cancelled
            $invoice->Status = 3; // Cancelled
            $invoice->write();

            // create new negative invoice
            $cancellationInvoice = new Invoice();
            //$cancellationInvoice->InvoiceNumber = $invoice->InvoiceNumber . '-STORNO';
            $cancellationInvoice->ClientObjectID = $invoice->ClientObjectID;
            $cancellationInvoice->DueDate = date('Y-m-d');
            $cancellationInvoice->IntroText = 'Stornorechnung für Rechnung ' . $invoice->InvoiceNumber . '.';
            $cancellationInvoice->OutroText = 'Diese Rechnung wurde automatisch als Stornorechnung erstellt.';
            $cancellationInvoice->Status = 2; // Paid
            $cancellationInvoice->CancellationOfID = $invoice->ID;
            $cancellationInvoice->write();

            // copy all invoice articles with negative quantities
            $invoiceArticles = $invoice->InvoiceArticles();
            foreach ($invoiceArticles as $invoiceArticle) {
                $newArticle = new InvoiceArticle();
                $newArticle->InvoiceID = $cancellationInvoice->ID;
                $newArticle->Title = $invoiceArticle->Title;
                $newArticle->Description = $invoiceArticle->Description;
                $newArticle->Quantity = -1 * $invoiceArticle->Quantity;
                $newArticle->Unit = $invoiceArticle->Unit;
                $newArticle->SellPriceNetto = $invoiceArticle->SellPriceNetto;
                $newArticle->Discount = $invoiceArticle->Discount;
                $newArticle->TaxRate = $invoiceArticle->TaxRate;
                $newArticle->FinalPrice = -1 * $invoiceArticle->FinalPrice;
                $newArticle->SourceArticleID = $invoiceArticle->SourceArticleID;
                $newArticle->write();
            }
        }
        $this->setNotifySessionData('success', 'bi bi-check2-circle', 'Stornorechnung erfolgreich erstellt.');
        return $this->redirect($this->Link('?e=' . $cancellationInvoice->ID));
    }
}
