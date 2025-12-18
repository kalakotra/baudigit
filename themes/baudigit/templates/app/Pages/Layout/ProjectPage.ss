<% if $checkEdit %>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-12 col-lg-2">
                    <!--  if status is Completed add button to copy to invoice-->
                    <% if actProject.Status==2 %>
                        <a href="{$Top.getMyClass(app\Pages\InvoicePage).First().Link}/?a=1&p=$actProject.ID" class="btn btn-sm btn-success px-4">In Rechnung umwandeln</a>
                    <% end_if %>
                </div>
                <div class="col-12 col-lg-8" >
                    <% include Includes/ProjectPage/ProjectStatusSwitch %>
                </div>
                <div class="col-12 col-lg-2 text-end">
                    <a href="{$Top.Link}" class="btn btn-sm btn-primary px-4">Zurück zur Übersicht</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="form-body">
            <div class="card-body">
                <ul class="nav nav-tabs nav-primary" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <% if not $checkActiveTab %>active<% end_if %>" data-bs-toggle="tab" href="#project_home" role="tab" aria-selected="<% if $checkActiveTab %>false<% else %>true<% end_if %>">
                            <div class="d-flex align-items-center">
                                <div class="tab-icon">
                                    <i class="bi bi-house-door me-1 fs-6"></i>
                                </div>
                                <div class="tab-title">Projektdaten ($actProject.getStatusLabel)</div>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <% if $checkActiveTab='project_tasks' %>active<% end_if %>" data-bs-toggle="tab" href="#project_tasks" role="tab" aria-selected="<% if $checkActiveTab='project_tasks' %>true<% else %>false<% end_if %>">
                            <div class="d-flex align-items-center">
                                <div class="tab-icon"><i class="bi bi-list-task me-1 fs-6"></i>
                                </div>
                                <div class="tab-title">Aaufgaben</div>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <% if $checkActiveTab='project_documents' %>active<% end_if %>" data-bs-toggle="tab" href="#project_documents" role="tab" aria-selected="<% if $checkActiveTab='project_documents' %>true<% else %>false<% end_if %>">
                            <div class="d-flex align-items-center">
                                <div class="tab-icon"><i class='bi bi-file-earmark-text me-1 fs-6'></i>
                                </div>
                                <div class="tab-title">Dokumente</div>
                            </div>
                        </a>
                    </li>
                </ul>
                <div class="tab-content py-3">
                    <div class="tab-pane fade <% if not $checkActiveTab %>show active<% end_if %>" id="project_home" role="tabpanel">
                        <%-- Project edit form --%>
                        $edit
                    </div>
                    <div class="tab-pane fade <% if $checkActiveTab='project_tasks' %>show active<% end_if %>" id="project_tasks" role="tabpanel">
                        <div class="row">
                            <div class="col-12 col-lg-8 mb-2">
                                <% include ProjectTasksTableList %>                        
                            </div>
                            <div class="col-12 col-lg-4 mb-2">
                                <% include ProjectTasksForms %>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade <% if $checkActiveTab='project_documents' %>show active<% end_if %>" id="project_documents" role="tabpanel">
                        <div class="px-3">
                            <div class="row">
                                <div class="col-12 col-lg-8 mb-2">
                                    <% include ProjectDocumentsTableList %>                        
                                </div>
                                <div class="col-12 col-lg-4 mb-2">
                                    <% include ProjectDocumentsForms %>
                                </div>
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
                <a type="button" class="btn btn-sm btn-success px-4" href="{$Top.Link}/?a=1">Projekt erstellen</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <% include Includes/ProjectPage/ProjectListTable %>
            </div>
        </div>
    <% end_if %>
<% end_if %>