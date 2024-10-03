<?php
/**
 * VirtuemartProductManufacturers Class
 * virtuemart_product_manufacturers table
 * 
 * PHP version 7.3
 */

namespace App\Models\Tables;

use Core\Config;
use Core\MySql;
use Core\Helper;

class Users extends TableActions
{    
    
    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }
    
}