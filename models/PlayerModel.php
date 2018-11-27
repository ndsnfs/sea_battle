<?php

class PlayerModel extends MainModel
{
    public $playerId;
    public $playerName;

    /**
     * Имитирует метод load, т.е. принимает и сохраняет свойства
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct();
        
        foreach ($data as $prop => $val)
        {            
            if(property_exists($this, $prop))
            {
                $this->$prop = $val;
            }
        }
    }

    /**
     * Возвращает ИД игрока (строковое значение)(хеш md5)
     * @return string
     */
    public function getId()
    {
        return $this->playerId;
    }

    /**
     * Возвращает имя игрока
     * 
     * @return string
     */
    public function getName()
    {
        return $this->playerName;
    }
    
    /**
     * Возвращает массив правил по которым проверяются свойства
     * 
     * @return array
     */
    public static function rules()
    {
        return array(
            'playerId' => 'required|string',
            'playerName' => 'required|alpha',
        );
    }
}