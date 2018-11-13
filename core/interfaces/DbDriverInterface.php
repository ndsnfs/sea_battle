<?php

interface DbDriverInterface
{
	public function insert(string $table, array $data);
	public function update(string $table, array $data, array $where);
	public function getOne(string $table, array $data);
	public function getAll(string $table);
	public function getWhere(string $table, array $data);
}