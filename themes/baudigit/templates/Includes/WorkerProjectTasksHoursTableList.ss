<table id="workerProjectTasksHoursTable" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>Stunden / Beschreibung / Aufgabe</th>
        </tr>
    </thead>
    <tbody>
        <% loop getActuallMonthWorkerTasksHours %>
            <tr <% if NeedsConfirmation %>class="table-warning-"<% end_if %>>
                <td>
                    <strong>Stunden: $Hours</strong>
                    <% if Description %>
                        <br />
                        <small>$Description</small>
                    <% end_if %>
                    <br />
                    <hr />
                    $ProjectTask.Title
                    <br />
                    <small>
                        $ProjectTask.Project.ClientObject.Name $ProjectTask.Project.ClientObject.Address {$ProjectTask.Project.ClientObject.PostalCode}, $ProjectTask.Project.ClientObject.City
                    </small>
                </td>
            </tr>
        <% end_loop %>
    </tbody>
    <tfoot>
        <tr>
            <th><strong>Gesamtstunden: $getActuallMonthWorkerTasksHoursSum</strong></th>
        </tr>
    </tfoot>
</table>