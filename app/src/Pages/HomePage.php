<?php

namespace app\Pages;

use Kalakotra\Utils\PagePermissions;
use app\Controllers\HomePageController;
use SilverStripe\Security\PermissionProvider;

class HomePage extends \Page implements PermissionProvider
{
    use PagePermissions;
    
    private static $table_name = 'app_HomePage';

    private static $singular_name = 'Startseite';
    private static $plural_name = 'Startseiten';

    private static $controller_name = HomePageController::class;
}