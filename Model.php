<?php
//require_once("DBService.php");
//require_once("constants.php");

namespace core_fw;
use core_fw\DatabaseService;
use ReflectionClass;

class Model extends DatabaseService
{
    protected string $_tablename;
    private ReflectionClass $class;
    protected array $primary_key;

    /**
     * Model constructor.
     */
    public function __construct()
    {
        parent::__construct(Application::$app->database);
        $this->class = new ReflectionClass($this);
        //$this->_tablename = $this->class->getName();
        $this->getTable();
        $this->primary_key = $this->get_primary_key();
    }

    public function getTable()
    {
        $class_name = $this->class->getName();
        $class_name = explode('\\', $class_name);
        $this->_tablename = end($class_name);
    }

    public function override_table(string $tablename)
    {
        $this->_tablename = $tablename;
    }

    function get_primary_key() : array{
        $primary = array();
        $doc = $this->class->getDocComment();
        $arr = explode('*', $doc);
        foreach ($arr as $index){
            if (preg_match('/@id/i', $index)) {
                $data = explode(' ', $index);
                $primary["type"] = $data[2];
                $primary["id"] = rtrim($data[3]);
                break;
            }
        }

        return $primary;
    }


    public function save($data)
    {
        $class = new ReflectionClass($this);
        $new_array = parent::array_map_properties($this->class, $data);
        $columns_array = parent::insert_columns($new_array, 1);
        parent::insert($this->_tablename, $columns_array);
        //return $new_array;
    }

    public function findAll()
    {
        return $this->select($this->_tablename, '*', null);
    }
    
    public function remove($data)
    {
        $class = new ReflectionClass($this);
        print_r($this->primary_key);
        if (array_key_exists($this->primary_key['id'], $data)){
            echo $this->delete($this->_tablename, [ $this->primary_key['id'] => $data[$this->primary_key['id']] ]);
        }

    }

    public function customSelect()
    {
        $columns = ['name', 'age', 'email'];
        $conditions = [
            "name" => "Sanka",
            "age" => 22,
            "email" => "gallagesanka03@gmail.com"
        ];
        $this->select('users', $columns, $conditions);
    }

    public function customDelete()
    {
        $conditions = [
            "name" => "Sanka",
            "age" => 22,
            "email" => "gallagesanka03@gmail.com"
        ];

        $this->delete('users', $conditions);
    }
}