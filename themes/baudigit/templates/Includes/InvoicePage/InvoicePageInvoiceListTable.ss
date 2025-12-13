<div >
    <table id="invoicePageInvoiceListTable" class="dataTableMe table table-striped table-bordered" style="width:100%">
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
                    <td data-order="$Created.Format('yyyyMMdd')">$Created.Format('dd.MM.yyyy')</td>
                    <td data-order="$DueDate.Format('yyyyMMdd')">$DueDate.Format('dd.MM.yyyy')</td>
                    <td class="text-end">$getTotalBrutto</td>
                    <td>$getGermanStatus</td>
                    <td class="text-center"><a href="{$Top.Link}/?e=$ID" class=""><i class="bx bx-edit-alt"></i></a></td>
                </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>