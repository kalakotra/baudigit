<% if checkEditArticle %>
    $editArticleForm
<% else %>
    $addArticleForm
<% end_if %>

<%-- datalist of articles    --%>
<datalist id="articleList">
    <option data-id="0" data-unit="0" data-nettoprice="0" value=""></option>
    <% loop getAllArticles %>
        <option data-id="$ID" data-unit="$Unit" data-nettoprice="$SellPriceNetto" data-title="$Title" value="$Title || $InternalID || $formatPrice($SellPriceNetto)"></option>
    <% end_loop %>
</datalist>