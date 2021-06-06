<?php

header('Content-type: application/json');
header('Access-Control-Allow-Origin: https://hmlthethester.netlify.app');
header('Access-Control-Allow-Origin: https://thethester.com.br');
header("Access-Control-Allow-Origin: *");//test only
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: *');
require_once("db/db.php");


try{
	$json = $_POST["obj"];
	$obj = json_decode($json);

	if(!ValidaAPI($obj->key)){	
		echo json_encode(['sucesso' => false , 'mensagem' => 'Key inválida']);
		die();
	}
	
	switch ($obj->action) {
		case "LOGIN":
			Login($obj);
			break;	
		case "LOGOUT":
			Logout($obj);
		  break;
		case "CHECK":
			Check($obj);
		  break;
		
		default:
			echo json_encode(['sucesso' => false , 'mensagem' => 'Parametro action deve ser alimentado.']);
			die();
	  }
}
catch(Exception $e){
	echo $e;
}


function Login($obj){
	try {
		$pdo = OpenDB();
		
		
        
		if($pdo){
			
			$stmt = $pdo->prepare('SELECT * FROM LOGIN WHERE login = :login AND senha = :senha');
			$stmt->execute(array(
			':login' => $obj->login, 
            ':senha' => sha1($obj->senha)
			));
			
			if($row = $stmt->fetch()){
                session_start();

                $_SESSION['id'] =	$row['id'];
                $_SESSION['id'] =	$row['login'];
                $_SESSION['id'] =	$row['senha'];
                $_SESSION['id'] =	$row['cod_assoc'];
                $_SESSION['id'] =	$row['dta_inscricao'];
                $_SESSION['id'] =	$row['dta_vencimento'];
                $_SESSION['id'] =	$row['fl_ativo'];
                $_SESSION['id'] =	$row['id_tipo_acesso'];

            }
			
            $stmt = $pdo->prepare("INSERT INTO AUTENTICACAO_LOG (dt_Alteracao, id_login, observacao) VALUES (NOW(), :id_login, 'Login de usuário efetuado'");
			$stmt->execute(array(
			':id_login' => $_SESSION['id']
			));

			echo json_encode(['sucesso' => true , 'mensagem' => 'Usuário autenticado com sucesso.', 'id' => $row['id']]);
		}

	}catch(Exception $e){
		echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);
		
	}	
}



?>