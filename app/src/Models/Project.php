<?php

namespace app\Models;

use app\Models\Client;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\i18n\i18nEntityProvider;
use Kalakotra\Utils\DataObjectPermissions;
use SilverStripe\Security\Member;
use SilverStripe\Security\PermissionProvider;


class Project extends DataObject implements PermissionProvider, i18nEntityProvider
{
    use DataObjectPermissions;

    private static $table_name = 'app_Project';

    private static $singular_name = 'Projekt';

    private static $plural_name = 'Projekte';

    private static $default_sort = 'Status ASC, Created DESC';

    public function provideI18nEntities()
    {
        return [
            __CLASS__ . '.SINGULARNAME' => 'Projekt',
            __CLASS__ . '.PLURALNAME' => 'Projekte',
            __CLASS__ . '.PLURALS' => [
                'one' => 'Projekt',
                'other' => '{count} Projekte',
            ],
        ];
    }

    private static $db = [
        'Status' => 'Int', // 0=Planned/New, 1=InProgress, 2=Completed, 3=Billed, 4=Canceled
        'Title' => 'Varchar(255)',
        'Description' => 'Text',
        //'Date' => 'Datetime',
    ];

    private static $has_one = [
        'ClientObject' => ClientObject::class,
    ];

    private static $has_many = [
        'Documents' => File::class
    ];

    private static $many_many = [
        'Members' => Member::class,
    ];

    private static $summary_fields = [
        'Title',
        'Client.Company',
        'ClientObject.Name',
    ];

    // german field labels
    private static $field_labels = [
        'Title' => 'Titel',
        'Description' => 'Beschreibung',
        'Date' => 'Datum',
        'Client.Company' => 'Kunde',
        'ClientObject.Name' => 'Kundenobjekt',
    ];

    public function getStatusLabel()
    {
        switch ($this->Status) {
            case 0:
                return 'Neu';
            case 1:
                return 'Laufend';
            case 2:
                return 'Abgeschlossen';
            case 3:
                return 'Abgerechnet';
            case 4:
                return 'Storniert';
            default:
                return 'Unbekannt';
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Status',
            'ClientObjectID'
        ]);

        if ($this->ID) {
            $fields->addFieldToTab('Root.Main', DropdownField::create(
                'Status',
                'Status',
                [
                    0 => 'Neu',
                    1 => 'Laufend',
                    2 => 'Abgeschlossen',
                    3 => 'Abgerechnet',
                    4 => 'Storniert',
                ]
            ), 'Title');
        }

        if ($this->ClientID) {
            $fields->addFieldToTab('Root.Main', DropdownField::create(
                'ClientObjectID',
                'Kundenobjekt',
                $this->Client()->ClientObjects()->map('ID', 'Name')->toArray()
            ), );
        }

        
        return $fields;
    }
}