<?php
/**
 * Table Class
 *
 * PHP version 7.4
 */

namespace App\Models\Tables;

class Table {
    
    /**
     * Convert the string with hyphens to camelCase,
     * e.g. add_new => addNew
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected static function convertToCamelCase($string) 
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }
    
    /**
     * Get the namespace for the class.
     *
     * @return string The request path
     */
    protected static function getNamespace() 
    {
        $namespace = 'App\Models\Tables\\';

        return $namespace;
    }

    public static function process(string $table, array $data = []) 
    {
        
        $className = self::convertToCamelCase($table);
        
        $model = self::getNamespace() . ucfirst($className);
        
        if (!empty($model) && class_exists($model)) {
            $table = new $model((string) $table);
            return $table;
        } else {
            throw new \Exception('Could not find class ' . $className);
        }
        
    }
    
    
    

}
