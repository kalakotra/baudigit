<div class="table-responsive-">
    <table id="offerListTable" class="dataTableMe table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>Datum</th>
                <th>Nr.</th>
                <th>Kunde</th>
                <th>Gesamt (brutto)</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <% loop readAllInputs %>
                <tr>
                    <td data-order="$Created.Format('yyyyMMdd')">
                        $Created.Format('dd.MM.yyyy')
                    </td>
                    <td>$OfferNumber</td>
                    <td>$ClientObject.getFullName</td>
                    <td>$getTotalBrutto</td>
                    <td>$getGermanStatus</td>
                    <td class="text-center"><a href="{$Top.Link}/?e=$ID" class=""><i class="bx bx-edit-alt"></i></a></td>
                </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>