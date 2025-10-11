<?php

namespace app\Controllers;

use app\Models\Offer;
use app\Models\Client;
use app\Models\Article;
use app\Models\Invoice;
use app\Pages\InvoicePage;
use app\Models\ClientObject;
use app\Models\OfferArticle;
use SilverStripe\Forms\Form;
use app\Models\InvoiceArticle;
use SilverStripe\ORM\DataList;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Forms\GroupedDropdownField;

class OfferPageController extends \PageController
{
    private static $allowed_actions = [
        'add',
        'edit',
        'addArticleForm',
        'editArticleForm',
        'deleteArticle',
        'previewPDF',
        'send',
        'toInvoice'
    ];

    public function readAllInputs() {
        return Offer::get();
    }

    public function getAllArticles() {
        return Article::get();
    }

    public function actOffer() {
        $id = $this->checkEdit();
        if ($id && is_numeric($id)) {
            $offer = Offer::get()->byID($id);
            if ($offer) {
                return $offer;
            }
        }

        return false;
    }

    public function checkEditArticle() {
        if ($this->getRequest()->getVar('ea')) {
            return $this->getRequest()->getVar('ea');
        } else if ($this->getRequest()->postVar('ea')) {
            return $this->getRequest()->postVar('ea');
        }
        return false;
    }

    public function add() {
        // get last offer to suggest next offer number, format YYYY-0001
        $lastOffer = Offer::get()->sort('Created', 'DESC')->first();
        $nextOfferNumber = $lastOffer ? $lastOffer->getNextOfferNumber() : date('Y') . '-0001';
        $allCLients = Client::get()->map('ID', 'Company')->toArray();
        $clientGroupedList = [];
        foreach ($allCLients as $id => $company) {
            $clientObjects = ClientObject::get()->filter('ClientID', $id);
            if ($clientObjects->count() > 0) {
                $clientGroupedList[$company] = $clientObjects->map('ID', 'getFullName')->toArray();
            }
        }

        $config = SiteConfig::current_site_config();

        // valid next 30 days
        $validUntil = date('Y-m-d', strtotime('+30 days'));
        $formFields = FieldList::create(
            TextField::create('OfferNumber', 'Angebotsnummer', $nextOfferNumber)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setReadonly(true),
            DateField::create('ValidUntil', 'Gültig bis', $validUntil)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            GroupedDropdownField::create('ClientObjectID', 'Kunde', $clientGroupedList)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select')->setEmptyString('-- bitte wählen --'),
            TextareaField::create('IntroText', 'Einleitungstext', $config->OfferIntroText)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
            TextareaField::create('OutroText', 'Schlusstext', $config->OfferOutroText)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control'),
        );

        $action = FieldList::create(
            FormAction::create('doAdd', 'Speichern')->addExtraClass('btn btn-primary px-4  mb-2')
        );

        $required = RequiredFields::create([
            'OfferNumber',
            'ClientObjectID',
        ]);

        $form = Form::create($this, 'add', $formFields, $action, $required);

        return $form;
    }

    public function doAdd($data, $form) {
        $offer = new Offer();
        $form->saveInto($offer);
        $offer->write();

        // redirect to edit page of the newly created offer
        return $this->redirect($this->Link('?e=' . $offer->ID));
    }

    public function edit() {
        $offer = $this->actOffer();
        if ($offer) {
            $addForm = $this->add();

            // add hidden field with id
            $addForm->Fields()->push(
                HiddenField::create('e', 'ID')->setValue($offer->ID)
            );

            $action = FieldList::create(
                FormAction::create('doEdit', 'Speichern')->addExtraClass('btn btn-primary px-4 mb-2')
            );

            $required = RequiredFields::create([
                'OfferNumber',
                'ClientObjectID',
            ]);

            $form = Form::create($this, 'edit', $addForm->Fields(), $action, $required);

            $form->loadDataFrom($offer);

            return $form;
        }
    }

    public function doEdit($data, $form) {
        $offer = $this->actOffer();
        if ($offer) {
            $form->saveInto($offer);
            $offer->write();

            $form->sessionMessage('Angebot erfolgreich gespeichert.', 'good');
            return $this->redirectBack();
        }

        $form->sessionMessage('Fehler beim Speichern des Angebots.', 'bad');
        return $this->redirectBack();
    }

    public function getAllOfferArticles() {
        $offer = $this->actOffer();
        if ($offer) {
            return $offer->OfferArticles();
        }
        
        return false;
    }

    public function deleteArticle() {
        $id = $this->checkEdit();
        $da = null;
        if ($this->getRequest()->getVar('da')) {
            $da = $this->getRequest()->getVar('da');
        } else if ($this->getRequest()->postVar('da')) {
            $da = $this->getRequest()->postVar('da');
        }

        if ($id && is_numeric($id) && $da && is_numeric($da)) {
            $offer = Offer::get()->byID($id);
            if ($offer) {
                $offerArticle = OfferArticle::get()->byID($da);
                if ($offerArticle && $offerArticle->OfferID == $offer->ID) {
                    $offerArticle->delete();
                    $offerArticle->destroy();
                    return $this->redirect($this->Link('?e=' . $offer->ID));
                }
            }
        }

        return $this->redirectBack();
    }

