<% if $checkEdit %>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <!-- Future: Add project actions like export, archive, etc. -->
                </div>
                <div class="col-12 col-lg-6 text-end">
                    <a href="{$Top.Link}" class="btn btn-primary px-4">Zurück zur Übersicht</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="form-body">
                <div class="accordion" id="projectFormAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="projectFormHeading">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#projectFormCollapse" aria-expanded="true" aria-controls="projectFormCollapse">
                                Projektdaten ($actProject.getStatusLabel)
                            </button>
                        </h2>
                        <div id="projectFormCollapse" class="accordion-collapse collapse show" aria-labelledby="projectFormHeading" data-bs-parent="#projectFormAccordion">
                            <div class="accordion-body">
                                $edit
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<% else %>
    <% if $checkAdd %>
        <div class="card">
            <div class="card-header text-end">
                <a href="{$Top.Link}" class="btn btn-primary px-4">Zurück zur Übersicht</a>
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
                <a type="button" class="btn btn-primary px-4" href="{$Top.Link}/?a=1">Projekt erstellen</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered" style="width:100%">
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
            </div>
        </div>
    <% end_if %>
<% end_if %>