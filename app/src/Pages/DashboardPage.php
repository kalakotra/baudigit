<?php

namespace app\Pages;

use Kalakotra\Utils\PagePermissions;
use app\Controllers\DashboardPageController;
use SilverStripe\Security\PermissionProvider;

class DashboardPage extends \Page implements PermissionProvider
{
    use PagePermissions;

    private static $table_name = 'app_DashboardPage';

    private static $singular_name = 'Dashboard-Seite';
    private static $plural_name = 'Dashboard-Seiten';

    // define controller for this page type
    private static $controller_name = DashboardPageController::class;

}