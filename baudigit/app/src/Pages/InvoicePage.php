<?php

namespace App\Pages;

use App\Pages\OfferPage;

class InvoicePage extends OfferPage
{
    private $invoiceNumber;
    private $invoiceDate;
    private $customerDetails;

    public function __construct($invoiceNumber, $invoiceDate, $customerDetails)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceDate = $invoiceDate;
        $this->customerDetails = $customerDetails;
    }

    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    public function getCustomerDetails()
    {
        return $this->customerDetails;
    }

    public function render()
    {
        // Custom rendering logic for the invoice page
    }
}