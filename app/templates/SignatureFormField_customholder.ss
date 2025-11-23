<div id="$HolderID" class="field<% if $extraClass %> $extraClass<% end_if %>">
	<% if $Title %><label class="left form-label" for="$ID">$Title</label><% end_if %>
	<div class="middleColumn d-none">
		$Field
	</div>
    <div id="{$HolderID}_signaturePart" class="signaturePart"></div>
	<% if $RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
	<% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
	<% if $Description %><span class="description">$Description</span><% end_if %>
    <div>
        <button type="button" id="{$HolderID}_clearSignature" class="btn btn-warning clearSignature">Unterschrift löschen</button>
    </div>
</div>
