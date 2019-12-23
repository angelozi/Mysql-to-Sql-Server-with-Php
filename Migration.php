<?php

class Migration
{
    private $pdoSql;
    private $pdoMysql;

    public $dataTypesMs2My = [
        'integer' => 'int',
        'tinyint' => 'tinyint',
        'smallint' => 'smallint',
        'mediumint' => 'int',
        'int' => 'int',
        'bigint' => 'bigint',
        'decimal' => 'decimal',
        'float' => 'float',
        'double' => 'float',
        'enum' => 'nvarchar',
        'text' => 'nvarchar',
        'char' => 'nchar',
        'varchar' => 'nvarchar',
        'mediumtext' => 'nvarchar',
        'longtext' => 'nvarchar',
        'bool' => 'bit',
        'boolean' => 'bit',
        'binary' => 'binary',
        'varbinary' => 'varbinary',
        'blob' => 'varbinary',
        'date' => 'date',
        'time' => 'time',
        'datetime' => 'datetime',
        'year' => 'smallint',
        'timestamp' => 'smalldatetime',
        'set' => 'string',
        'bit' => 'string',
        'point' => 'point',
        'geometry' => 'geometry',
    ];

    public $dataTypesStatic = [
        'double' => '53',
        'enum' => '1',
        'text' => 'MAX',
        'mediumtext' => 'MAX'
    ];

    /**
     * @param $sqlSrvDsn
     * @param $sqlSrvUsername
     * @param $sqlSrvPass
     * @param string $charSet
     */
    public function connectSqlSrv($sqlSrvDsn, $sqlSrvUsername, $sqlSrvPass, $charSet = 'UTF-8')
    {
        try{
            $this->pdoSql =  new \PDO($sqlSrvDsn, $sqlSrvUsername, $sqlSrvPass);
            $this->pdoSql->query("SET CHARACTER SET ".$charSet);
        } catch (\PDOException $e) {
            die( $e->getMessage() );
        }

    }


    public function connectMySql($mySqlDsn, $mySqlUsername, $mySqlPass, $charSet = 'UTF-8')
    {
        try{
            $this->pdoMysql =  new \PDO($mySqlDsn, $mySqlUsername, $mySqlPass);
            $this->pdoMysql->query("SET CHARACTER SET ".$charSet);
        } catch (\PDOException $e) {
            die( $e->getMessage() );
        }
    }
}