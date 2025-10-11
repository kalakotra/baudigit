<?php

namespace app\Pages;

use Kalakotra\Utils\PagePermissions;
use app\Controllers\MemberPageController;
use SilverStripe\Security\PermissionProvider;

class MemberPage extends \Page implements PermissionProvider
{
    use PagePermissions;

    private static $table_name = 'app_MemberPage';

    private static $singular_name = 'Benutzer-Seite';
    private static $plural_name = 'Benutzer-Seiten';

    // define controller for this page type
    private static $controller_name = MemberPageController::class;
}