<div class="well well-sm">
	<h1>{$title}</h1>

	{if $groupmsglist}
		<table class="data table table-striped responsive-utilities jambo-table Sortable">

			<tr>
				<th>group</th>
				<th>msg</th>
			</tr>

			{foreach from=$groupmsglist item=group}
				<tr>
					<td>{$group.group}</td>
					<td>{$group.msg}</td>
				</tr>
			{/foreach}

		</table>
		<p>View <a href="group-list">all groups</a>.</p>
	{else}
		<p>Regex of groups to add to the site.</p>
		<form action="group-bulk?action=submit" method="POST">
			{{csrf_field()}}
			<table class="input">

				<tr>
					<td width="90">Group List:</td>
					<td>
						<textarea id="groupfilter" name="groupfilter"></textarea>
						<div class="hint">A regular expression to match against group names e.g.
							alt.binaries.cd.image.linux|alt.binaries.warez.linux
						</div>
					</td>
				</tr>
				<tr>
					<td><label for="active">Active</label>:</td>
					<td>
						{html_radios id="active" name='active' values=$yesno_ids output=$yesno_names selected=1 separator='<br />'}
						<div class="hint">Inactive groups will not have headers downloaded for them.</div>
					</td>
				</tr>
				<tr>
					<td><label for="backfill">Backfill:</label></td>
					<td>
						{html_radios id="backfill" name='backfill' values=$yesno_ids output=$yesno_names selected=0 separator='<br />'}
						<div class="hint">Inactive groups will not have backfill headers downloaded for them.</div>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input class="btn btn-default" type="submit" value="Add Groups"/>
					</td>
				</tr>

			</table>

		</form>
	{/if}
</div>
