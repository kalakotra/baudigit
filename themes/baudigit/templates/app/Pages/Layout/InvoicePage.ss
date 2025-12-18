<% if $checkEdit %>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-12 col-lg-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="dropdown">Options</button>
                        <button type="button" class="btn btn-sm btn-success split-bg-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">	<span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">
                            <a href="{$Top.Link}/previewPDF?e={$checkEdit}" class="dropdown-item" target="_blank"><i class="bx bx-search " ></i>Vorschau</a>
                            <a href="{$Top.Link}/send?e={$checkEdit}" class="dropdown-item yousure" ><i class="bx bx-envelope " ></i>Versenden</a>
                            <% if $actInvoice.canBeCancelled %>
                                <a href="{$Top.Link}/cancellation?e={$checkEdit}" class="dropdown-item yousure text-danger" ><i class="fadeIn animated bx bx-shield-x" ></i>Storno</a>
                            <% end_if %>
                        </div>
                    </div>
                    
                </div>
                <div class="col-12 col-lg-8" >
                    <% include Includes/InvoicePage/InvoiceStatusSwitch %>
                </div>
                <div class="col-12 col-lg-2 text-end">
                    <a href="{$Top.Link}" class="btn btn-sm btn-primary px-4">Zurück zur Übersicht</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs nav-primary" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link <% if not $checkActiveTab %>active<% end_if %>" data-bs-toggle="tab" href="#invoice_home" role="tab" aria-selected="<% if not $checkActiveTab %>true<% else %>false<% end_if %>">
                        <div class="d-flex align-items-center">
                            <div class="tab-icon">
                                <i class="bi bi-house-door me-1 fs-6"></i>
                            </div>
                            <div class="tab-title">Rechnungsdaten ($actInvoice.getGermanStatus)</div>
                        </div>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <% if $checkActiveTab == 'invoice_article' %>active<% end_if %>" data-bs-toggle="tab" href="#invoice_article" role="tab" aria-selected="<% if $checkActiveTab == 'invoice_article' %>true<% else %>false<% end_if %>">
                        <div class="d-flex align-items-center">
                            <div class="tab-icon"><i class="bi bi-list-task me-1 fs-6"></i>
                            </div>
                            <div class="tab-title">Artikel</div>
                        </div>
                    </a>
                </li>
                <% if $actInvoice.Project %>
                    <li class="nav-item">
                        <a class="nav-link <% if $checkActiveTab == 'invoice_project' %>active<% end_if %>" data-bs-toggle="tab" href="#invoice_project" role="tab" aria-selected="<% if $checkActiveTab == 'invoice_project' %>true<% else %>false<% end_if %>">
                            <div class="d-flex align-items-center">
                                <div class="tab-icon"><i class="bi bi-list-task me-1 fs-6"></i>
                                </div>
                                <div class="tab-title">Projekt Daten</div>
                            </div>
                        </a>
                    </li>
                <% end_if %>
            </ul>
            <div class="tab-content py-3">
                <div class="tab-pane fade <% if not $checkActiveTab %>show active<% end_if %>" id="invoice_home" role="tabpanel">
                    <%-- Project edit form --%>
                    $edit
                </div>
                <div class="tab-pane fade <% if $checkActiveTab == 'invoice_article' %>show active<% end_if %>" id="invoice_article" role="tabpanel">
                    <%-- Invoice Articles if status is draft --%>
                    <% if $actInvoice.checkEditingStatus %>
                        <% include Includes/InvoicePage/InvoicePageArticleForms %>
                        <hr />
                    <% end_if %>
                    
                    <% include Includes/InvoicePage/InvoicePageArticleTable %>
                </div>

                <% if $actInvoice.Project %>
                    <div class="tab-pane fade <% if $checkActiveTab == 'invoice_project' %>show active<% end_if %>" id="invoice_project" role="tabpanel">
                        <% include Includes/InvoicePage/InvoicePageProjectData Project=$actInvoice.Project %>
                    </div>
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
                <a type="button" class="btn btn-sm btn-success px-4" href="{$Top.Link}/?a=1">Rechnung erstellen</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <% include Includes/InvoicePage/InvoicePageInvoiceListTable %>
            </div>
        </div>
    <% end_if %>
<% end_if %>