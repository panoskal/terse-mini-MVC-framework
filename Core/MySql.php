<?php

/**
 * MySql Model
 *
 * PHP version 7.3
 */

namespace Core;

class MySql extends Database
{
    
    public static function init()
    {
        $mysql = new MySql();
        
        return $mysql;
    }
    
    public function rawQuery(string $queryString, ?iterable $params = null)
    {
        $this->query = $queryString;

        if (!empty($params)) {
            $this->params = $params;
        }

        return $this;
    }

    public function selectQuery(array $fields, string $table)
    {
        $this->query = "SELECT ";
        $this->params = [];

        if (empty($table)) {
            return false;
        }

        if (empty($fields)) {
            // throw Error Exception
            $this->query .= ' * ';
        } else {
            foreach ($fields as $field) {
                $this->query .= $field . ', ';
            }

            $this->query = rtrim($this->query, ', ');
        }

        $this->query .= " FROM $table;";

        return $this;
    }

    public function distinctQuery(array $fields, string $table)
    {
        $this->query = "SELECT DISTINCT ";
        $this->params = [];

        if (empty($table)) {
            return false;
        }

        if (empty($fields)) {
            // throw Error Exception
            $this->query .= ' * ';
        } else {
            foreach ($fields as $field) {
                $this->query .= $field . ', ';
            }

            $this->query = rtrim($this->query, ', ');
        }

        $this->query .= " FROM $table;";

        return $this;
    }
    
    
    
    public function countQuery(string $table)
    {
        $this->query = "SELECT COUNT(*) AS count_num ";
        $this->params = [];

        if (empty($table)) {
            return false;
        }

        $this->query .= " FROM $table;";

        return $this;
    }


    public function insertQuery(array $fields = [], string $table)
    {
        if (!empty($fields)) {
            $fieldKeys = $fieldValues = '';
            $this->params = [];
            foreach ($fields as $fkey=>$fval) {
                $fieldKeys .=  "$fkey, ";
                $fieldValues .= ":$fkey, ";
                $this->params[":$fkey"] = $fval;
            }
        }

        $fieldKeys = rtrim($fieldKeys, ', ');
        $fieldValues = rtrim($fieldValues, ', ');

        $this->query = "INSERT INTO $table ( $fieldKeys ) VALUES ( $fieldValues );";

        return $this;
    }


    public function updateQuery(array $fields = [], string $table)
    {
        if (!empty($fields)) {
            $fieldValuePairs = '';
            $this->params = [];
            foreach ($fields as $fkey=>$fval) {
                $fieldValuePairs .= "$table.$fkey=:$fkey, ";
                $this->params[":$fkey"] = $fval;
            }
        }

        $fieldValuePairs = rtrim($fieldValuePairs, ', ');

        $this->query = "UPDATE $table SET $fieldValuePairs;";

        return $this;
    }
    
    
    public function onDuplicate($fields)
    {
        $this->query = rtrim($this->query, ';');
        
        if (!empty($fields)) {
            $on_duplicate = '';
            foreach ($fields as $fkey=>$fval) {
                $on_duplicate .= $fkey.'='.':'.$fkey.'2,';
                $this->params[":{$fkey}2"] = $fval;
            }
        }
        
        $on_duplicate = rtrim($on_duplicate,',');
        
        $this->query .= " ON DUPLICATE KEY UPDATE $on_duplicate;";
        
        return $this;
    }


    public function deleteQuery(string $table)
    {
        $this->params = [];

        $this->query = "DELETE FROM $table;";

        return $this;
    }


    public function groupByQuery(string $field)
    {
        $this->query = rtrim($this->query, ';');

        $this->query .= " GROUP BY $field;";

        return $this;
    }

    public function havingCountQuery()
    {
        $this->query = rtrim($this->query, ';');

        $this->query .= " HAVING COUNT(*) > 1;";

        return $this;
    }

    public function innerJoinQuery(string $table, string $on)
    {
        $this->query = rtrim($this->query, ';');

        $this->query .= " INNER JOIN $table ON $on";

        return $this;
    }
    
    
    public function joinQuery(string $table, string $on)
    {
        $this->query = rtrim($this->query, ';');

        $this->query .= " JOIN $table ON $on";

        return $this;
    }


    private function getMultiValuesForInClause(string $string)
    {
        $return = '';
        $values = explode('|', $string);

        $i = 0;
        $return = " (";
        foreach ($values as $value) {
            $return .= ":elem_" . $i . ",";
            $this->params[":elem_" . $i] = $value;
            $i++;
        }

        $return = rtrim($return, ',') . ")";

        return $return;
    }


