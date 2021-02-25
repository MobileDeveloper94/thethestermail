<?php

$_CONNECTIONSTRING = 'mysql:host=localhost;dbname=id16240844_thethester';
$_DBUSER = 'id16240844_thethester_base';
$_DBPASSWORD = 'P@ralelepiped0';

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

?>