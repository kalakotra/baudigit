<?php

namespace app\Pages;

use Kalakotra\Utils\PagePermissions;
use SilverStripe\Security\PermissionProvider;
use app\Controllers\WorkerTasksPageController;

class WorkerTasksPage extends \Page implements PermissionProvider
{
    use PagePermissions;

    private static $table_name = 'app_WorkerTasksPage';

    private static $singular_name = 'Mitarbeiter Aufgaben Seite';

    private static $plural_name = 'Mitarbeiter Aufgaben Seiten';

    private static $description = 'Seite für Mitarbeiter um ihre Aufgaben zu verwalten';

    private static $controller_name = WorkerTasksPageController::class;
    
}