<?php

namespace app\Admin;

use SilverStripe\Admin\ModelAdmin;
use app\Models\ProjectDocumentCategory;


class ProjectDocumentAdmin extends ModelAdmin
{
    private static $managed_models = [
        ProjectDocumentCategory::class,
    ];

    private static $url_segment = 'project-documents';

    private static $menu_title = 'Projektdokumente';
}