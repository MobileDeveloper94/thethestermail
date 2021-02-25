<?php

$_CONNECTIONSTRING = 'mysql:host=localhost;dbname=id16240844_thethester';
$_DBUSER = 'id16240844_thethester_base';
$_DBPASSWORD = 'P@ralelepiped0';
$_APIKEY = 'e19055b167dd976ae6a93174d3f3a709d5c43043';
$_MAILUSER = 'paulo_sergio_duarte@hotmail.com';
$_MAILPWD = 'Paulsccp';

function OpenDB(){
    try{
        $pdo = new PDO($_CONNECTIONSTRING, $_DBUSER, $_DBPASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;

    }catch(PDOException $e){
        echo $e;
        return null;
    }

}

function ValidaAPI($key){

    if(strcmp($key, $_APIKEY) == 0){
        return true;
    }else{
        return false;
    }

}

?>