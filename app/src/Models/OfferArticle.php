<?php

namespace app\Models;

use app\Models\Offer;
use app\Models\Article;

class OfferArticle extends Article
{
    private static $table_name = 'app_OfferArticle';

    private static $db = [
        'Quantity' => 'Decimal(10,2)', // quantity of the article
        'Discount' => 'Decimal(10,2)', // discount in percent
        'TaxRate' => 'Decimal(10,2)', // tax rate in percent
        'FinalPrice' => 'Decimal(19,2)', // final price after discount and tax
    ];

    private static $has_one = [
        'Offer' => Offer::class,
        'SourceArticle' => Article::class, // link to the original article
    ];

    public function onBeforeWrite() {
        parent::onBeforeWrite();
        // calculate final price
        $netPrice = $this->SellPriceNetto * $this->Quantity;
        $discountAmount = $netPrice * ($this->Discount / 100);
        $priceAfterDiscount = $netPrice - $discountAmount;
        $taxAmount = $priceAfterDiscount * ($this->TaxRate / 100);
        $this->FinalPrice = $priceAfterDiscount + $taxAmount;
    }

    public function getDiscountedPrice(): float|int {
        $netPrice = $this->SellPriceNetto * $this->Quantity;
        $discountAmount = $netPrice * ($this->Discount / 100);
        return $netPrice - $discountAmount;
    }

    public function getDiscountAmount(): float|int {
        $netPrice = $this->SellPriceNetto * $this->Quantity;
        return $netPrice * ($this->Discount / 100);
    }

    public function getTaxAmount(): float|int {
        $priceAfterDiscount = $this->getDiscountedPrice();
        return $priceAfterDiscount * ($this->TaxRate / 100);
    }
}