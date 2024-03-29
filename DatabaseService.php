<?php

namespace core;

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

    public function mapDataToClassProperties($className, $dataToBeMapped): array
    {
        $arrayOfMappedProperties = array();
        foreach ($className->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            if (array_key_exists($property->getName(), $dataToBeMapped))
                $arrayOfMappedProperties[$property->getName()] = $dataToBeMapped[$property->getName()];
        }

        return $arrayOfMappedProperties;
    }

    public function refactorDataForQuery($dataToBeRefactored, $queryType): array
    {
        $refactoredData = array();
        if ($queryType === 0) {
            foreach ($dataToBeRefactored as $columnName => $value) {
                if (gettype($value) === "integer" || gettype($value) === "double")
                    array_push($refactoredData, "$columnName = $value");
                else
                    array_push($refactoredData, "$columnName = \"$value\"");
            }
        } elseif ($queryType === 1) {
            foreach ($dataToBeRefactored as $columnName => $value) {
                if (gettype($value) === "integer" || gettype($value) === "double")
                    $refactoredData[$columnName] = $value;
                else
                    $refactoredData[$columnName] = "'$value'";
            }
        }
        //print_r($refactoredData);
        return $refactoredData;
    }

    public function insert($tableName, $columnsAndValues): bool|int
    {
        $columnNames = array_keys($columnsAndValues);
        $separatedColumnNames = implode(', ', $columnNames);
        $valuesForColumns = implode(", ", $columnsAndValues);

        $sqlStatement = "INSERT INTO $tableName($separatedColumnNames) 
                VALUES($valuesForColumns)";

        echo $sqlStatement;
        return $this->pdo->exec($sqlStatement);
        //echo $sql;
        //return $this->execute($sql);

        //return true;
    }

    function select($tableName, $columns, $conditions, $returnTypeFlag): int|array
    {

        if (is_array($columns) && !empty($columns)) {
            $columnsToRetrieve = implode(', ', $columns);
        }

        if (is_string($columns) && !empty($columns)) {
            $columnsToRetrieve = '*';
        }

        $sqlStatement = "SELECT $columnsToRetrieve FROM $tableName";

        if (!empty($conditions)) {
            $conditionsQueryString = implode(", ", $this->refactorDataForQuery($conditions, 0));
            $sqlStatement .= " WHERE $conditionsQueryString";
        }

        return $this->executeSelectStatement(query: $sqlStatement, flag: $returnTypeFlag);

        //return $sql;

    }

    function delete($tableName, $conditions): bool|int
    {
        $conditionQueryString = implode(', ', $this->refactorDataForQuery($conditions, 0));
        $sqlStatement = "DELETE FROM $tableName WHERE $conditionQueryString";
        echo $sqlStatement;
        return $this->pdo->exec($sqlStatement);
    }

    function update($tableName, $columns, $conditions): bool|int
    {
        $columnQueryString = implode(', ', $this->refactorDataForQuery($columns, 0));
        $conditionQueryString = implode(', ', $this->refactorDataForQuery($conditions, 0));
        $sqlStatement = "UPDATE $tableName SET $columnQueryString  WHERE $conditionQueryString";
        return $this->pdo->exec($sqlStatement);
        //return $sql;
    }

    function customSqlQuery($sqlQuery, $returnTypeFlag): bool|int|array
    {
        $separatedQuery = explode(" ", $sqlQuery, 2);
        $queryType = strtolower($separatedQuery[0]);

        if ($queryType === "select") {
            return $this->executeSelectStatement($sqlQuery, $returnTypeFlag);
        } elseif ($queryType === "delete" || $queryType === "update" || $queryType === "insert") {
            return $this->pdo->exec($sqlQuery);
        } else {
            return false;
        }
    }

    public function executeSelectStatement($query, $flag): int|array
    {
        $statement = $this->pdo->query($query);

        if ($flag === DatabaseService::FETCH_COUNT)
            return $statement->rowCount();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}