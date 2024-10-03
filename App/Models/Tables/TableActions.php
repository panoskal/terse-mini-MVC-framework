<?php
/**
 * TableActions Class
 *
 * PHP version 7.4
 */

namespace App\Models\Tables;

use Core\MySql;

abstract class TableActions
{    

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }
    

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getAllActiveAndApproved() 
    {
        $findRows = MySql::init()
            ->selectQuery([
                '*',
            ], $this->tableName)
            ->whereQuery('where', 'approved', 'equal', [1])
            ->whereQuery('and', 'active', 'equal', [1])
            ->getRows();

        return !empty($findRows) ? $findRows : false;
    }



    public function getAll() 
    {
        $findRows = MySql::init()
            ->selectQuery([
                '*',
            ], $this->tableName)
            ->getRows();

        return !empty($findRows) ? $findRows : false;
    }


    /**
     * Undocumented function
     *
     * @param array $data
     * @return void
     */
    public function getTableRowByKeys(array $data)
    {
        $findRow = MySql::init()
            ->selectQuery([], $this->tableName)
            ->whereQuery('where', $data['name'], 'equal', [$data['value']])
            ->limitQuery(1)
            ->getRow();

        return $findRow;
    }

    /**
     * Undocumented function
     *
     * @param array $data
     * @return void
     */
    public function insertTableData(array $data) 
    {
        $insert = MySql::init()
            ->insertQuery($data, $this->tableName)
            ->insertRow();

        if (!$insert) {
            throw new \Exception('Could not insert entries into database.', 500);
        }
        
        $id = MySql::init()->getInsertedId();
        
        if (empty($id)) {
            throw new \Exception('Could not insert entries into database.', 500);
        }
        
        return $id;
    }

    public function getTableRowsByKeys(array $data){}
    public function createTableData(array $data) {}
    public function deleteTableData() {}
    public function updateTableData(array $data, int $id) {}
    
}
