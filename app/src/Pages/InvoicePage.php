<?php

namespace app\Pages;

use Kalakotra\Utils\PagePermissions;
use app\Controllers\InvoicePageController;
use SilverStripe\Security\PermissionProvider;

class InvoicePage extends \Page implements PermissionProvider
{
    use PagePermissions;

    private static $table_name = 'app_InvoicePage';

    private static $singular_name = 'Rechnungs-Seite';
    private static $plural_name = 'Rechnungs-Seiten';

    // define controller for this page type
    private static $controller_name = InvoicePageController::class;
}
