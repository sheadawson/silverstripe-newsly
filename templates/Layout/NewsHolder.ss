<h1><a href="$Link">$Title</a></h1>

<% if Articles %>
	<% loop Articles %>
		<p class="article_meta">Written<% if Author %> by $Author<% end_if %> on $PublishDate.Nice</p>
		<a href="$Link">$Image</a>
		<div class="article_content typography">$Content</div>
		<% if Attachment %><p class="article_attachment">Download PDF Version</p><% end_if %> 
		<% if Tags %><p class="article_tags"><% include LinkedTags %></p><% end_if %>
	<% end_loop %>
<% else %>
	<p>Sorry, there are currently no articles in this section</p>
<% end_if %>