<style type="text/css">

	.td {
		display: inline-block;
		box-sizing: border-box;
		width: 25px;
		height: 25px;
		text-align: center;
	}

</style>
<h2>Игрок №<?= count($players) + 1 ?> вводи свои данные!</h2>
<form action="?page=init" method="POST">
	<div>
		<label>Имя</label>
		<input type="text" name="player_name">
	</div>
	<div class="tr">
		<div class="td"></div>
		<?php foreach (range('a', 'j') as $x): ?>
		<div class="td"><?= $x ?></div>
		<?php endforeach; ?>
	</div>
	<?php foreach (range(1, 10) as $y): ?>
	<div class="tr">
		<div class="td"><?= $y ?></div>
		<?php foreach (range('a', 'j') as $x): ?>
		<div class="td"><input type="checkbox" name="cell_status[<?= $x ?>:<?= $y ?>]" value="2"></div>
		<?php endforeach; ?>
	</div>
	<?php endforeach; ?>
	<?php // (new StateWidget())->draw() ?>
	<input type="submit" value="сохранить" name="init">
</form>
