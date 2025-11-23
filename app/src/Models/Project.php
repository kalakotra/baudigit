<?php

namespace app\Models;

use app\Models\Offer;
use app\Models\Client;
use app\Models\ProjectTask;
use app\Models\ProjectMember;
use SilverStripe\Assets\File;
use app\Models\ProjectDocument;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Forms\DropdownField;
use app\Models\ProjectDocumentCategory;
use SilverStripe\i18n\i18nEntityProvider;
use Kalakotra\Utils\DataObjectPermissions;
use SilverStripe\Security\PermissionProvider;


class Project extends DataObject implements PermissionProvider, i18nEntityProvider
{
    use DataObjectPermissions;

    private static $table_name = 'app_Project';

    private static $singular_name = 'Projekt';

    private static $plural_name = 'Projekte';

    private static $default_sort = 'Status ASC, Created DESC';

    public function provideI18nEntities()
    {
        return [
            __CLASS__ . '.SINGULARNAME' => 'Projekt',
            __CLASS__ . '.PLURALNAME' => 'Projekte',
            __CLASS__ . '.PLURALS' => [
                'one' => 'Projekt',
                'other' => '{count} Projekte',
            ],
        ];
    }

    private static $db = [
        'Status' => 'Int', // 0=Planned/New, 1=InProgress, 2=Completed, 3=Billed, 4=Canceled
        'Title' => 'Varchar(255)',
        'Description' => 'Text',
        'StartDate' => 'Date',
        'EndDate' => 'Date',
    ];

    private static $has_one = [
        'ClientObject' => ClientObject::class,
        'Offer' => Offer::class,
    ];

    private static $has_many = [
        'ProjectDocuments' => ProjectDocument::class,
        'ProjectTasks' => ProjectTask::class,
    ];

    private static $many_many = [
        'Documents' => File::class,
    ];

    private static $owns = [
        'Documents',
    ];

    private static $cascade_deletes = [
        'ProjectDocuments',
        'ProjectTasks',
    ];

    private static $many_many_extraFields = [
        'Documents' => [
            'Category' => 'Int',
        ],
    ];

    private static $summary_fields = [
        'Title',
        'ClientObject.Name',
    ];

    // german field labels
    private static $field_labels = [
        'Title' => 'Titel',
        'Description' => 'Beschreibung',
        'Date' => 'Datum',
        'Client.Company' => 'Kunde',
        'ClientObject.Name' => 'Kundenobjekt',
    ];

    public function getStatusLabel()
    {
        switch ($this->Status) {
            case 0:
                return 'Neu';
            case 1:
                return 'Laufend';
            case 2:
                return 'Abgeschlossen';
            case 3:
                return 'Abgerechnet';
            case 4:
                return 'Storniert';
            default:
                return 'Unbekannt';
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Status',
            'ClientObjectID'
        ]);

        if ($this->ID) {
            $fields->addFieldToTab('Root.Main', DropdownField::create(
                'Status',
                'Status',
                [
                    0 => 'Neu',
                    1 => 'Laufend',
                    2 => 'Abgeschlossen',
                    3 => 'Abgerechnet',
                    4 => 'Storniert',
                ]
            ), 'Title');
        }

        if ($this->ClientID) {
            $fields->addFieldToTab('Root.Main', DropdownField::create(
                'ClientObjectID',
                'Kundenobjekt',
                $this->Client()->ClientObjects()->map('ID', 'Name')->toArray()
            ), );
        }

        
        return $fields;
    }

    public function getUploadFolders() {
        return [
            'doc' => 'projects/' . $this->ID . '/documents',
            'task' => 'projects/' . $this->ID . '/tasks',
        ];
    }

    /**
     * Get all members assigned to this project
     */
    public function getAssignedMembers()
    {
        /*return Member::get()->innerJoin('app_ProjectMember', 'app_ProjectMember.MemberID = Member.ID')
            ->where(['app_ProjectMember.ProjectID' => $this->ID]);*/
    }

    /**
     * Get members assigned to project within a specific date range
     */
    public function getMembersForDateRange($startDate, $endDate)
    {
        /*return $this->ProjectMembers()->filter([
            'StartDate:LessThanOrEqual' => $endDate,
            'EndDate:GreaterThanOrEqual' => $startDate
        ]);*/
    }

    /**
     * Check if a member is assigned to this project during a specific period
     */
    public function isMemberAssigned($memberID, $startDate = null, $endDate = null)
    {
        $filter = ['MemberID' => $memberID];
        
        if ($startDate && $endDate) {
            $filter['StartDate:LessThanOrEqual'] = $endDate;
            $filter['EndDate:GreaterThanOrEqual'] = $startDate;
        }
        
        //return $this->ProjectMembers()->filter($filter)->count() > 0;
    }

    /**
     * Check if project can be closed // if all tasks are completed
     * @return bool
     */
    public function canBeClosed(): bool {
        $filter = ['Status:not' => 2]; // not completed
        return $this->ProjectTasks()->filter($filter)->count() > 0;
    }

    /**
     * check if projectTasks & ProjectDocuments cann be added
     * @return bool
     */
    public function canAddTasksAndDocuments(): bool {
        return !in_array($this->Status, [2,3,4]); // not Completed, Billed, Canceled
    }
}