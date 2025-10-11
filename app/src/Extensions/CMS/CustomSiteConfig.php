<?php

use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\EmailField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\AssetAdmin\Forms\UploadField;

class CustomSiteConfig extends DataExtension
{

    private static $db = [
        'InvoiceTax' => 'Int', 

        'PaymentTermDays' => 'Int', // Zahl der Tage für Zahlungsziel
        'PaymentTermText' => 'Text', // Text für Zahlungsziel
        'PaymentIBAN' => 'Varchar(34)', // IBAN für Zahlung
        'PaymentBIC' => 'Varchar(11)', // BIC für Zahlung
        'PaymentAccountHolder' => 'Varchar(255)', // Kontoinhaber

        'CompanyName' => 'Varchar(255)', // Firmenname
        'CompanyAddressLine1' => 'Text', // Firmenadresse Zeile 1
        'CompanyAddressLine2' => 'Text', // Firmenadresse Zeile 2
        'CompanyAddressLine3' => 'Text', // Firmenadresse Zeile 3
        'CompanyPostalCode' => 'Varchar(20)', // Firmenadresse PLZ
        'CompanyCity' => 'Varchar(255)', // Firmenadresse Ort
        'CompanyCountry' => 'Varchar(2)', // Firmenadresse Land; DE
        'CompanyContactPerson' => 'Varchar(255)', // Firmenkontaktperson
        'CompanyContactDepartment' => 'Varchar(255)', // Firmenkontakt Abteilung
        'CompanyContactPhone' => 'Varchar(255)', // Firmenkontakt Telefon
        'CompanyContactEmail' => 'Varchar(255)', // Firmenkontakt Email

        'CompanyContactWebsite' => 'Varchar(255)', // Firmenkontakt Webseite
        'CompanyVATId' => 'Varchar(50)', // USt-IdNr
        'CompanyTaxNumber' => 'Varchar(50)', // Steuernummer

        'OfferIntroText' => 'Text', // Standard Einleitungstext für Angebote
        'OfferOutroText' => 'Text', // Standard Schlusstext für Angebote
        'InvoiceIntroText' => 'Text', // Standard Einleitungstext für Rechnungen
        'InvoiceOutroText' => 'Text', // Standard Schlusstext für Rechnungen

        'EmailOfferText' => 'HTMLText', // Standard Text für Angebots-E-Mail
        'EmailInvoiceText' => 'HTMLText', // Standard Text für Rechnungs-E-Mail
    ];

    private static $has_one = [
        'Logo' => Image::class
    ];

    private static $has_many = [

    ];

    private static $many_many = [

    ];

    private static $many_many_extraFields = [

    ];

    private static $owns = [
        'Logo',
    ];

    private static $field_labels = [

        'InvoiceTax' => 'MwSt.',

        'PaymentTermDays' => 'Zahl der Tage für Zahlungsziel',
        'PaymentTermText' => 'Text für Zahlungsziel',
        'PaymentIBAN' => 'IBAN für Zahlung',
        'PaymentBIC' => 'BIC für Zahlung',
        'PaymentAccountHolder' => 'Kontoinhaber',

        'CompanyName' => 'Firmenname',
        'CompanyAddressLine1' => 'Firmenadresse Zeile 1',
        'CompanyAddressLine2' => 'Firmenadresse Zeile 2',
        'CompanyAddressLine3' => 'Firmenadresse Zeile 3',
        'CompanyPostalCode' => 'Firmenadresse PLZ',
        'CompanyCity' => 'Firmenadresse Ort',
        'CompanyCountry' => 'Firmenadresse Land (DE)',
        'CompanyContactPerson' => 'Firmenkontaktperson (Inhaber)',
        'CompanyContactDepartment' => 'Firmenkontakt Abteilung',
        'CompanyContactPhone' => 'Firmenkontakt Telefon',
        'CompanyContactEmail' => 'Firmenkontakt Email',
        'CompanyContactWebsite' => 'Firmenkontakt Webseite',
        'CompanyVATId' => 'USt-IdNr.',
        'CompanyTaxNumber' => 'Steuernummer',

        'Logo' => 'Firmenlogo (oben links auf Angeboten/Rechnungen)',

        'OfferIntroText' => 'Standard Einleitungstext für Angebote',
        'OfferOutroText' => 'Standard Schlusstext für Angebote',
        'InvoiceIntroText' => 'Standard Einleitungstext für Rechnungen',
        'InvoiceOutroText' => 'Standard Schlusstext für Rechnungen',

        'EmailOfferText' => 'Standard Text für Angebots-E-Mail',
        'EmailInvoiceText' => 'Standard Text für Rechnungs-E-Mail',
    ];

    public function getPaymentDetails() {
        $details = "";
        if ($this->owner->PaymentIBAN) {
            $details .= "IBAN: " . $this->owner->PaymentIBAN;
        }
        if ($this->owner->PaymentBIC) {
            if ($details) {
                $details .= "<br />";
            }
            $details .= "BIC: " . $this->owner->PaymentBIC;
        }
        if ($this->owner->PaymentAccountHolder) {
            if ($details) {
                $details .= "<br />";
            }
            $details .= "Kontoinhaber: " . $this->owner->PaymentAccountHolder;
        }
        return DBField::create_field('HTMLText', $details);
    }

