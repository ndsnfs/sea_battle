<h3>Ход делает - <?= $player->getName() ?>; id - <?= $player->getId() ?></h3>
<style type="text/css">
	input {
		margin: 0;
		padding: 0;
	}
	input[type="radio"] {
		margin-top: 5px;
	}
	.td {
		display: inline-block;
		margin-bottom: 2px;
		box-sizing: border-box;
		width: 25px;
		height: 25px;
		text-align: center;
		border: 1px solid gray;
		vertical-align: middle;
		/* border-right: 1px solid gray; */
	}

	.field {
		float: left;
		margin-right: 20px;
		margin-bottom: 10px;
	}
	.bg-checked {
		background-color: gray
	}
	.bg-danger {
		background-color: red;
	}

</style>

<pre>
	<?php // var_dump($playerField) ?>
	<?php //var_dump($enemyField) ?>
</pre>

<form action="?page=step" method="POST">
	<?php foreach ($fields as $field): ?>

		<?php if($player->getId() === $field['player_id']): ?>

		<div class="field">
			<?php foreach ($field['field_state'] as $row): ?>
			<div class="tr">
				<?php foreach ($row as $k => $cellValue): ?>
					<?php if($cellValue === 1): ?>
					<div class="td"></div>
					<?php elseif($cellValue === 2): ?>
					<div class="td bg-checked"></div>
					<?php elseif($cellValue === 3): ?>
					<div class="td">.</div>
					<?php elseif($cellValue === 4): ?>
					<div class="td bg-checked">x</div>
					<?php endif ?>
				<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
		</div>

		<?php else: ?>
		
		<div class="field">
			<?php foreach ($field['field_state'] as $row): ?>
			<!-- TR -->
			<div class="tr">
				<?php foreach ($row as $k => $cellValue): ?>
					<?php if($cellValue === 1): ?>
					<div class="td"><input type="radio" name="cell" value="<?= $k ?>"></div>
					<?php elseif($cellValue === 2): ?>
					<div class="td bg-danger"><input type="radio" name="cell" value="<?= $k ?>"></div>
					<?php elseif($cellValue === 3): ?>
					<div class="td">.</div>
					<?php elseif($cellValue === 4): ?>
					<div class="td">x</div>
					<?php endif ?>
				<?php endforeach; ?>
			</div>
			<!-- TR END -->
			<?php endforeach; ?>
			<input type="hidden" name="enemy_player_id" value="<?= $field['player_id'] ?>">
			<?php (new StateWidget())->draw() ?>
			<input type="submit" value="огонь!" name="init" class="btn btn-block">
		</div>

		<?php endif ?>
	<?php endforeach; ?>
	<input type="hidden" name="current_player_id" value="<?= $player->getId() ?>">
</form>