<aside class="sidebar unit size1of4">
	<% if $LinkedTagList %>
		<nav class="secondary">
			<h3>Tags</h3>
			<ul>
				<% loop $LinkedTagList %>
					<li><a href="$Link">$Title</a></li>
				<% end_loop %>
			</ul>
		</nav>
	<% end_if %>

	<% if $Archive %>
		<nav class="secondary">
			<h3>Archive</h3>
			<ul>
				<% loop $Archive %>
					<li><a href="$Link">$YearName</a>
						<% if Months %>
							<ul style="display: block">
								<% loop Months %>
									<li><a href="$Link">$MonthName</a></li>
								<% end_loop %>
							</ul>
						<% end_if %>
					</li>
				<% end_loop %>
			</ul>
		</nav>
	<% end_if %>
</aside>
