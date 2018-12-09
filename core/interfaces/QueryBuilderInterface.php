<?php

interface QueryBuilderInterface
{
    public function setSelect(string $select);
    
    public function setFrom(string $table);
    
    public function join(string $table, string $on);
}

