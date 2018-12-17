<?php

class Cell extends MainModel
{
    /**
     * Хранит в себе строку вида 1:2
     * @var string
     */
    public $coordinat;
    
    /**
     * Хранит в себе состояние: пусто, корабль, подбитый, промах
     * @var int 
     */
    public $state;
    
    /**
     * Состояния ячеек
     */
    const EMPTY_CELL = 1;
    const SHIP_CELL = 2;
    const FAILED_CELL = 3;
    const WOUND_CELL = 4;
    
    /**
     * Имитирует метод load, т.е. принимает и сохраняет свойства
     * В базу не лезем, поэтому вызывать род. конструктор нет смысла - просто переопределяем
     * @param array $data
     */
    public function __construct(array $data)
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
     * Возвращает массив правил по которым проверяются свойства
     * 
     * @return array
     */
    public static function rules()
    {
        return array(
            'coordinat' => 'required|pregMath[^\d:\d]',
            'state' => 'required|int'
        );
    }

    /**
     * Разбивает координату на массив, например 6:6 на array(6, 6)
     * @param string $coordinat
     */
    private function _coordinatAsArray(string $coordinat)
    {
        $tmp = explode(':', $coordinat);
        $tmp = array_map(function($v) {
                return (int)$v;
        }, $tmp);

        return $tmp;

        throw new Exception('invalid coordinat');
    }
    
    /**
     * Добавляет ячейке сдвиг по определенному правилу, допустим (2:5)+(0:-1) = 2:4
     * @return object
     */
    public function addOffset($rule)
    {
        $c1 = $this->_coordinatAsArray($this->coordinat); // к этой ячейке приюавляем $c2
        $c2 = $this->_coordinatAsArray($rule); // :FIX - rule не валидируется

        $v1 = $c1[0] + $c2[0];
        $v2 = $c1[1] + $c2[1];
        
// :FIX вынести создание объекта за пределы
        return new self(array('coordinat' => $v1 . ':' . $v2, 'state' => $this->state));
    }
}