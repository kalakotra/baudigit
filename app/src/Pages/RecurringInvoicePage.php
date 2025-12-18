<?php

namespace app\Pages;

use Kalakotra\Utils\PagePermissions;
use app\Controllers\RecurringInvoicePageController;
use SilverStripe\Security\PermissionProvider;

class RecurringInvoicePage extends \Page implements PermissionProvider
{
    use PagePermissions;

    private static $table_name = 'app_RecurringInvoicePage';

    private static $singular_name = 'Wiederkehrende-Rechnung-Seite';
    private static $plural_name = 'Wiederkehrende-Rechnung-Seiten';

    // define controller for this page type
    private static $controller_name = RecurringInvoicePageController::class;

}