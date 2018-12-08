<?php
require_once 'autoload.php';
require_once 'commonFuncs.php';
$config = require_once 'config' . DIRECTORY_SEPARATOR . 'config.php';

define('VIEW_PATH', __DIR__
                            . DIRECTORY_SEPARATOR . 'views'
                            . DIRECTORY_SEPARATOR);

define('ROOT_DIR', __DIR__);

$app = new App();
$app->init($config);
$app->run();