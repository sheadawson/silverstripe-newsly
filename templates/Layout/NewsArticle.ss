<h1>$Title</h1>
<p class="article_meta">
	Written<% if Author %> by $Author<% end_if %> on $PublishDate.Nice
</p>
$Image
<div class="article_content typography">$Content</div>
<% if Attachment %><p class="article_attachment">Download PDF Version</p><% end_if %> 
<% if Tags %><p class="article_tags"><% include LinkedTags %></p><% end_if %>