    public function getTaxData() {
        $taxData = "";
        if ($this->owner->CompanyVATId) {
            $taxData .= "USt-IdNr.: " . $this->owner->CompanyVATId;
        }
        if ($this->owner->CompanyTaxNumber) {
            if ($taxData) {
                $taxData .= "<br />";
            }
            $taxData .= "Steuernummer: " . $this->owner->CompanyTaxNumber;
        }
        if ($this->owner->CompanyContactPerson) {
            if ($taxData) {
                $taxData .= "<br />";
            }
            $taxData .= "Inhaber: " . $this->owner->CompanyContactPerson;
        }
        return DBField::create_field('HTMLText', $taxData);
    }

    public function getCompanyContacts() {
        $contacts = "";
        if ($this->owner->CompanyContactDepartment) {
            if ($contacts) {
                $contacts .= "<br />";
            }
            $contacts .= $this->owner->CompanyContactDepartment;
        }
        if ($this->owner->CompanyContactPhone) {
            if ($contacts) {
                $contacts .= "<br />";
            }
            $contacts .= $this->owner->CompanyContactPhone;
        }
        if ($this->owner->CompanyContactEmail) {
            if ($contacts) {
                $contacts .= "<br />";
            }
            $contacts .= $this->owner->CompanyContactEmail;
        }
        if ($this->owner->CompanyContactWebsite) {
            if ($contacts) {
                $contacts .= "<br />";
            }
            $contacts .= $this->owner->CompanyContactWebsite;
        }
        return DBField::create_field('HTMLText', $contacts);
    }

    public function getCompanyDetails() {
        $details = $this->owner->CompanyName;
        if ($this->owner->CompanyAddressLine1) {
            $details .= "<br />" . $this->owner->CompanyAddressLine1;
        }
        if ($this->owner->CompanyAddressLine2) {
            $details .= "<br />" . $this->owner->CompanyAddressLine2;
        }
        if ($this->owner->CompanyAddressLine3) {
            $details .= "<br />" . $this->owner->CompanyAddressLine3;
        }
        if ($this->owner->CompanyPostalCode || $this->owner->CompanyCity) {
            $details .= "<br />";
            if ($this->owner->CompanyPostalCode) {
                $details .= $this->owner->CompanyPostalCode . " ";
            }
            if ($this->owner->CompanyCity) {
                $details .= $this->owner->CompanyCity;
            }
        }
        return DBField::create_field('HTMLText', $details);
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Firma', [
            UploadField::create('Logo', $this->owner->fieldLabel('Logo')),
            TextField::create('CompanyName', $this->owner->fieldLabel('CompanyName')),
            TextField::create('CompanyAddressLine1', $this->owner->fieldLabel('CompanyAddressLine1')),
            TextField::create('CompanyAddressLine2', $this->owner->fieldLabel('CompanyAddressLine2')),
            TextField::create('CompanyAddressLine3', $this->owner->fieldLabel('CompanyAddressLine3')),
            TextField::create('CompanyPostalCode', $this->owner->fieldLabel('CompanyPostalCode')),
            TextField::create('CompanyCity', $this->owner->fieldLabel('CompanyCity')),
            TextField::create('CompanyCountry', $this->owner->fieldLabel('CompanyCountry')),
            TextField::create('CompanyContactPerson', $this->owner->fieldLabel('CompanyContactPerson')),
            TextField::create('CompanyContactDepartment', $this->owner->fieldLabel('CompanyContactDepartment')),
            TextField::create('CompanyContactPhone', $this->owner->fieldLabel('CompanyContactPhone')),
            EmailField::create('CompanyContactEmail', $this->owner->fieldLabel('CompanyContactEmail')),
            TextField::create('CompanyContactWebsite', $this->owner->fieldLabel('CompanyContactWebsite')),
            TextField::create('CompanyVATId', $this->owner->fieldLabel('CompanyVATId')),
            TextField::create('CompanyTaxNumber', $this->owner->fieldLabel('CompanyTaxNumber')),
        ]);

        $fields->addFieldsToTab('Root.Rechnung', [
            NumericField::create('InvoiceTax', $this->owner->fieldLabel('InvoiceTax')),
            TextField::create('PaymentTermDays', $this->owner->fieldLabel('PaymentTermDays')),
            TextField::create('PaymentTermText', $this->owner->fieldLabel('PaymentTermText')),
            TextField::create('PaymentIBAN', $this->owner->fieldLabel('PaymentIBAN')),
            TextField::create('PaymentBIC', $this->owner->fieldLabel('PaymentBIC')),
            TextField::create('PaymentAccountHolder', $this->owner->fieldLabel('PaymentAccountHolder')),
        ]);

        $fields->addFieldsToTab('Root.Texte', [
            TextareaField::create('OfferIntroText', $this->owner->fieldLabel('OfferIntroText')),
            TextareaField::create('OfferOutroText', $this->owner->fieldLabel('OfferOutroText')),
            TextareaField::create('InvoiceIntroText', $this->owner->fieldLabel('InvoiceIntroText')),
            TextareaField::create('InvoiceOutroText', $this->owner->fieldLabel('InvoiceOutroText')),
            HTMLEditorField::create('EmailOfferText', $this->owner->fieldLabel('EmailOfferText')),
            HTMLEditorField::create('EmailInvoiceText', $this->owner->fieldLabel('EmailInvoiceText')),
        ]);
    }
}
