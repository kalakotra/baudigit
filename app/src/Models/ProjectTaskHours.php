<?php

namespace app\Models;

use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Forms\DropdownField;
use SilverStripe\i18n\i18nEntityProvider;
use Kalakotra\Utils\DataObjectPermissions;
use SilverStripe\Security\PermissionProvider;

class ProjectTaskHours extends DataObject implements PermissionProvider, i18nEntityProvider
{
    use DataObjectPermissions;

    private static $table_name = 'app_ProjectTaskHours';

    private static $singular_name = 'Stunde';

    private static $plural_name = 'Stunden';

    private static $default_sort = 'Created DESC';

    public function provideI18nEntities()
    {
        return [
            __CLASS__ . '.SINGULARNAME' => 'Projektaufgabe Stunde',
            __CLASS__ . '.PLURALNAME' => 'Projektaufgabe Stunden',
            __CLASS__ . '.PLURALS' => [
                'one' => 'Projektaufgabe Stunde',
                'other' => '{count} Projektaufgabe Stunden',
            ],
        ];
    }

    private static $db = [
        'Hours' => 'Decimal(10,2)',
        'Description' => 'Text',
        'Signature' => 'HTMLText',
    ];

    private static $has_one = [
        'ProjectTask' => ProjectTask::class,
        'LoggedBy' => Member::class,
    ];

    private static $many_many = [
        'TaskImages' => Image::class,
    ];

    private static $owns = [
        'TaskImages',
    ];

    private static $cascade_deletes = [
        'TaskImages',
    ];

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // If ProjectTask status is ToDo (0), change it to InProgress (1)
        $projectTask = $this->ProjectTask();
        if ($projectTask && $projectTask->Status == 0) {
            $projectTask->Status = 1; // InProgress
            $projectTask->write();
        }
    }
}