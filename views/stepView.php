<!doctype html>
<html>
    <head>
        <title>title</title>
        <link rel="stylesheet" href="./static/css/main.css"/>
    </head>
    <body>
        <div class="my-container">
            <div class="my-row mb-2">
                <div class="my-col my-col-12">
                    <h3 class="game-header">Ход делает <?= $current->getName() ?></h3>
                </div>
            </div>
            <div class="my-row">
                <div class="my-col my-col-6">Соперник</div>
                <div class="my-col my-col-6">Мое поле</div>
            </div>
            <div class="my-row">
                <div class="my-col my-col-6">
                    <form action="?r=battle/step" method="POST">
                        <div class="my-form-group">
                            <?php $maxCoordinat = 9; $counterX = 0; $counterY = 0; ?>
                            <div class="my-td"></div>
                            <?php for($i = 0; $i <= $maxCoordinat; $i++): ?>
                            <div class="my-td"><?= $i ?></div>
                            <?php endfor ?>
                            <br>
                            <?php foreach($enemyPlayerField as $cell): ?>
                            <?php

                            if($counterX > 9)
                            {
                                $counterX = 0;
                                $counterY++;
                                echo '<br>';
                            }

                            if($counterX == 0)
                            {
                                echo '<div class="my-td">' . $counterY . '</div>';
                            }

                            ?>
                            <div class="my-td">
                                <?php if($cell->state == 4): ?>
                                <div class="my-td__ship my-td__ship_wound"></div>
                                <?php elseif($cell->state == 3): ?>
                                <div class="my-td__ship my-td__ship_fail">.</div>
                                <?php else: ?>
                                <input type="radio" name="cell" value="<?= $cell->coordinat ?>">
                                <?php endif ?>
                            </div>
                            <?php $counterX++; ?>
                            <?php endforeach ?>
                            <input type="hidden" name="enemy_player_id" value="<?= $enemy->getId() ?>">
                            <input type="hidden" name="current_player_id" value="<?= $current->getId() ?>">
                        </div>
                        <div class="my-form-group">
                            <input type="submit" value="огонь" class="my-btn">
                        </div>
                    </form>
                </div>
                <div class="my-col my-col-6">
                    <?php $counterX = 0; $counterY = 0; ?>
                        <div class="my-td"></div>
                    <?php for($i = 0; $i <= $maxCoordinat; $i++): ?>
                        <div class="my-td"><?= $i ?></div>
                    <?php endfor ?>
                    <br>
                    <?php foreach($currentPlayerField as $cell): ?>
                    <?php

                    if($counterX > 9)
                    {
                        $counterX = 0;
                        $counterY++;
                        echo '<br>';
                    }

                    if($counterX == 0)
                    {
                        echo '<div class="my-td">' . $counterY . '</div>';
                    }

                    ?>
                    <div class="my-td">
                        <?php if($cell->state == 4): ?>
                        <div class="my-td__ship my-td__ship_wound"></div>
                        <?php elseif($cell->state == 3): ?>
                        <div class="my-td__ship my-td__ship_fail">.</div>
                        <?php elseif($cell->state == 2): ?>
                        <div class="my-td__ship my-td__ship_normal"></div>
                        <?php endif ?>
                    </div>
                    <?php $counterX++; ?>
                    <?php endforeach ?>
                </div>
            </div>
            <div class="my-row">
                <a href="?r=battle/reset">сброс</a>
            </div>
        </div>
    </body>
</html>
