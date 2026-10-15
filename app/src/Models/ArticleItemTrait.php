<?php

namespace app\Models;

trait ArticleItemTrait
{
    public function getUnitLabel()
    {
        $units = $this->getUnitOptions();
        return isset($units[$this->Unit]) ? $units[$this->Unit] : 'Unbekannt';
    }

    public function getUnitOptions()
    {
        return [
            0 => 'Stück',
            1 => 'm',
            2 => 'm²',
            3 => 'm³',
            4 => 'l',
            5 => 'kg',
            6 => 'Stunde',
            7 => 'Sonstige',
        ];
    }

    // return unit by __BT-130 format
    public function getUnitShortLabel()
    {
        $units = [
            0 => 'STK',
            1 => 'M',
            2 => 'M2',
            3 => 'M3',
            4 => 'L',
            5 => 'KG',
            6 => 'STD',
            7 => 'SNS',
        ];
        return isset($units[$this->Unit]) ? $units[$this->Unit] : 'UNB';
    }

    public function formatPrice($price, $withCurrency = true) {
        $value = number_format($price, 2, ',', '.') . ' €';
        return $withCurrency ? $value : rtrim($value, ' €');
    }
}
