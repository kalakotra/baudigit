<?php

namespace app\Extensions;

use PharIo\Manifest\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

class MemberExtension extends DataExtension
{

    private static $db = [
        'MemberColor' => 'Varchar(7)' // Hex color code for the member
    ];
    public function onBeforeWrite() {
        parent::onBeforeWrite();
        if (!$this->owner->MemberColor) {
            // generate a random hex color code
            $this->owner->MemberColor = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        }
    }
}