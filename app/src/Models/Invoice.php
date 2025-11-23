<?php

namespace app\Models;

use app\Models\ClientObject;
use app\Models\InvoiceArticle;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\i18n\i18nEntityProvider;
use Kalakotra\Utils\DataObjectPermissions;
use SilverStripe\Security\PermissionProvider;

class Invoice extends DataObject implements i18nEntityProvider, PermissionProvider
{
    use DataObjectPermissions;

    private static $table_name = 'app_Invoice';

    private static $singular_name = 'Rechnung';
    private static $plural_name = 'Rechnungen';

    private static $default_sort = 'Created DESC';
    public function provideI18nEntities()
    {
        return [
            __CLASS__ . '.SINGULARNAME' => 'Rechnung',
            __CLASS__ . '.PLURALNAME' => 'Rechnungen',
            __CLASS__ . '.PLURALS' => [
                'one' => 'Rechnung',
                'other' => '{count} Rechnungen',
            ],
        ];
    }
    private static $db = [
        'InvoiceNumber' => 'Varchar',
        'TotalAmount' => 'Decimal(19,2)',
        'IntroText' => 'Text',
        'OutroText' => 'Text',
        'Status' => 'Int', //'Enum("Draft,Sent,Paid", "Draft")', 0=Draft, 1=Sent, 2=Paid, 
        'DueDate' => 'Date',
    ];

    private static $has_one = [
        'ClientObject' => ClientObject::class,
        'Project' => Project::class,
        'Offer' => Offer::class,
    ];

    private static $has_many = [
        'InvoiceArticles' => InvoiceArticle::class,
    ];

    private static $cascade_deletes = [
        'InvoiceArticles',
    ];

    public function getNextInvoiceNumber()
    {
        if (preg_match('/^(\\d{4})-(\\d{4})$/', $this->InvoiceNumber, $matches)) {
            $year = $matches[1];
            $number = (int)$matches[2] + 1;
            return sprintf('%s-%04d', $year, $number);
        }
        return $this->InvoiceNumber;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // set offer number if not set
        if ($this->InvoiceNumber == '') {
            $lastInvoice = Invoice::get()->sort('Created', 'DESC')->first();
            if ($lastInvoice && preg_match('/^(\\d{4})-(\\d{4})$/', $lastInvoice->InvoiceNumber)) {
                $this->InvoiceNumber = $lastInvoice->getNextInvoiceNumber();
            } else {
                $this->InvoiceNumber = date('Y') . '-0001';
            }
        }
        if (!$this->DueDate) {
            $this->DueDate = date('Y-m-d', strtotime('+30 days'));
        }

    }

    public function getTotalNetto(): float|int
    {
        $total = 0;
        foreach ($this->InvoiceArticles() as $article) {
            $total += $article->getDiscountedPrice();
        }
        return $total;
    }

    public function getTotalTax(): float|int
    {
        $total = 0;
        foreach ($this->InvoiceArticles() as $article) {
            $total += $article->getTaxAmount();
        }
        return $total;
    }

    public function getTotalBrutto(): float|int
    {
        $myBrutto = $this->getTotalNetto() + $this->getTotalTax();
        // show always 2 decimal places
        return (float) number_format($myBrutto, 2, '.', '');
    }

    public function getGermanStatus() {
        return match ($this->Status) {
            0 => 'Entwurf',
            1 => 'Gesendet',
            2 => 'Bezahlt',
            default => 'Unbekannt'
        };
    }

    public function getPDF() {
        $controller = Controller::curr();
        $html = $controller->customise(['Invoice' => $this])->renderWith('app/Pages/Layout/PDF/Invoice');
        $filename = "rechnung-".$this->ID.".pdf";
        $pdfPath = $controller->generatePDF($html, $filename);

        $controller->getResponse()->addHeader('Content-Type', 'application/pdf');
        $controller->getResponse()->addHeader('Content-Disposition', 'inline; filename="'.$filename.'"');

        return file_get_contents($pdfPath);
    }

    public function sendInvoiceEmail() {
        $client = $this->ClientObject()->Client();
        if ($client && $client->Email) {
            $config = SiteConfig::current_site_config();
            $pdfContent = $this->getPDF();
            $email = Email::create();
            $email->setTo($client->Email);
            $email->setFrom($config->CompanyContactEmail);
            $email->setSubject('Rechnung ' . $this->InvoiceNumber);
            $email->setBody(DBField::create_field('HTMLText', $config->EmailInvoiceText));
            $email->addAttachmentFromData($pdfContent, 'Rechnung-' . $this->InvoiceNumber . '.pdf', 'application/pdf');
            $email->send();
            //$this->Status = 1; // Sent
            $this->write();
            return true;
        }
        return false;
    }
}