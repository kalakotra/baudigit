<?php

namespace app\Tests\Models;

use app\Models\Invoice;
use app\Models\InvoiceArticle;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    public function testGetTotalNetto()
    {
        $invoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->addMethods(['InvoiceArticles'])
            ->getMock();

        $article1 = $this->createPartialMock(InvoiceArticle::class, ['getDiscountedPrice']);
        $article1->method('getDiscountedPrice')->willReturn(10.0);

        $article2 = $this->createPartialMock(InvoiceArticle::class, ['getDiscountedPrice']);
        $article2->method('getDiscountedPrice')->willReturn(20.5);

        $invoice->method('InvoiceArticles')->willReturn([$article1, $article2]);

        $this->assertEquals(30.5, $invoice->getTotalNetto());
    }

    public function testGetTotalNettoWithRaw()
    {
        $invoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->addMethods(['InvoiceArticles'])
            ->getMock();

        $article1 = $this->createPartialMock(InvoiceArticle::class, ['getDiscountedPrice']);
        $article1->method('getDiscountedPrice')->willReturn(15.75);

        $article2 = $this->createPartialMock(InvoiceArticle::class, ['getDiscountedPrice']);
        $article2->method('getDiscountedPrice')->willReturn(25.25);

        $invoice->method('InvoiceArticles')->willReturn([$article1, $article2]);

        $this->assertEquals(41.0, $invoice->getTotalNetto(true));
    }

    public function testGetTotalNettoEmpty()
    {
        $invoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->addMethods(['InvoiceArticles'])
            ->getMock();

        $invoice->method('InvoiceArticles')->willReturn([]);

        $this->assertEquals(0, $invoice->getTotalNetto());
    }
}
