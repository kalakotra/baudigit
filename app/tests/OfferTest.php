<?php

namespace app\Tests;

use app\Models\Offer;
use app\Models\OfferArticle;
use SilverStripe\Dev\SapphireTest;

class OfferTest extends SapphireTest
{
    // Important: DO NOT use database, we are just mocking
    protected $usesDatabase = false;

    // Override tearDownAfterClass to prevent DB interaction from SapphireTest
    public static function tearDownAfterClass(): void
    {
        // Do nothing to prevent database connection attempts
    }

    public function testGetTotalNettoWithNoArticles()
    {
        $offer = $this->getMockBuilder(Offer::class)
            ->addMethods(['OfferArticles'])
            ->getMock();

        $offer->method('OfferArticles')->willReturn([]);

        $this->assertEquals(0, $offer->getTotalNetto());
    }

    public function testGetTotalNettoWithArticles()
    {
        $offer = $this->getMockBuilder(Offer::class)
            ->addMethods(['OfferArticles'])
            ->getMock();

        $article1 = $this->getMockBuilder(OfferArticle::class)
            ->onlyMethods(['getDiscountedPrice'])
            ->getMock();
        $article1->method('getDiscountedPrice')->willReturn(180.0);

        $article2 = $this->getMockBuilder(OfferArticle::class)
            ->onlyMethods(['getDiscountedPrice'])
            ->getMock();
        $article2->method('getDiscountedPrice')->willReturn(50.0);

        $offer->method('OfferArticles')->willReturn([$article1, $article2]);

        $this->assertEquals(230.0, $offer->getTotalNetto());
    }

    public function testGetTotalNettoWithNegativePrice()
    {
        $offer = $this->getMockBuilder(Offer::class)
            ->addMethods(['OfferArticles'])
            ->getMock();

        $article1 = $this->getMockBuilder(OfferArticle::class)
            ->onlyMethods(['getDiscountedPrice'])
            ->getMock();
        $article1->method('getDiscountedPrice')->willReturn(180.0);

        $article2 = $this->getMockBuilder(OfferArticle::class)
            ->onlyMethods(['getDiscountedPrice'])
            ->getMock();
        $article2->method('getDiscountedPrice')->willReturn(-50.0);

        $offer->method('OfferArticles')->willReturn([$article1, $article2]);

        $this->assertEquals(130.0, $offer->getTotalNetto());
    }
}
