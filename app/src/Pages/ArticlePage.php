<?php

namespace app\Pages;

use app\Models\Article;
use Kalakotra\Utils\PagePermissions;
use app\Controllers\ArticlePageController;
use SilverStripe\Security\PermissionProvider;

class ArticlePage extends \Page implements PermissionProvider
{
    use PagePermissions;

    private static $table_name = 'app_ArticlePage';

    private static $singular_name = 'Artikel-Seite';
    private static $plural_name = 'Artikel-Seiten';

    // define controller for this page type
    private static $controller_name = ArticlePageController::class;


}