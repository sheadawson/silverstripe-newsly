<% with Parent.Controller %>
	<% include NewsSideBar %>
<% end_with %>
<div class="content-container unit size3of4 lastUnit">
	<article>
		<h1>$Title</h1>
		<p class="article_meta">
			Written<% if Author %> by $Author<% end_if %> on $PublishDate.Nice
		</p>
		$Image
		<div class="content">$Content</div>
		<% if Attachment %><p class="article_attachment">Download PDF Version</p><% end_if %>
		<% if Tags %><p class="article_tags"><% include LinkedTags %></p><% end_if %>
	</article>
</div>
