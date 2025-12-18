<% if $actOffer.getGermanStatusOptionsDataList %>
    <div class="row justify-content-end g-2 align-items-center">
        <div class="col-auto text-end">
            <b>Status ändern: </b>
        </div>
        <% loop $actOffer.getGermanStatusOptionsDataList %>
            <div class="col-auto">
                <a href="{$Top.Link}/changeInvoiceStatus/?e=$Up.actOffer.ID&status=$Key" class="yousure btn btn-sm btn-sm <% if isActive %> btn-success<% else %> btn-outline-secondary<% end_if %>">$Title</a>
            </div>
        <% end_loop %>
    </div>
<% end_if %>