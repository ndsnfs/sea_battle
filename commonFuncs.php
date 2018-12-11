<?php

function initStore()
{
	// если есть post players и  fields
	// заполняем импровизируемую талицу
	// пришедшими игроками и их состоянием полей
	if(Input::post('players') && Input::post('fields'))
	{
		$players = unserialize(base64_decode(Input::post('players')));
		$fields = unserialize(base64_decode(Input::post('fields')));
		
		$store = Store::getInstance();

		foreach ($players as $p)
		{
			$store->insert('players', $p);
		}

		foreach ($fields as $f)
		{
			$store->insert('fields', $f);
		}
	}
}

function debug(Array $a)
{
	echo '<pre>';
	print_r($a);
	echo '</pre>';
}

function htmlDebug($data, $action = 'echo')
{
    echo '<!doctype html>';
    echo '<html>';
    echo '<body>';
    if($action == 'echo')
    {
        echo $data;
    }
    elseif($action == 'debug')
    {
        debug($data);
    }
    elseif($action == 'dump')
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
    else
    {
        echo 'Параметр не задан';
    }
    echo '</body>';
    echo '</html>';
    exit;
}