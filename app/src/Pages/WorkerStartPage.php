<?php

namespace app\Pages;

use Kalakotra\Utils\PagePermissions;
use SilverStripe\Security\PermissionProvider;

class WorkerStartPage extends \Page implements PermissionProvider
{
    use PagePermissions;
    private static $table_name = 'app_WorkerStartPage';

    private static $singular_name = 'Mitarbeiter Startseite';

    private static $plural_name = 'Mitarbeiter Startseiten';

    private static $description = 'Startseite für Mitarbeiter nach dem Login';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }
}