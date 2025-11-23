<?php

namespace app\Controllers;

use app\Models\ProjectTaskHours;

class WorkerTasksHoursPageController extends \PageController
{
    public function getActuallMonthWorkerTasksHours()
    {
        $currentUser = $this->currentUser();
        if ($currentUser) {
            $startOfMonth = date('Y-m-01');
            $endOfMonth = date('Y-m-t');

            $myHours = ProjectTaskHours::get()
                ->filter([
                    'LoggedByID' => $currentUser->ID,
                    'Created:GreaterThanOrEqual' => $startOfMonth,
                    'Created:LessThanOrEqual' => $endOfMonth,
                ]);
            return $myHours;
        }
        return null;
    }

    public function getActuallMonthWorkerTasksHoursSum()
    {
        $currentUser = $this->currentUser();
        if ($currentUser) {
            $startOfMonth = date('Y-m-01');
            $endOfMonth = date('Y-m-t');

            $myHoursSum = ProjectTaskHours::get()
                ->filter([
                    'LoggedByID' => $currentUser->ID,
                    'Created:GreaterThanOrEqual' => $startOfMonth,
                    'Created:LessThanOrEqual' => $endOfMonth,
                ])->sum('Hours');
            return $myHoursSum;
        }
        return 0;
    }
}