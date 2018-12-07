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
                    <h3 class="game-header">Игрок №<?= count($players) + 1 ?> вводи свои данные</h3>
                </div>
            </div>
            <div class="my-row">
                <div class="my-col my-col-12">
                    <form action="?page=create" method="POST">
                        <div class="my-form-group">
                            <label>Имя</label>
                            <br>
                            <input name="player_name" value="">
                        </div>
                        <div class="my-form-group">
                            <?php  $maxCoordinat = 9; $counterX = 0; $counterY = 0; ?>
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
                                    <input type="checkbox" name="cell_status[<?= $cell->coordinat ?>]" value="2">
                                </div>
                            <?php $counterX++; ?>
                            <?php endforeach ?>
                        </div>
                        <div class="my-form-group">
                            <input type="submit" value="Сохранить">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
