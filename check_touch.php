<?php
include '/var/www/html/configure/defines.php';
include '/var/www/html/modules/error_logger.php';
$dbtblstatus=TABLESYSTEMSTATUS;

$touch_connected=0;//by default touchscreen does not connected
//full string - Bus 001 Device 004: ID 0eef:0005 D-WAV Scientific Co., Ltd
exec ('lsusb > /var/www/html/usblist.txt');
$textfile = file_get_contents('/var/www/html/usblist.txt');
if(strstr($textfile,'D-WAV Scientific Co., Ltd')==TRUE){
    $touch_connected=1;
}
else {
    $touch_connected=0;
}
//echo $textfile; - enable if you want to see file content
setconntodb();
$dbname=DATABASENAME;
$sql = "SHOW TABLES FROM ".$dbname;
$result = mysql_query($sql);
$rows=mysql_num_rows($result);
if (!$result) {
    //sql error - cannot show tables from database
    log_sql_error(51983,$sql,mysql_error());
    die("SQL problems");
}
$check_t="SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$dbtblstatus."' AND Column_Name = 'touchscreen_exist'";
$res=mysql_query($check_t);
if(!$res){
    //sql error - cannot select table and column from database
    log_sql_error(519992,$res,mysql_error());
    die("");
}
$get_val=mysql_fetch_row($res);
if($get_val){
    $sql_get="SELECT touchscreen_exist FROM ".$dbtblstatus." LIMIT 1";
    $res_get=mysql_query($sql_get);
    $get_row=mysql_fetch_row($res_get);
    $tsstatus=$get_row[0];
    if($tsstatus!=1){
        //in sql base set that there is no touch screen
        if($touch_connected==1){
            //but touch screen connected!
            log_information(6003, 'touch screen connected');
            $sql_set="UPDATE ".$dbtblstatus." SET touchscreen_exist=1";
            $res=mysql_query($sql_set);
            if(!$res){
                //51996 - Cannot update touchscreen status - thc connected
                log_sql_error(51996,$sql_set,mysql_error());
                die("");
            }
        }
    }
    else{
        //in sql base set that touch_screen exist
        if($touch_connected==0){
            //but touch screen does not connected!
            log_information(6004, 'touch screen not connected');
            $sql_set="UPDATE ".$dbtblstatus." SET touchscreen_exist=0";
            $res=mysql_query($sql_set);
            if(!$res){
                //51997 - Cannot update touchscreen status - thc NOT connected
                log_sql_error(51997,$sql_set,mysql_error());
                die("");
            }
        }
    }

}
else{
    //CREATE COLUMN touchscreen_exist
    $add_column="ALTER TABLE ".$dbtblstatus." ADD touchscreen_exist INT";
    $res=mysql_query($add_column);
    if(!$res){
        echo "ERROR ADD COLUMN".PHP_EOL;
        //error 51998 - cannot alter table
        log_sql_error(51998,$add_column,mysql_error());
        die("");
    }
    else{
        echo "COLUMN touchscreen_exist SUCCESSFULLY ADDED".PHP_EOL;
    }
}
die("");
?>