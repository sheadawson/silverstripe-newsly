<% loop LinkedTags %>
	<a href="$Link" class="tag">$Title</a><% if not Last %>, <% end_if %>
<% end_loop %>