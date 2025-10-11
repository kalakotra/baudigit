<?php

namespace App\Pages;

use App\Pages\OfferPage;

class InvoicePage extends OfferPage
{
    private $invoiceNumber;
    private $invoiceDate;

    public function __construct($invoiceNumber, $invoiceDate)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceDate = $invoiceDate;
    }

    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    public function render()
    {
        // Custom rendering logic for the invoice page
    }
}