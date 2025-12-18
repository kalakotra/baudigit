<div >
    <table id="invoicePageInvoiceListTable" class="dataTableMe table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>R.Nr.</th>
                <th>Kunde</th>
                <th>Datum</th>
                <th>Fäll.datum</th>
                <th>Brutto</th>
                <th>Status</th>
                <th data-orderable="false"></th>
            </tr>
        </thead>
        <tbody>
            <% loop readAllInputs %>
                <tr>
                    <td>$InvoiceNumber</td>
                    <td>
                        $ClientObject.getFullName
                        <br />
                        <small>$ClientObject.Client.Company</small>
                    </td>
                    <td data-order="$Created.Format('yyyyMMdd')">$Created.Format('dd.MM.yyyy')</td>
                    <td data-order="$DueDate.Format('yyyyMMdd')">$DueDate.Format('dd.MM.yyyy')</td>
                    <td class="text-end">$getTotalBruttoAsString</td>
                    <td class="text-end">$getGermanStatus</td>
                    <td class="text-end">
                        <% if not checkEditingStatus %>
                            <a href="{$Top.Link}/previewPDF/?e=$ID" target="_blank"><i class="bi bi-file-text bi-filetype-pdf fs-5 me-1"></i></a>
                        <% end_if %>
                        <a href="{$Top.Link}/?e=$ID" class=""><i class="bx bx-edit-alt fs-5"></i></a>
                    </td>
                </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>