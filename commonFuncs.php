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