    public function addArticleForm() {
        $allArticles = Article::get();
        $config = SiteConfig::current_site_config();

        $formFields = FieldList::create(
            //AutoCompleteField::create('Article', 'Produkt', null, Article::class, 'Title')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-select searchableField'),
            NumericField::create('Quantity', 'Menge', 1)->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setAttribute('type', 'number')->setAttribute('min', '1')->setAttribute('step', '1')->setValue(1),
            NumericField::create('Discount', 'Rabatt (%)', '0')->setFieldHolderTemplate('FormField_customholder')->setAttribute('class', 'form-control')->setScale(2),
            HiddenField::create('e', 'Angebot')->setValue($this->checkEdit()),

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
            FormAction::create('doAddArticle', 'Hinzufügen')->addExtraClass('btn btn-primary px-4 mb-2')
        );
        $required = RequiredFields::create([
            'Quantity',
            'Title',
            'OfferID',
            'SellPriceNetto'
        ]);

        $form = Form::create($this, 'addArticleForm', $formFields, $action, $required);

        $form->setTemplate('OfferArticleForm');

        return $form;
    }

    public function doAddArticle($data, $form) {
        $id = $this->checkEdit();
        if ($id && is_numeric($id)) {
            $offer = Offer::get()->byID($id);
            if ($offer) {
                $article = OfferArticle::create();
                $form->saveInto($article);
                $article->OfferID = $offer->ID;
                $article->write();

                $form->sessionMessage('Artikel erfolgreich hinzugefügt.', 'good');
                return $this->redirectBack();
            }
        }
        $form->sessionMessage('Fehler beim Hinzufügen des Artikels.', 'bad');
        return $this->redirectBack();
    }

    public function editArticleForm() {
        $id = $this->checkEditArticle();
        if ($id && is_numeric($id)) {
            $offerArticle = OfferArticle::get()->byID($id);
            if ($offerArticle) {
                $formFields = $this->addArticleForm()->Fields();
                $formFields->push(
                    HiddenField::create('ea', 'ID')->setValue($id)
                );

                $action = FieldList::create(
                    FormAction::create('doEditArticle', 'Speichern')->addExtraClass('btn btn-primary px-4  mb-2')
                );
                $required = RequiredFields::create([
                    'Quantity',
                    'Title',
                    'OfferID',
                    'SellPriceNetto'
                ]);

                $form = Form::create($this, 'editArticleForm', $formFields, $action, $required);

                $form->setTemplate('OfferArticleForm');

                $form->loadDataFrom($offerArticle);

                return $form;
            }
        }
    }

    public function doEditArticle($data, $form) {
        $id = $this->checkEditArticle();
        if ($id && is_numeric($id)) {
            $offerArticle = OfferArticle::get()->byID($id);
            if ($offerArticle) {
                $form->saveInto($offerArticle);
                $offerArticle->write();

                $form->sessionMessage('Artikel erfolgreich gespeichert.', 'good');
                return $this->redirect($this->Link('?e=' . $offerArticle->OfferID));
            }
        }
        $form->sessionMessage('Fehler beim Speichern des Artikels.', 'bad');
        return $this->redirectBack();
    }

    public function previewPDF() {
        $offer = $this->actOffer();
        if ($offer) {
            return $offer->getPDF();
        }
        return false;
    }

    public function send() {
        $offer = $this->actOffer();
        if ($offer) {
            if ($offer->sendOfferEmail()) {
                return $this->redirect($this->Link('?e=' . $offer->ID.'&sent=1'));
            } else {
                return $this->redirect($this->Link('?e=' . $offer->ID.'&sent=0'));
            }
        }
        return $this->redirectBack();
    }

    public function getSentStatus() {
        if ($this->getRequest()->getVar('sent')) {
            return $sent = $this->getRequest()->getVar('sent');
        }
        return null;
    }

    public function toInvoice() {
        $offer = $this->actOffer();
        if ($offer) {
            // Create new invoice
            $invoice = new Invoice();
            
            // Copy matching data from offer to invoice
            $invoice->ClientObjectID = $offer->ClientObjectID;
            $invoice->IntroText = $offer->IntroText;
            $invoice->OutroText = $offer->OutroText;
            
            // Generate invoice number (get next available invoice number)
            $lastInvoice = Invoice::get()->sort('Created', 'DESC')->first();
            $invoice->InvoiceNumber = $lastInvoice ? $lastInvoice->getNextInvoiceNumber() : date('Y') . '-0001';
            
            // Set due date (14 days from now)
            $invoice->DueDate = date('Y-m-d', strtotime('+14 days'));
            
            // Save the invoice
            $invoice->write();
            
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
            
            // Update offer status to 'Accepted'
            $offer->Status = 'Accepted';
            $offer->write();
            
            // Redirect to invoice page in edit mode
            $invoicePage = InvoicePage::get()->first();
            if ($invoicePage) {
                return $this->redirect($invoicePage->Link('?e=' . $invoice->ID . '&converted=1'));
            } else {
                // Fallback if invoice page doesn't exist
                return $this->redirect($this->Link('?e=' . $offer->ID . '&invoice_created=' . $invoice->ID));
            }
        }
        
        return $this->redirectBack();
    }
}