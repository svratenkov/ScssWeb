<div class="container-fluid">
	<div class="row bg-info">
		<div class="col-xs-3">
			<a href="<?= url('/'); ?>" class="btn btn-default" title="SCSS Compiler Help"><strong>Home</strong></a>
			<a href="<?= url('compile'); ?>" class="btn btn-primary" title="One-shot compilation of the active project">Compile</a>
			<a href="<?= url('clear'); ?>" class="btn btn-info" title="Clear compiler output">Clear</a>
		<?php if (! $watching) { ?>
			<a href="<?= url('watch'); ?>" class="btn btn-success" title="Start watching mode compilation of the active project">Watch</a>
		<?php } else { ?>
			<a href="<?= url('stopwatch'); ?>" class="btn btn-danger" onclick="watchStop()" title="Stop watching mode compilation of the active project">Stop</a>
		<?php } ?>
		</div>

		<div class="col-xs-9">
			<span class="btn"><strong>Projects:</strong></span>
		<?php foreach ((array) $projects as $name) { ?>
			<a href="<?= url('$'.$name); ?>" class="btn<?= $name == $active ? ' btn-warning active' : ''; ?>" title="Activate project <?= $name; ?> and show it's details"><?= $name; ?></a>
		<?php } ?>
		</div>
	</div>
</div>
