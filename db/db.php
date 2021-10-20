<?php

 $CONNECTIONSTRING = 'mysql:host=localhost;dbname=id16240844_thethester';
 $DBUSER = 'id16240844_thethester_base';
 $DBPASSWORD = 'P@ralelepiped0';
 $APIKEY = 'e19055b167dd976ae6a93174d3f3a709d5c43043';
 $MAILUSER = 'paulo_sergio_duarte@hotmail.com';
 $MAILPWD = 'Paulsccp';


function OpenDB(){
    global $CONNECTIONSTRING, $DBUSER, $DBPASSWORD;
    try{
        $pdo = new PDO($CONNECTIONSTRING, $DBUSER, $DBPASSWORD, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;

    }catch(PDOException $e){
        echo $e;
        return null;
    }

}

function ValidaAPI($key){
    global $APIKEY;
    if(strcmp($key, $APIKEY) == 0){
        return true;
    }else{
        return false;
    }

}

?>