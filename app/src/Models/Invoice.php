<?php

namespace app\Models;

use app\Models\ClientObject;
use app\Models\InvoiceArticle;
use app\Pages\InvoicePage;
use SilverStripe\ORM\ArrayList;
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
        'Status' => 'Int', //'Enum("Draft,Sent,Paid", "Draft")', 0=Draft, 1=Sent, 2=Paid, 3=Cancelled (Storno)
        'DueDate' => 'Date',
    ];

    private static $has_one = [
        'ClientObject' => ClientObject::class,
        'Project' => Project::class,
        'Offer' => Offer::class,
        'CancellationOf' => Invoice::class,
        'RecurringInvoice' => RecurringInvoice::class,
    ];

    private static $has_many = [
        'InvoiceArticles' => InvoiceArticle::class,
    ];

    private static $cascade_deletes = [
        'InvoiceArticles',
    ];

    public function getNextInvoiceNumber()
    {
        $currentYear = date('Y');
    
        // Provjeri format trenutnog broja (YYYY-XXXX)
        if (preg_match('/^(\d{4})-(\d{4})$/', $this->InvoiceNumber, $matches)) {
            $yearFromInvoice = $matches[1];
            
            // Ako je godina na zadnjem računu ista kao trenutna, samo uvećaj broj
            if ($yearFromInvoice == $currentYear) {
                $number = (int)$matches[2] + 1;
                return sprintf('%s-%04d', $currentYear, $number);
            }
        }
        
        // Ako je godina drugačija (nova godina) ili format nije prepoznat, 
        // resetuj na 0001 za trenutnu godinu
        return $currentYear . '-0001';
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // set offer number if not set
        if ($this->InvoiceNumber == '') {
            $currentYear = date('Y');
    
            // Tražimo zadnji račun isključivo u TEKUĆOJ godini
            $lastInvoice = Invoice::get()
                ->filter(['InvoiceNumber:StartsWith' => $currentYear])
                ->sort('InvoiceNumber', 'DESC')
                ->first();

            if ($lastInvoice) {
                // Ako postoji račun u ovoj godini, nastavi niz
                $parts = explode('-', $lastInvoice->InvoiceNumber);
                $nextSequence = (int)$parts[1] + 1;
                $this->InvoiceNumber = $currentYear . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
            } else {
                // Ako je nova godina i nema još računa, kreni od 0001
                $this->InvoiceNumber = $currentYear . '-0001';
            }
        }

        if (!$this->DueDate) {
            $this->DueDate = date('Y-m-d', strtotime('+30 days'));
        }

    }

    public function getGermanStatusOptions(): array
    {
        return [
            0 => 'Entwurf',
            1 => 'Gesendet',
            2 => 'Bezahlt',
        ];
    }

    public function getGermanStatusOptionsDataList()
    {

        // return false if invoice is cancelled, paid or connected to a cancellation
        if ($this->Status >= 2 || $this->CancellationOfID > 0) {
            return false;
        }

        $myList = ArrayList::create();
        foreach ($this->getGermanStatusOptions() as $key => $value) {
            $myItem = [
                'isDisabled' => ($this->Status == 4 || $this->CancellationOfID > 0) ? 'true' : 'false',
                'isActive' => ($this->Status == $key) ? 'active' : '',
                'Key' => $key,
                'Title' => $value,
            ];
            $myList->push($myItem);
        }
        return $myList;
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

    public function getTotalBruttoAsString(): string
    {
        return  '€ ' . number_format($this->getTotalBrutto(), 2, ',', ' ');
    }

    public function getGermanStatus() {
        return match ($this->Status) {
            0 => 'Entwurf',
            1 => 'Gesendet',
            2 => 'Bezahlt',
            3 => 'Storniert',
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
            //$email->setBody(DBField::create_field('HTMLText', $config->EmailInvoiceText));
            $email->setBody($config->EmailInvoiceText);
            $email->addAttachmentFromData($pdfContent, 'Rechnung-' . $this->InvoiceNumber . '.pdf', 'application/pdf');
            $email->sendPlain();
            // update status if is Draft
            if ($this->Status == 0) {
                $this->Status = 1; // Sent
            }
            $this->write();
            return true;
        }
        return false;
    }

    public function checkEditingStatus() {
        if ($this->Status >= 2) { // not Draft
            return false;
        }
        return true;
    }


    // check if invoice can be cancelled
    // maybe can be used under checkEditingStatus function but for now separately
    public function canBeCancelled() {
        if ($this->Status >= 2 || $this->CancellationOfID > 0) { // Paid or already cancelled
            return false;
        }
        return true;
    }

    public function getFirstInvoicePage() {
        return InvoicePage::get()->first();
    }
}