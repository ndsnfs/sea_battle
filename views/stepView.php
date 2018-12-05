<!doctype html>
<html>
    <head>
        <title>title</title>
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
    </head>
    <body>
        <h3>Ход делает - <?= $player->getName() ?></h3>
        <form action="?page=step" method="POST">
                <?php foreach ($fields as $playerId => $field): ?>
                        <?php if($player->getId() === $playerId): ?>
                        <?php

                        $cnt = 0;

                        ?>
                        <div class="field">
                                <!-- первая строка с названиями столбцов -->
                                <div class="tr">
                                        <div class="td"></div>
                                        <?php for ($i = $minCoordinat; $i <= $maxCoordinat; $i++): ?>
                                        <div class="td"><?= $i ?></div>
                                        <?php endfor ?>
                                </div>
                                <!-- первая строка с названиями столбцов END -->
                                <?php foreach ($field as $row): ?>
                                <div class="tr">
                                        <!-- Первй столбец -->
                                        <div class="td"><?= $cnt ?></div>
                                        <!-- Первй столбец END -->
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
                                        <?php $cnt++; ?>
                                </div>
                                <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <?php

                        $cnt = 0;

                        ?>
                        <div class="field">
                                <!-- первая строка с названиями столбцов -->
                                <div class="tr">
                                        <div class="td"></div>
                                        <?php for ($i = $minCoordinat; $i <= $maxCoordinat; $i++): ?>
                                        <div class="td"><?= $i ?></div>
                                        <?php endfor ?>
                                </div>
                                <!-- первая строка с названиями столбцов END -->
                                <?php foreach ($field as $row): ?>
                                <!-- TR -->
                                <div class="tr">
                                        <div class="td"><?= $cnt ?></div>
                                        <?php foreach ($row as $k => $cellValue): ?>
                                                <?php if($cellValue === 1): ?>
                                                <div class="td"><input type="radio" name="cell" value="<?= $k ?>"></div>
                                                <?php elseif($cellValue === 2): ?>
                                                <div class="td"><input type="radio" name="cell" value="<?= $k ?>"></div>
                                                <?php elseif($cellValue === 3): ?>
                                                <div class="td">.</div>
                                                <?php elseif($cellValue === 4): ?>
                                                <div class="td">x</div>
                                                <?php endif ?>
                                        <?php endforeach; ?>
                                        <?php $cnt++; ?>
                                </div>
                                <!-- TR END -->
                                <?php endforeach; ?>
                                <input type="hidden" name="enemy_player_id" value="<?= $playerId ?>">
                                <?php // (new StateWidget())->draw() ?>
                                <input type="submit" value="огонь!" name="init" class="btn btn-block">
                        </div>
                        <?php endif ?>
                <?php endforeach; ?>
                <input type="hidden" name="current_player_id" value="<?= $player->getId() ?>">
        </form>
        <div style="clear: both;">
                <a href="?page=reset">exit</a>
        </div>
    </body>
</html>