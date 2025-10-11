<% if $checkEdit %>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <a href="{$Top.Link}/previewPDF?e={$checkEdit}" class="btn btn-secondary px-4" target="_blank"><i class="bx bx-search " ></i>Vorschau</a>
                    <a href="{$Top.Link}/send?e={$checkEdit}" class="btn btn-secondary px-4 yousure" ><i class="bx bx-envelope " ></i>Versenden</a>
                </div>
                <div class="col-12 col-lg-6 text-end">
                    <a href="{$Top.Link}" class="btn btn-primary px-4">Zurück zur Übersicht</a>
                </div>
            </div>
        </div>
    </div>
    <% if getSentStatus=1 %>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Die Rechnung wurde erfolgreich versendet.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <% end_if %>
    <div class="card">
        <div class="card-body">
            <div class="form-body">
                <div class="accordion" id="invoiceArticleFormAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="invoiceFormHeading">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#invoiceFormCollapse" aria-expanded="false" aria-controls="invoiceFormCollapse">
                                Rechnungsdaten ($actInvoice.getGermanStatus)
                            </button>
                        </h2>
                        <div id="invoiceFormCollapse" class="accordion-collapse collapse" aria-labelledby="invoiceFormHeading" data-bs-parent="#invoiceArticleFormAccordion">
                            <div class="accordion-body">
                                $edit
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="invoiceArticleFormHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#invoiceArticleFormCollapse" aria-expanded="true" aria-controls="invoiceArticleFormCollapse">
                                Artikel zur Rechnung hinzufügen / bearbeiten
                            </button>
                        </h2>
                        <div id="invoiceArticleFormCollapse" class="accordion-collapse collapse show" aria-labelledby="invoiceArticleFormHeading" data-bs-parent="#invoiceArticleFormAccordion">
                            <div class="accordion-body">
                                <% if checkEditArticle %>
                                    $editArticleForm
                                <% else %>
                                    $addArticleForm
                                <% end_if %>
                                <hr />
                                <table id="invoiceArticleTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Titel</th>
                                            <th>Menge</th>
                                            <th class="text-end">Preis (netto)</th>
                                            <th class="text-end">Rabatt</th>
                                            <th class="text-end">MwSt.</th>
                                            <th class="text-end">Gesamtpreis</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <% loop getAllInvoiceArticles %>
                                            <tr>
                                                <td>$Title</td>
                                                <td>$Quantity $getUnitLabel</td>
                                                <td class="text-end">$formatPrice($SellPriceNetto)</td>
                                                <td class="text-end"> ({$Discount}%) $formatPrice($getDiscountAmount)</td>
                                                <td class="text-end"> ({$TaxRate}%) $formatPrice($getTaxAmount)</td>
                                                <td class="text-end">$formatPrice($FinalPrice)</td>
                                                <td class="text-center"><a href="{$Top.Link}/?e=$InvoiceID&ea=$ID" class=""><i class="bx bx-edit-alt fs-4"></i></a><a href="{$Top.Link}/deleteArticle?e=$InvoiceID&da=$ID" class="yousure"><i class="bx bx-x-circle text-danger fs-4"></i></a></td>
                                            </tr>
                                        <% end_loop %>
                                    </tbody>
                                    <% with actInvoice %>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-end">Gesamt (netto):</th>
                                            <th class="text-end">$Top.formatPrice($getTotalNetto)</th>
                                            <th></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-end">Gesamt MwSt.:</th>
                                            <th class="text-end">$Top.formatPrice($getTotalTax)</th>
                                            <th></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-end">Gesamt (brutto):</th>
                                            <th class="text-end">$Top.formatPrice($getTotalBrutto)</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                    <% end_with %>
                                </table>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <%-- datalist of articles    --%>
    <datalist id="articleList">
        <option data-id="0" data-unit="0" data-nettoprice="0" value=""></option>
        <% loop getAllArticles %>
            <option data-id="$ID" data-unit="$Unit" data-nettoprice="$SellPriceNetto" data-title="$Title" value="$Title || $InternalID || $formatPrice($SellPriceNetto)"></option>
        <% end_loop %>
    </datalist>
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
                <a type="button" class="btn btn-primary px-4" href="{$Top.Link}/?a=1">Rechnung erstellen</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Rechnungsnummer</th>
                                <th>Kunde</th>
                                <th>Datum</th>
                                <th>Fälligkeitsdatum</th>
                                <th>Gesamt (brutto)</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <% loop readAllInputs %>
                                <tr>
                                    <td>$InvoiceNumber</td>
                                    <td>$ClientObject.getFullName</td>
                                    <td>$Created.Format('dd.MM.yyyy')</td>
                                    <td>$DueDate.Format('dd.MM.yyyy')</td>
                                    <td>$getTotalBrutto</td>
                                    <td>$getGermanStatus</td>
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