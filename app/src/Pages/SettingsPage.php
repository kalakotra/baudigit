<?php

namespace app\Pages;

use Kalakotra\Utils\PagePermissions;
use app\Controllers\SettingsPageController;
use SilverStripe\Security\PermissionProvider;

class SettingsPage extends \Page implements PermissionProvider
{
    use PagePermissions;

    private static $table_name = 'app_SettingsPage';

    private static $singular_name = 'Einstellungen-Seite';
    private static $plural_name = 'Einstellungen-Seiten';

    // define controller for this page type
    private static $controller_name = SettingsPageController::class;

}