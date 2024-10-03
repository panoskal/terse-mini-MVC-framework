<?php
/**
 * Database Model
 *
 * PHP version 7.3
 */

namespace Core;

use PDO;
use Core\Config;

class Database {

    private static $dbInstance = null;
    private static $dbConnError;
    private $isConn = false;
    private $error;
    private $stmt;
	protected $dbh;
	protected $query;
	protected $params;


	public function __construct() {
		$this->dbh = self::dbConnect();
		$this->query = '';
		$this->params = [];
	}

    public static function dbConnect() {
        $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
        $options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=(SELECT REPLACE(REPLACE(REPLACE(@@sql_mode,"ONLY_FULL_GROUP_BY",""), "NO_ZERO_IN_DATE", ""), "NO_ZERO_DATE", ""));',
            PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES  => false,
        );
        if (!isset(self::$dbInstance)) {
            try {
                self::$dbInstance = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD, $options);
            } catch(\PDOException $e) {
                self::$dbConnError = $e->getMessage();
                echo "<h1>Unable to connect to database</h1><p>".self::$dbConnError."</p>";
                die();
            }
        }
        
        return self::$dbInstance;
        
    }

    private function __clone() {} // prevent cloning

    private function __wakeup() {} // prevent unserialization

    /**
     * Prepares a statement for execution and returns a statement object
     * @param string $query [[The query string]]
     * @return Object [[PDOStatement object. If the database server cannot successfully prepare the statement, PDO::prepare() returns FALSE or emits PDOException]]
     */
    public function query() {
        $this->stmt = $this->dbh->prepare($this->query);
    }

    /**
     * Adds parameters to the parameter array binded to their data types
     */
    public function bind() {
        if (!empty($this->params)) {
            foreach($this->params as $param => $value) {
                if(is_int($value)) {
                    $type = PDO::PARAM_INT;
                } else if(is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } else if(is_null($value)) {
                    $type = PDO::PARAM_NULL;
                } else {
                    $type = PDO::PARAM_STR;
                }
                $this->stmt->bindValue($param, $value, $type);
            }
        }
    }

    /**
     * Executes the prepared statement
     * @return Boolean
     */
    public function execute() {
        return $this->stmt->execute();
    }


    /**
     * Executes non-prepared statement that doesn't return result sets
     * @return Boolean
     */
    public function imExec() {
        $this->dbh->exec($this->query);
    }

    /**
     * 1. Calls prepare statement method
     * 2. Binds the params if any
     * 3. Calls the execute method
     * 4. Creates an object instantiation by matching the table column names with the object attributes
     *
     * @return Class
     */
    public function getObj($class, $args) {
        try {
            $this->query($this->query);
            if (count($this->params)) {
                $this->bind($this->params);
            }
            $this->execute();
            return $this->stmt->fetchAll(PDO::FETCH_CLASS, $class, $args);
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
            throw new \Exception("Query fail: ". $this->error);
        }
    }

    /**
     * @return Array
     */
    public function getRows() {
        try {
            $this->query($this->query);
            if (count($this->params)) {
                $this->bind($this->params);
            }
            $this->execute();
            return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
            throw new \Exception("Query fail: ". $this->error);
        }
    }

    /**
     * [[Description]]
     * @return [[Type]] [[Description]]
     */
    public function getRow() {
        try {
            $this->query($this->query);
            if (count($this->params)) {
                $this->bind($this->params);
            }
            $this->execute();
            return $this->stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
            throw new \Exception("Query fail: ". $this->error);
        }
    }

    /**
    *  Get error from a try catch inside transaction
    */
    public function returnError(){
        return $this->error;
    }

    /**
     * [[Description]]
     * @return [[Type]] [[Description]]
     */
    public function affectRow() {
        try {
            $this->query($this->query);
            if (count($this->params)) {
                $this->bind($this->params);
            }
            $this->execute();
            return $this->numRows();
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
            throw new \Exception("Query fail: ". $this->error);
        }
    }

    /**
     * [[Description]]
     */
    public function insertRow() {
        return $this->affectRow($this->query, $this->params);
    }

    /**
     * [[Description]]
     */
    public function updateRow() {
        return $this->affectRow($this->query, $this->params);
    }

    /**
     * [[Description]]
     */
    public function deleteRow() {
        return $this->affectRow($this->query, $this->params);
    }
    
    
    /**
     * [[Description]]
     */
    public function executeQuery() {
        return $this->affectRow($this->query, $this->params);
    }

    /**
     * Returns the auto generated id used in the latest query
     * @return [[Type]] [[Description]]
     */
    public function getInsertedId() {
        return $this->dbh->lastInsertId();
    }

    /**
     * Gets the number of rows in a result
     * @return [[Type]] [[Description]]
     */
    public function numRows() {
        return $this->stmt->rowCount();
    }

    /**
     * Begin transaction
     * @return [[Type]] [[Description]]
     */
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    /**
     * Execute transaction
     * @return [[Type]] [[Description]]
     */
    public function endTransaction() {
        return $this->dbh->commit();
    }

    /**
     * Rollback
     * @return [[Type]] [[Description]]
     */
    public function cancelTransaction() {
        return $this->dbh->rollBack();
    }

    /**
     * Show database tables if exist
     */
    public function showTables() {
        $sql = "SHOW TABLES";
        $result = $this->getRows($sql);
        return !empty($result)?$result: false;
    }

    /**
     * Disconnect from db
     */
    public function dbDisconnect(){
        $this->isConn = FALSE;
		return null;
    }

}