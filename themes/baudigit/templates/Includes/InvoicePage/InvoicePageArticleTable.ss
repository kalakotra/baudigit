<table id="invoiceArticleTable" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>Titel</th>
            <th>Menge</th>
            <th class="text-end">Preis (netto)</th>
            <th class="text-end">Rabatt</th>
            <th class="text-end">MwSt.</th>
            <th class="text-end">Gesamtpreis</th>
            <% if $actInvoice.checkEditingStatus %>
                <th data-orderable="false"></th>
            <% end_if %>
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
                <% if $Invoice.checkEditingStatus %>
                    <td class="text-center"><a href="{$Top.Link}/?e=$InvoiceID&ea=$ID&activetab=invoice_article" class=""><i class="bx bx-edit-alt fs-5"></i></a><a href="{$Top.Link}/deleteArticle?e=$InvoiceID&da=$ID&activetab=invoice_article" class="yousure"><i class="bx bx-x-circle text-danger fs-5"></i></a></td>
                <% end_if %>
            </tr>
        <% end_loop %>
    </tbody>
    <% with actInvoice %>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">Gesamt (netto):</th>
                <th class="text-end">$Top.formatPrice($getTotalNetto)</th>
                <% if checkEditingStatus %>
                    <th></th>
                <% end_if %>
            </tr>
            <tr>
                <th colspan="5" class="text-end">Gesamt MwSt.:</th>
                <th class="text-end">$Top.formatPrice($getTotalTax)</th>
                <% if checkEditingStatus %>
                    <th></th>
                <% end_if %>
            </tr>
            <tr>
                <th colspan="5" class="text-end">Gesamt (brutto):</th>
                <th class="text-end">$Top.formatPrice($getTotalBrutto)</th>
                <% if checkEditingStatus %>
                    <th></th>
                <% end_if %>
            </tr>
        </tfoot>
    <% end_with %>
</table>