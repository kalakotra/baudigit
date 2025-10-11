<?php

namespace app\Pages;

use Kalakotra\Utils\PagePermissions;
use app\Controllers\ProjectPageController;
use SilverStripe\Security\PermissionProvider;

class ProjectPage extends \Page implements PermissionProvider
{
    use PagePermissions;

    private static $table_name = 'app_ProjectPage';

    private static $singular_name = 'Projekt-Seite';
    private static $plural_name = 'Projekt-Seiten';

    // define controller for this page type
    private static $controller_name = ProjectPageController::class;
}