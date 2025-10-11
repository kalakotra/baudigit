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
                            <button class="accordion-button <% if checkActiveAccordion != 'details' %>collapsed<% end_if %>" type="button" data-bs-toggle="collapse" data-bs-target="#projectFormCollapse" aria-expanded="<% if checkActiveAccordion == 'details' %>true<% else %>false<% end_if %>" aria-controls="projectFormCollapse">
                                Projektdaten ($actProject.getStatusLabel)
                            </button>
                        </h2>
                        <div id="projectFormCollapse" class="accordion-collapse collapse <% if checkActiveAccordion == 'details' %>show<% end_if %>" aria-labelledby="projectFormHeading" data-bs-parent="#projectFormAccordion">
                            <div class="accordion-body">
                                $edit
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="projectMemberFormHeading">
                            <button class="accordion-button <% if checkActiveAccordion != 'members' %>collapsed<% end_if %>" type="button" data-bs-toggle="collapse" data-bs-target="#projectMemberFormCollapse" aria-expanded="<% if checkActiveAccordion == 'members' %>true<% else %>false<% end_if %>" aria-controls="projectMemberFormCollapse">
                                Mitarbeiter zum Projekt hinzufügen / bearbeiten
                            </button>
                        </h2>
                        <div id="projectMemberFormCollapse" class="accordion-collapse collapse <% if checkActiveAccordion == 'members' %>show<% end_if %>" aria-labelledby="projectMemberFormHeading" data-bs-parent="#projectFormAccordion">
                            <div class="accordion-body">
                                <% if checkEditMember %>
                                    $editMemberForm
                                <% else %>
                                    $addMemberForm
                                <% end_if %>
                                <hr />
                                <table id="projectMemberTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Mitarbeiter</th>
                                            <th>Startdatum</th>
                                            <th>Enddatum</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <% loop getAllProjectMembers %>
                                            <tr>
                                                <td>$Member.FirstName $Member.Surname</td>
                                                <td>$StartDate.Format('dd.MM.yyyy')</td>
                                                <td>$EndDate.Format('dd.MM.yyyy')</td>
                                                <td class="text-center">
                                                    <a href="{$Top.Link}/?e=$ProjectID&em=$ID" class="" title="Bearbeiten">
                                                        <i class="bx bx-edit-alt fs-4"></i>
                                                    </a>
                                                    <a href="{$Top.Link}/deleteMember?e=$ProjectID&dm=$ID" class="yousure" title="Löschen">
                                                        <i class="bx bx-x-circle text-danger fs-4"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <% end_loop %>
                                    </tbody>
                                </table>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                        <div id='calendarMembers' <% if actProject.StartDate %>data-start="$actProject.StartDate.Format('yyyy-MM-dd')" <% end_if %> <% if actProject.EndDate %>data-end="$actProject.EndDate.Format('yyyy-MM-dd')" <% end_if %>>
                                                <% loop getAllProjectMembers %>
                                                    <marker data-name="$Member.FirstName $Member.Surname" data-color="$Member.MemberColor" data-start="$StartDate.Format('yyyy-MM-dd')" data-end="$EndDate.Format('yyyy-MM-dd')"></marker>
                                                <% end_loop %>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="projectDocumentFormHeading">
                        <button class="accordion-button <% if checkActiveAccordion != 'documents' %>collapsed<% end_if %>" type="button" data-bs-toggle="collapse" data-bs-target="#projectDocumentFormCollapse" aria-expanded="<% if checkActiveAccordion == 'documents' %>true<% else %>false<% end_if %>" aria-controls="projectDocumentFormCollapse">
                                Projekt-Dokumentationen verwalten
                            </button>
                        </h2>
                        <div id="projectDocumentFormCollapse" class="accordion-collapse collapse <% if checkActiveAccordion == 'documents' %>show<% end_if %>" aria-labelledby="projectDocumentFormHeading" data-bs-parent="#projectFormAccordion">
                            <div class="accordion-body">
                                <% if checkEditDocument %>
                                    $editDocumentForm
                                <% else %>
                                    $addDocumentForm
                                <% end_if %>
                                <hr />
                                <table id="projectDocumentTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Titel</th>
                                            <th>Sichtbar für Arbeiter</th>
                                            <th>Dokumente</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <% loop getAllProjectDocuments %>
                                            <tr>
                                                <td>$Title</td>
                                                <td>
                                                    <% if VisibleToWorker %>
                                                        <span class="badge bg-success">Ja</span>
                                                    <% else %>
                                                        <span class="badge bg-secondary">Nein</span>
                                                    <% end_if %>
                                                </td>
                                                <td>
                                                    <% loop Documents %>
                                                        <div class="document-item">
                                                            <a href="$Link" target="_blank">$Title</a>
                                                        </div>
                                                    <% end_loop %>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{$Top.Link}/?e=$ProjectID&ed=$ID" class="" title="Bearbeiten">
                                                        <i class="bx bx-edit-alt fs-4"></i>
                                                    </a>
                                                    <a href="{$Top.Link}/deleteDocument?e=$ProjectID&dd=$ID" class="yousure" title="Löschen">
                                                        <i class="bx bx-x-circle text-danger fs-4"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <% end_loop %>
                                    </tbody>
                                </table>
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