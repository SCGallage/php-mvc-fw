<?php

namespace core_fw;

use PDO;
use ReflectionProperty;

class DatabaseService
{
    protected PDO $pdo;
    const FETCH_COUNT = 0;
    const FETCH_ALL = 1;

    /**
     * DatabaseService constructor.
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
    }


    private function connect($pdo)
    {
        $this->pdo = $pdo;
    }

    function array_map_assoc($callback, $array): array
    {
        $r = array();
        foreach ($array as $key => $value) {
            $r[$key] = $callback($key, $value);
        }

        return $r;
    }

    public function array_map_properties($class, $data): array
    {
        $new_array = array();
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (array_key_exists($property->getName(), $data))
                $new_array[$property->getName()] = $data[$property->getName()];
        }

        return $new_array;
    }

    public function insert_columns($array, $type): array
    {
        $r = array();
        if ($type === 0) {
            foreach ($array as $key => $value) {
                if (gettype($value) === "integer" || gettype($value) === "double")
                    array_push($r, "$key = $value");
                else
                    array_push($r, "$key = \"$value\"");
            }
        } elseif ($type === 1) {
            foreach ($array as $key => $value) {
                if (gettype($value) === "integer" || gettype($value) === "double")
                    $r[$key] = $value;
                else
                    $r[$key] = "'$value'";
            }
        }
        print_r($r);
        return $r;
    }

    public function insert($tablename, $parameters)
    {
        $columns = array_keys($parameters);
        $columns_str = implode(', ', $columns);
        $value_str = implode(", ", $parameters);
        echo "INSERT INTO $tablename($columns_str) 
                VALUES($value_str)";
        $sql = "INSERT INTO $tablename($columns_str) 
                VALUES($value_str)";

        //echo $sql;
        return $this->execute($sql);

        //return true;
    }

    function select($tablename, $columns, $conditions, $flag)
    {

        if (is_array($columns) && !empty($columns)) {
            $column_str = implode(', ', $columns);
        }

        if (is_string($columns) && !empty($columns)) {
            $column_str = '*';
        }

        $sql = "SELECT $column_str FROM $tablename";

        if (!empty($conditions)) {
            $cond_str = implode(", ", $this->insert_columns($conditions, 0));
            $sql .= " WHERE $cond_str";
        }
        echo $sql;
        $stmt = $this->pdo->query($sql);
        //return $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($flag === DatabaseService::FETCH_COUNT)
            return $stmt->rowCount();
        if ($flag === DatabaseService::FETCH_ALL)
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        //return $sql;

    }

    function delete($tablename, $conditions)
    {
        $cond_str = implode(', ', $this->insert_columns($conditions, 0));
        $sql = "DELETE FROM $tablename WHERE $cond_str";
        echo $sql;
        return $this->pdo->exec($sql);
    }

    function update($tablename, $columns, $conditions)
    {
        $column_str = implode(', ', $this->insert_columns($columns, 0));
        $cond_str = implode(', ', $this->insert_columns($conditions, 0));
        $sql = "UPDATE $tablename SET $column_str  WHERE $cond_str";
        return $this->pdo->exec($sql);
        //return $sql;
    }

    function custom_query($query)
    {
        $split = explode(" ", $query, 2);
        $query_type = strtolower($split[0]);
        print_r($split);
        echo $query_type;
        if ($query_type === "select") {

        } elseif ($query_type === "delete") {

        } elseif ($query_type === "update") {

        } elseif ($query_type === "insert") {

        } else {

        }
    }

    public function execute($sql): bool|\PDOStatement
    {
        return $this->pdo->query($sql);
    }
}