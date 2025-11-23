<div >
    <table id="projectListTable" class="dataTableMe table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>Titel</th>
                <th>Kunde</th>
                <th>Kundenobjekt</th>
                <th>Status</th>
                <th>Erstellt am</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <% loop readAllInputs %>
                <tr>
                    <td>$Title</td>
                    <td>$ClientObject.Client.Company</td>
                    <td>$ClientObject.getFullName</td>
                    <td>
                        <% if Status == 0 %>
                            <span class="badge bg-secondary">$getStatusLabel</span>
                        <% else_if Status == 1 %>
                            <span class="badge bg-primary">$getStatusLabel</span>
                        <% else_if Status == 2 %>
                            <span class="badge bg-success">$getStatusLabel</span>
                        <% else_if Status == 3 %>
                            <span class="badge bg-info">$getStatusLabel</span>
                        <% else_if Status == 4 %>
                            <span class="badge bg-danger">$getStatusLabel</span>
                        <% else %>
                            <span class="badge bg-warning">$getStatusLabel</span>
                        <% end_if %>
                    </td>
                    <td>$Created.Format('dd.MM.yyyy')</td>
                    <td class="text-center"><a href="{$Top.Link}/?e=$ID" class=""><i class="bx bx-edit-alt"></i></a></td>
                </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>