    public function where(array $conditions = [])
    {

        // $jfkd = [
        //     ['where', 'name', '=', '23'],
        //     ['or', 'name', 'in', '']
        // ];

        $this->query = rtrim($this->query, ';');

        if (empty($conditions)) {
            // throw Error Exception
            return false;
        }

        foreach ($conditions as $value) {

            $connection = empty($value[0]) ? "WHERE" : "{$value[0]}";
            
            $this->query .= " $connection {$value[1]} {$value[2]} ";

            if ($value[2] === 'IN') {
                $this->query .= $this->getMultiValuesForInClause($value[3]);
                continue;
            } else if ($value[2] === 'LIKE') {
                $this->query .= " '%{$value[3]}%';";
                continue;
            } else if ($value[2] === 'IS NULL') {
                $this->query .= " ";
                continue;
            } else {
                $this->query .= " :{$value[1]}";
                $this->params[":{$value[1]}"] = $value[3];
                if (strpos($value[1], '.') !== false) {
                    $fields = explode('.', $value[1]);
                    $this->query .= " :{$fields[1]}";
                    $this->params[":{$fields[1]}"] = $value[3];
                } else {
                    $this->query .= " :{$value[1]}";
                    $this->params[":{$value[1]}"] = $value[3];
                }
            }
        }

        $this->query .= ";";

        return $this;
    }


    public function whereQuery(string $connect = '', string $field, string $relation, array $values = [], string $group = '')
    {
        $this->query = rtrim($this->query, ';');

        $op = '';

        switch ($relation) {
            case 'equal':
                $op = '=';
                break;
            case 'not equal':
                $op = '!=';
                break;
            case 'like':
                $op = 'LIKE';
                break;
            case 'greater or equal':
                $op = '>=';
                break;
            case 'greater':
                $op = '>';
                break;
            case 'less or equal':
                $op = '<=';
                break;
            case 'less':
                $op = '<';
                break;
            case 'in':
                $op = 'IN';
                break;
            case 'null':
                $op = 'IS NULL';
                break;
            default:
                throw new \Exception('Illegal value for query relation', 500);
        }

        switch ($connect) {
            case 'where':
                $connective = 'WHERE';
                break;
            case 'and':
                $connective = 'AND';
                break;
            case 'or':
                $connective = 'OR';
                break;
            default:
                $connective = 'WHERE';
        }

        switch ($group) {
            case 'open':
                $open = '(';
                $close = '';
                break;
            case 'close':
                $open = '';
                $close = ')';
                break;
            default:
                $open = $close = '';
        }

        if ($op === 'IN') {
            $this->query .= " $connective $open $field IN (";
            $i = 0;
            foreach ($values as $value) {
                $elem = ":elem_".$i;
                $this->query .= $elem . ",";
                $this->params["$elem"] = $value;
                $i++;
            }
            $this->query = rtrim($this->query, ',');
            $this->query .= ") $close;";
        } else if ($op === 'IS NULL') {
            $this->query .= " $connective $field $op;";
        } else if ($op === 'LIKE') {
            $this->query .= " $connective $field $op '%{$values[0]}%';";
        } else {
            if (strpos($field, '.') !== false) {
                $fields = explode('.', $field);
                $this->query .= " $connective $open $field $op :$fields[1] $close;";
                $this->params[":$fields[1]"] = $values[0];
            } else {
                $this->query .= " $connective $open $field $op :$field $close;";
                $this->params[":$field"] = $values[0];
            }

        }
        
        $this->query = preg_replace('!\s+!', ' ', $this->query);
        
        return $this;
    }


    public function whereRawQuery(string $where, string $logicOp = '') {
		
		$this->query = rtrim($this->query  ,';');
        
        if ($logicOp === '') $logicOp = "WHERE";
        
		$this->query .= " {$logicOp} {$where};";
		
		return $this;
		
	}


    public function limitQuery(int $limitNumber, $ok = false)
    {
        $this->query = rtrim($this->query, ';');

        $this->query .= " LIMIT " . (int) $limitNumber . ";";

        return $this;
    }

    public function orderQuery(string $column, bool $asc = false)
    {
        $this->query = rtrim($this->query, ';');

        $orderDir = empty($asc) ? 'DESC' : 'ASC';

        $this->query .= " ORDER BY $column $orderDir;";
        
        return $this;
    }


    public function groupQuery(string $group)
    {
        $this->query = rtrim($this->query, ';');

        if (!empty($group)) {
            $this->query .= " GROUP BY $group;";
        }

        return $this;
    }


    public function havingQuery(string $having)
    {
        $this->query = rtrim($this->query, ';');

        if (!empty($having)) {
            $this->query .= " HAVING $having;";
        }

        return $this;
    }
}
