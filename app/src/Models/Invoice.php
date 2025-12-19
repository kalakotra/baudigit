<?php

namespace app\Models;

use app\Pages\InvoicePage;
use app\Models\ClientObject;
use app\Models\InvoiceArticle;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use horstoeko\zugferd\ZugferdProfiles;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\i18n\i18nEntityProvider;
use Kalakotra\Utils\DataObjectPermissions;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use SilverStripe\Security\PermissionProvider;
use horstoeko\zugferd\ZugferdDocumentPdfMerger;

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

    public function getTotalNetto($raw = false)
    {
        $total = 0;
        foreach ($this->InvoiceArticles() as $article) {
            $total += $article->getDiscountedPrice();
        }
        return $total;
    }

    public function getTotalTax($raw = false)
    {
        $total = 0;
        foreach ($this->InvoiceArticles() as $article) {
            $total += $article->getTaxAmount();
        }
        return $total;
    }

    public function getTotalBrutto($raw = false): float|int
    {
        $myBrutto = $this->getTotalNetto() + $this->getTotalTax();
        // show always 2 decimal places
        if ($raw === false) {
            return (float) number_format($myBrutto, 2, ',', '');
        } else {
            return (float) $myBrutto;
        }
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

    public function generateEInvoiceXML() {
        $config = SiteConfig::current_site_config();
        $invoiceData = $this;
        $invoiceDate = \DateTime::createFromFormat("Ymd", $invoiceData->dbObject("Created")->Format("yyyyMMdd"));
        $paymentDate = clone $invoiceDate;
        if ($config->PaymentTermDays) {
            $paymentDate->modify('+ '.$config->PaymentTermDays.' days');
        } else {
            $paymentDate->modify('+ 10 days');
        }

        $document = ZugferdDocumentBuilder::CreateNew(ZugferdProfiles::PROFILE_XRECHNUNG_3);
        $document->setDocumentInformation($invoiceData->InvoiceNumber, "380", $invoiceDate, "EUR");
        
        $document->setDocumentSupplyChainEvent($invoiceDate);

        $document->setDocumentBusinessProcess("urn:fdc:peppol.eu:2017:poacc:billing:01:1.0")
            ->setDocumentSeller($config->CompanyName)
            ->setDocumentSellerAddress($config->CompanyAddressLine1, $config->CompanyAddressLine2, $config->CompanyAddressLine3, $config->CompanyPostalCode, $config->CompanyCity, $config->CompanyCountry)
            ->setDocumentSellerContact($config->CompanyContactPerson, "", $config->CompanyContactPhone, "", $config->CompanyContactEmail)
            ->setDocumentSellerCommunication("EM", $config->CompanyContactEmail)

            ->addDocumentNote($invoiceData->IntroText)
            ->setDocumentSummation($invoiceData->getTotalBrutto(true), $invoiceData->getTotalBrutto(true), $invoiceData->getTotalNetto(true), 0.0, 0.0, $invoiceData->getTotalNetto(true), $invoiceData->getTotalTax(true), null, 0.0)

            ->addDocumentSellerTaxRegistration("VA", $config->CompanyVATId)
            ->addDocumentTax("S", "VAT", $invoiceData->getTotalNetto(true), $invoiceData->getTotalTax(), $config->InvoiceTax);

        $paymenTermText = sprintf($config->PaymentTermText, $config->PaymentTermDays ?? 10);
        $document->addDocumentPaymentTerm($paymenTermText, $invoiceData->paymentDate);
        $document->addDocumentPaymentMean("42", "Payment to bank account", "", "" ,"" ,"", $config->PaymentIBAN, $config->PaymentAccountHolder, "", $config->PaymentBIC);

        $document->setDocumentBuyer($invoiceData->ClientObject()->Client()->Company);
        $document->setDocumentBuyerReference($invoiceData->ClientObject()->ClientID);

        if($invoiceData->ClientObject()->Client()->Email) {
            $document->setDocumentBuyerCommunication("EM", $invoiceData->ClientObject()->Client()->Email);
        }else if($invoiceData->ClientObject()->ContactEmail) {
            $document->setDocumentBuyerCommunication("EM", $invoiceData->ClientObject()->ContactEmail);
        }

        $addressLineOne = $invoiceData->ClientObject()->Client()->Address;
        $addressLineTwo = "";
        $addressLineThree = "";
        $docPostcode = $invoiceData->ClientObject()->Client()->PostalCode;
        $docCity = $invoiceData->ClientObject()->Client()->City;
        $docCountry = "DE";

        $document->setDocumentBuyerAddress($addressLineOne, $addressLineTwo, $addressLineThree, $docPostcode, $docCity, $docCountry);

        $itemList = $invoiceData->InvoiceArticles();
        $i = 0;
        foreach($itemList as $item) {
            $document->addNewPosition($i)
                ->setDocumentPositionProductDetails($item->Title, $item->Description);

            $document->setDocumentPositionQuantity($item->Quantity, $item->getUnitShortLabel());

            //setDocumentPositionNetPrice(float $amount, ?float $basisQuantity = null, ?string $basisQuantityUnitCode = null)
            $document->setDocumentPositionNetPrice((float)$item->SellPriceNetto);

            $document->addDocumentPositionTax("S", "VAT", $config->InvoiceTax);

            //$document->setDocumentPositionLineSummation((float)$item->getBruttoPrice());

            /**
             * Set information on item totals (with support for EXTENDED profile).
             *
             * @param  float $lineTotalAmount            __BT-131, From BASIC__ The total amount of the invoice item
             * @param  float $chargeTotalAmount          __BT-X-327, From EXTENDED__ Total amount of item surcharges
             * @param  float $allowanceTotalAmount       __BT-X-328, From EXTENDED__ Total amount of item discounts
             * @param  float $taxTotalAmount             __BT-X-329, From EXTENDED__ Total amount of item taxes
             * @param  float $grandTotalAmount           __BT-X-330, From EXTENDED__ Total gross amount of the item
             * @param  float $totalAllowanceChargeAmount __BT-X-98, From EXTENDED__ Total amount of item surcharges and discounts
             * @return ZugferdDocumentBuilder
             */
            $document->setDocumentPositionLineSummationExt((float) $item->SellPriceNetto, null, (float) $item->getDiscountAmount(), (float) $item->getTaxAmount(), (float)$item->getBruttoPrice(), (float) $item->getDiscountAmount());

            $i++;
        }

        return $document;
    }

    public function getPDF() {
        $controller = Controller::curr();
        $html = $controller->customise(['Invoice' => $this])->renderWith('app/Pages/Layout/PDF/Invoice');
        $filename = "rechnung-".$this->ID.".pdf";
        $pdfPath = $controller->generatePDF($html, $filename);


        // need some check if the XML is generated correctly !!!
        //$pdfMerger = new ZugferdDocumentPdfMerger($this->generateEInvoiceXML(), $pdfPath);
        //$pdfMerger->generateDocument()
        //    ->saveDocument($pdfPath);

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