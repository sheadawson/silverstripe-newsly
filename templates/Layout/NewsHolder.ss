<% include NewsSideBar %>

<div class="content-container unit size3of4 lastUnit">
	<article>
		<h1>$Title</h1>
		<div class="content">$Content</div>
	</article>

	<% if Articles %>
		<% loop Articles %>
			<article>
				<h2><a href="$Link">$Title</a></h2>

				<p class="article_meta">Written<% if AuthorName %> by $AuthorName<% end_if %> on $PublishDate.Nice</p>

				<% if Image %>
					<a href="$Link">$Image</a>
				<% end_if %>

				<div class="article_content typography">
					<% if Summary %>
						$Summary
					<% else %>
						$Content.Summary
					<% end_if %>
				</div>

				<% if Attachment %>
					<p class="article_attachment">Download PDF Version</p>
				<% end_if %>

				<% if Tags %>
					<p class="article_tags"><% include LinkedTags %></p>
				<% end_if %>

				<p><strong><a href="$Link">Read more</a></strong></p>
			</article>
		<% end_loop %>
	<% else %>
		<p>Sorry, there are currently no articles in this section</p>
	<% end_if %>

</div>




