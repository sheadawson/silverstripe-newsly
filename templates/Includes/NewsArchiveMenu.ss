<% if Years %>
	<nav class="secondary_menu">
		<% with $Level(1) %>
			<h3 class="secondary_menu_title"><a href="$Link">$Title</a></h3>
		<% end_with %>
		<ul class="menu">
			<% loop Years %>
				<li><a href="$Link">$YearName</a>
					<% if Months %>
						<ul>
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