<?php

class Logger
{    
    public static function log(string $str)
    {
        $file = ROOT_DIR . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'LOG_' . date('Y_m_d') . '.txt';
        $f = fopen($file, 'a+');
        fwrite($f, date('H:m:s') . ' ' . $str . "\n");
        fclose($f);
    }
}

