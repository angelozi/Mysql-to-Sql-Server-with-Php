<?php
/*
 * Convert MsSql Database to MsSql Database
 * Table, Index and Data
 * */
include "Migration.php";
$migrationMs2My = new Migration();


$conMs = $migrationMs2My->connectSqlSrv("sqlsrv:server=localSqlSrv;Database=dummyDB", 'admin', 'admin123');
$conMy = $migrationMs2My->connectMySql("mysql:host=localMysql;dbname=dummyDB", 'admin', 'admin123');

$_getTables = $conMy->query("SHOW TABLES");

while ($row = $_getTables->fetch(PDO::FETCH_NUM)) {
    $createTableSql ='';
    $tableName = $row[0];

    $_getColums = $conMy->prepare("DESCRIBE ".$tableName);
    $_getColums->execute();

    $tableColums= $_getColums->fetchAll(PDO::FETCH_ASSOC);


    // Table
    $createTableSql .=' CREATE TABLE '.$tableName.' (';

    $tableIndex = []; $colsSql = []; $colNames = []; $colTypes = [];

    foreach ($tableColums as $col){

        $colType =  $col['Type'];
        $colType = str_replace(' unsigned','', $colType);

        preg_match('%\((.*?)\)%', $colType, $typeSize);
        if(isset($typeSize[1]))
            $typeSize = $typeSize[1];
        else
            $typeSize ='';

        $colType = preg_replace('%\(.*?\)%','', $colType);

        if( $migrationMs2My->dataTypesMs2My[$colType] !='nvarchar' && $migrationMs2My->dataTypesMs2My[$colType] !='decimal')
            $typeSize ='';

        if($col['Key']!='PRI' && $col['Key']!='')
            $tableIndex[] = $col['Field'];

        if(isset($migrationMs2My->dataTypesStatic[$colType]))
            $typeSize = $migrationMs2My->dataTypesStatic[$colType];

        if($colType =='enum' and $col['Default']!='')
            $typeSize = ''.strlen($col['Default']).'';

        $def = ($col['Default']!=''?'default \''.$col['Default'].'\'':'');

        if($col['Default']=='CURRENT_TIMESTAMP') $def =' default GETDATE()';
        if($col['Default']=='0000-00-00 00:00:00') $def =' ';

        if($migrationMs2My->dataTypesMs2My[$colType] == 'int')
            $pri = ($col['Key']=='PRI'?'IDENTITY(1,1) PRIMARY KEY':'');
        else
            $pri = ($col['Key']=='PRI'?'PRIMARY KEY':'');

        $colsSql[] = "[".$col['Field']."] ".$migrationMs2My->dataTypesMs2My[$colType]." ".(($typeSize)?'('.$typeSize.')':'')." ".$pri." ".($col['Null']=='No'?'Not Null':'')." ".$def;

        $colNames[$col['Field']] = $col['Field'];
        $colTypes[$col['Field']] = $migrationMs2My->dataTypesMs2My[$colType];

    }

    $createTableSql .= join(',',$colsSql).');';


    // Table indexes
    foreach ($tableIndex as $index)
        $createTableSql .=" CREATE INDEX $index ON $tableName ($index);";

    try{
        $stmt = $conMs->prepare('SET IDENTITY_INSERT '.$tableName.' ON;'."INSERT INTO ".$tableName." (".join(",", $colNames).") VALUES (".join(",",$rowd).")", array( PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY, PDO::SQLSRV_ATTR_QUERY_TIMEOUT => 1  ) );
        $stmt->execute($row);
    }catch(Exception  $e){
        print_r($e->getMessage());
    }


    //Datas
    $tableData = [];
    $query = $conMy->query("SELECT * FROM  ".$tableName, PDO::FETCH_ASSOC);
    if ( $query->rowCount() ) {

        $tableData[] = 'SET IDENTITY_INSERT '.$tableName.' ON;';

        $ekledi = 0;
        foreach ($query as $row) {
            $rowd = [];
            foreach ($row as $r=>$v){
                $v = addslashes($v);
                if($colTypes[$r] =='date')
                    $row[$r] = ($v!='' && $v!='0000-00-00')?'CAST(N\''.$v.'\' AS Date)':'null';
                else if($colTypes[$r] =='datetime')
                    $row[$r] = ($v!='' && $v!='0000-00-00 00:00:00')?'CAST(N\''.$v.'\' AS DateTime)':'null';
                else if($colTypes[$r] =='int' || $colTypes[$r] =='decimal')
                    $row[$r] = (($v=='')?0:$v);
                else
                    $row[$r] ="'$v'";
                $rowd[$r] = '?';
            }

            try{
              $stmt = $conMs->prepare('SET IDENTITY_INSERT '.$tableName.' ON;'."INSERT INTO ".$tableName." (".join(",", $colNames).") VALUES (".join(",",$rowd).")", array( PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY, PDO::SQLSRV_ATTR_QUERY_TIMEOUT => 1  ) );
              $stmt->execute($row);
            }catch(Exception  $e){
                print_r($e->getMessage());
            }

        }
    }

}


