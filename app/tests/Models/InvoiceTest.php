<?php

namespace app\Tests\Models;

use app\Models\Invoice;
use app\Models\InvoiceArticle;
use app\Models\ClientObject;
use app\Models\Client;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;

class InvoiceTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testGenerateEInvoiceXML()
    {
        $config = SiteConfig::current_site_config();
        $config->PaymentTermDays = 14;
        $config->CompanyName = "Test Company";
        $config->CompanyAddressLine1 = "Test Street 1";
        $config->CompanyPostalCode = "12345";
        $config->CompanyCity = "Test City";
        $config->CompanyCountry = "DE";
        $config->CompanyContactPerson = "Test Person";
        $config->CompanyContactEmail = "test@example.com";
        $config->CompanyVATId = "DE123456789";
        $config->InvoiceTax = 19;
        $config->PaymentTermText = "Zahlbar innerhalb von %s Tagen.";
        $config->PaymentIBAN = "DE12345678901234567890";
        $config->PaymentAccountHolder = "Test Account Holder";
        $config->PaymentBIC = "TESTDEF1XXX";
        $config->write();

        $client = Client::create();
        $client->Company = "Client Company";
        $client->Address = "Client Street 1";
        $client->PostalCode = "54321";
        $client->City = "Client City";
        $client->Email = "client@example.com";
        $client->write();

        $clientObject = ClientObject::create();
        $clientObject->ClientID = $client->ID;
        $clientObject->write();

        $invoice = Invoice::create();
        $invoice->InvoiceNumber = "INV-001";
        $invoice->ClientObjectID = $clientObject->ID;
        $invoice->IntroText = "Test Intro Text";
        $invoice->Created = '2023-10-26 12:00:00';
        $invoice->write();

        $article = InvoiceArticle::create();
        $article->InvoiceID = $invoice->ID;
        $article->Title = "Test Article";
        $article->Description = "Test Description";
        $article->Quantity = 2;
        $article->Unit = "h";
        $article->SellPriceNetto = 50.00;
        $article->write();

        $document = $invoice->generateEInvoiceXML();

        // Ensure we got a ZugferdDocumentBuilder object back
        $this->assertInstanceOf(\horstoeko\zugferd\ZugferdDocumentBuilder::class, $document);

        // Get the XML string to perform assertions on its content
        $xml = $document->getContent();

        // Basic Assertions to check if the data from config and invoice are present in the XML
        $this->assertStringContainsString("Test Company", $xml);
        $this->assertStringContainsString("INV-001", $xml);
        $this->assertStringContainsString("Client Company", $xml);
        $this->assertStringContainsString("Test Article", $xml);
        $this->assertStringContainsString("100", $xml); // 2 * 50
    }

    public function testGenerateEInvoiceXMLWithoutPaymentTermDays()
    {
        $config = SiteConfig::current_site_config();
        $config->PaymentTermDays = 0; // Or null
        $config->CompanyName = "Test Company";
        $config->CompanyAddressLine1 = "Test Street 1";
        $config->CompanyPostalCode = "12345";
        $config->CompanyCity = "Test City";
        $config->CompanyCountry = "DE";
        $config->CompanyContactPerson = "Test Person";
        $config->CompanyContactEmail = "test@example.com";
        $config->CompanyVATId = "DE123456789";
        $config->InvoiceTax = 19;
        $config->PaymentTermText = "Zahlbar innerhalb von %s Tagen.";
        $config->PaymentIBAN = "DE12345678901234567890";
        $config->PaymentAccountHolder = "Test Account Holder";
        $config->PaymentBIC = "TESTDEF1XXX";
        $config->write();

        $client = Client::create();
        $client->Company = "Client Company";
        $client->Address = "Client Street 1";
        $client->PostalCode = "54321";
        $client->City = "Client City";
        // Do not set client email here to test fallback to ContactEmail
        $client->write();

        $clientObject = ClientObject::create();
        $clientObject->ClientID = $client->ID;
        $clientObject->ContactEmail = "contact@example.com";
        $clientObject->write();

        $invoice = Invoice::create();
        $invoice->InvoiceNumber = "INV-002";
        $invoice->ClientObjectID = $clientObject->ID;
        $invoice->IntroText = "Test Intro Text";
        $invoice->Created = '2023-10-26 12:00:00';
        $invoice->write();

        $article = InvoiceArticle::create();
        $article->InvoiceID = $invoice->ID;
        $article->Title = "Test Article 2";
        $article->Description = "Test Description 2";
        $article->Quantity = 1;
        $article->Unit = "h";
        $article->SellPriceNetto = 150.00;
        $article->write();

        $document = $invoice->generateEInvoiceXML();
        $this->assertInstanceOf(\horstoeko\zugferd\ZugferdDocumentBuilder::class, $document);

        $xml = $document->getContent();
        $this->assertStringContainsString("INV-002", $xml);
        $this->assertStringContainsString("Zahlbar innerhalb von 0 Tagen.", $xml);

        // contact email check
        $this->assertStringContainsString("contact@example.com", $xml);
    }
}
