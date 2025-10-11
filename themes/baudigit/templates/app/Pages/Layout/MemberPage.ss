<% if $checkEdit %>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <!-- Future: Add member actions like reset password, disable account, etc. -->
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
                <div class="accordion" id="memberFormAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="memberFormHeading">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#memberFormCollapse" aria-expanded="true" aria-controls="memberFormCollapse">
                                Benutzerdaten - $actMember.FirstName $actMember.Surname
                            </button>
                        </h2>
                        <div id="memberFormCollapse" class="accordion-collapse collapse show" aria-labelledby="memberFormHeading" data-bs-parent="#memberFormAccordion">
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
                <a type="button" class="btn btn-primary px-4" href="{$Top.Link}/?a=1">Benutzer erstellen</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>E-Mail</th>
                                <th>Benutzergruppe</th>
                                <th>Erstellt am</th>
                                <th>Letzter Login</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <% loop readAllInputs %>
                                <tr>
                                    <td>$FirstName $Surname</td>
                                    <td>$Email</td>
                                    <td>
                                        <% if Groups.filter('ID', 2).count > 0 %>
                                            <span class="badge bg-danger">Administrator</span>
                                        <% else_if Groups.filter('ID', 3).count > 0 %>
                                            <span class="badge bg-primary">Arbeiter</span>
                                        <% else %>
                                            <span class="badge bg-secondary">Keine Gruppe</span>
                                        <% end_if %>
                                    </td>
                                    <td>$Created.Format('dd.MM.yyyy')</td>
                                    <td>
                                        <% if LastVisited %>
                                            $LastVisited.Format('dd.MM.yyyy HH:mm')
                                        <% else %>
                                            <span class="text-muted">Nie</span>
                                        <% end_if %>
                                    </td>
                                    <td class="text-center">
                                        <a href="{$Top.Link}/?e=$ID" class="" title="Bearbeiten">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
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