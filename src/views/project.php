<div class="container-fluid">
	<table class="table table-bordered table-striped">
		<caption>
			<h3>Project `<?= $active->name; ?>` details</h3>
		</caption>
		<tr>
			<td style="text-align: right; font-weight: bold;">CSS file</td>
			<td><?= $active->css_file; ?></td>
		</tr>
		<tr>
			<td style="text-align: right; font-weight: bold;">SCSS file</td>
			<td><?= $active->scss_file; ?></td>
		</tr>
		<tr>
			<td style="text-align: right; font-weight: bold;">Cache Dir</td>
			<td>
				<?= $active->cache_dir; ?>
				&nbsp;
				<a href="<?= url('clearcache'); ?>" class="btn btn-warning btn-xs" title="Clear project cache">Clear Cache</a>
			</td>
		</tr>
		<tr>
			<td style="text-align: right; font-weight: bold;">CSS file style</td>
			<td><?= $active->css_style; ?></td>
		</tr>
		<tr>
			<td style="text-align: right; font-weight: bold;">CSS file signature</td>
			<td><?= $active->signature ? 'Yes' : 'No'; ?></td>
		</tr>
	</table>
</div>

