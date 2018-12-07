<!doctype html>
<html>
    <head>
        <title>title</title>
        <link rel="stylesheet" href="./static/css/main.css"/>
    </head>
    <body>
        <div class="my-container">
            <div class="my-row">
                <div class="my-col my-col-12">
                    <h3 class="game-header">Игрок №<?= count($players) + 1 ?> пофикси ошибки</h3>
                </div>
            </div>
            <div class="my-row">
                <div class="my-col my-col-12">
                    <form action="?page=create" method="POST">
                        <div class="my-form-group">
                            <label>Имя</label>
                            <br>
                            <input name="player_name" value="<?= $playerName ?>">
                        </div>
                        <?php if(isset($errors['playerName']) && count($errors['playerName']) > 0): ?>
                        <div class="my-form-group">
                            <div class="errors">
                            <?php foreach ($errors['playerName'] as $row): ?>
                            <div class="errors__row"><?= $row ?></div>
                            <?php endforeach ?>
                            </div>
                        </div>
                        <?php endif ?>
                        <div class="my-form-group">
                            <?php $maxCoordinat = 9; $counterX = 0; $counterY = 0; ?>
                            <div class="my-td"></div>
                            <?php for($i = 0; $i <= $maxCoordinat; $i++): ?>
                            <div class="my-td"><?= $i ?></div>
                            <?php endfor ?>
                            <br>
                            <?php foreach($field as $cell): ?>
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
                                <?php if($cell->state == 2): ?>
                                <div class="my-td__ship<?= isset($errors['ajacentCoordinats']) && in_array($cell->coordinat, $errors['ajacentCoordinats']) ? ' my-td__ship_ajacent' : '' ?>">
                                    <input type="checkbox" name="cell_status[<?= $cell->coordinat ?>]" value="<?= $cell->state ?>" checked="true">
                                </div>
                                <?php else: ?>
                                <input type="checkbox" name="cell_status[<?= $cell->coordinat ?>]" value="2">
                                <?php endif ?>
                            </div>
                            <?php $counterX++; ?>
                            <?php endforeach ?>
                        </div>
                        <?php if(isset($errors['fieldState']) && count($errors['fieldState']) > 0): ?>
                        <div class="my-form-group">
                            <div class="errors">
                                <?php foreach ($errors['fieldState'] as $row): ?>
                                <div class="errors__row"><?= $row ?></div>
                                <?php endforeach ?>
                            </div>
                        </div>
                        <?php endif ?>
                        <div class="my-form-group">
                            <input type="submit" value="Сохранить">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
