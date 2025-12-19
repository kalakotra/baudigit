<% if $checkEdit %>
    <div class="card">
        <div class="card-header text-end">
            <% if checkLocationList %>
                <a href="{$Top.Link}?e=$checkEdit&al=1" class="btn btn-sm btn-success px-4">Standort hinzufügen</a>
            <% else %>
                <a href="{$Top.Link}?e=$checkEdit&el=0" class="btn btn-sm btn-primary px-4">Standorte</a>
            <% end_if %>
            <a href="{$Top.Link}" class="btn btn-sm btn-primary px-4">Zurück zur Kundenübersicht</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="form-body">
                <% if checkLocationList %>
                    <table id="locationListTable" class="dataTableMe table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Adresse</th>
                                <th>Ansprechpartner</th>
                                <th>Beschreibung</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <% loop readAllLocationInputs %>
                                <tr>
                                    <td>$Name</td>
                                    <td>$Address <br />$PostalCode $City</td>
                                    <td>$ContactPerson <br />$ContactTelephone <br />$ContactEmail</td>
                                    <td>$Description</td>
                                    <td class="text-center">
                                        <a href="{$Top.Link}/?e=$ClientID&el=$ID" class=""><i class="bx bx-edit-alt fs-4"></i></a>
                                    </td>
                                </tr>
                            <% end_loop %>
                        </tbody>
                    </table>
                <% else %>
                    <% if checkAddLocation %>
                        $addLocation
                    <% else %>
                        <% if checkEditLocation %>
                            $editLocation
                        <% else %>
                            $edit
                        <% end_if %>
                    <% end_if %>
                <% end_if %>
            </div>
        </div>
    </div>
<% else %>
    <% if $checkAdd %>
        <div class="card">
            <div class="card-header text-end">
                <a href="{$Top.Link}" class="btn btn-sm btn-primary px-4">Zurück zur Übersicht</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="form-body">
                    $add
                </div>
            </div>
        </div>
    <% else %>
        <div class="card">
            <div class="card-header text-end">
                <a type="button" class="btn btn-sm btn-success px-4" href="{$Top.Link}/?a=1">Kunde erstellen</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="locationListTable" class="dataTableMe table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Firma</th>
                                <th>Kontakt Person</th>
                                <th>Email</th>
                                <th>Telefon</th>
                                <th>Adresse</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <% loop readAllInputs %>
                                <tr>
                                    <td>$Company</td>
                                    <td>$Name</td>
                                    <td>$Email</td>
                                    <td>$Phone</td>
                                    <td>$Address <br />$PostalCode $City</td>
                                    <td class="text-center">
                                        <a href="{$Top.Link}/?e=$ID" class=""><i class="bx bx-edit-alt fs-4"></i></a>
                                        <a href="{$Top.Link}/?e=$ID&el=0" class=""><i class="bx bx-buildings fs-4"></i></a>
                                    </td>
                                </tr>
                            <% end_loop %>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <% end_if %>
<% end_if %>
