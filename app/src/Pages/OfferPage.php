<?php

namespace app\Pages;

use Kalakotra\Utils\PagePermissions;
use app\Controllers\OfferPageController;
use SilverStripe\Security\PermissionProvider;

class OfferPage extends \Page implements PermissionProvider
{
    use PagePermissions;

    private static $table_name = 'app_OfferPage';

    private static $singular_name = 'Angebots-Seite';
    private static $plural_name = 'Angebots-Seiten';

    // define controller for this page type
    private static $controller_name = OfferPageController::class;
}