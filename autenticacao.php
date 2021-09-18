<?php

header('Content-type: application/json');
// header('Access-Control-Allow-Origin: https://hmlthethester.netlify.app');
// header('Access-Control-Allow-Origin: https://thethester.com.br');
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
			
			$stmt = $pdo->prepare('SELECT * FROM LOGIN WHERE email = :email AND senha = :senha');
			$stmt->execute(array(
			':email' => $obj->email, 
            ':senha' => sha1($obj->senha)
			));
			
			if($row = $stmt->fetch()){
				
				if(isset($_SESSION)){
					session_destroy();
				}
					
				session_start();
				$_SESSION['id'] = session_id();
				$_SESSION['id_login'] =	$row['id'];
				$_SESSION['token_autenticacao'] = sha1("snorlax" . gmdate('Y-m-d h:i:s \G\M\T'));
				
            }else{
				echo json_encode(['sucesso' => false , 'mensagem' => 'Usuário ou senha inválidos.']);
				die();
			}
			
            $stmt = $pdo->prepare("INSERT INTO LOGIN_LOG (dt_Alteracao, id_login, observacao) VALUES (NOW(), :id_login, :mensagem)");
			$stmt->execute(array(
			':id_login' => $_SESSION['id_login'],
			':mensagem' => "ACESSO / " . $obj->useragent
			));

			$stmt = $pdo->prepare("DELETE FROM AUTENTICACAO_AUX WHERE id_login = :id_login;");
			$stmt->execute(array(
			':id_login' => $_SESSION['id_login']
			));

			$stmt = $pdo->prepare("INSERT INTO AUTENTICACAO_AUX (id_login, dta_expiracao, token) VALUES (:id_login, date_add(now(), interval 15 MINUTE), :token);");
			$stmt->execute(array(
			':id_login' => $_SESSION['id_login'],
			':token' => $_SESSION['token_autenticacao']
			));

			echo json_encode(['sucesso' => true , 'mensagem' => 'Usuário autenticado com sucesso.', 'id' => $_SESSION['id_login'], 'token' => $_SESSION['token_autenticacao']]);
		}

	}catch(Exception $e){
		echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);		
	}	
}

function Logout($obj){
	try {
		$pdo = OpenDB();
		
		if($pdo){
			$stmt = $pdo->prepare("DELETE FROM AUTENTICACAO_AUX WHERE token = :token");
			$stmt->execute(array(
			':token' => $obj->token
			));
				
			if(isset($_SESSION)){
				session_destroy();
			}

			echo json_encode(['sucesso' => true , 'mensagem' => 'Logout efetuado com sucesso.']);
		}
	}catch(Exception $e){
		echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);
	}	
}

function Check($obj){
	try {
		$pdo = OpenDB();
		
		if($pdo){
			
			if(isset($_SESSION)){
				
				if($_SESSION['token_autenticacao'] = $obj->token){
					
					$stmt = $pdo->prepare("SELECT * FROM AUTENTICACAO_AUX WHERE id_login = :id_login and token = :token AND NOW() < dta_expiracao");
					
					$stmt->execute(array(
					':id_login' => $_SESSION['id_login'],
					':token' => $_SESSION['token_autenticacao']
					));
					
					if($row = $stmt->fetch()){
						
						$stmt = $pdo->prepare("DELETE FROM AUTENTICACAO_AUX WHERE id_login = :id_login;");
						$stmt->execute(array(
						':id_login' => $_SESSION['id_login']
						));
						
						$_SESSION['token_autenticacao'] = sha1("snorlax" . gmdate('Y-m-d h:i:s \G\M\T'));

						$stmt = $pdo->prepare("INSERT INTO AUTENTICACAO_AUX (id_login, dta_expiracao, token) VALUES (:id_login, date_add(now(), interval 15 MINUTE), :token);");
						$stmt->execute(array(
						':id_login' => $_SESSION['id_login'],
						':token' => $_SESSION['token_autenticacao']
						));		

						echo json_encode(['sucesso' => true , 'mensagem' => 'Usuário autenticado com sucesso.', 'id' => $_SESSION['id_login'], 'token' => $_SESSION['token_autenticacao']]);
					}else{
						echo json_encode(['sucesso' => false , 'mensagem' => 'Usuário não autenticado']);
						die();
					}
				}else{
					echo json_encode(['sucesso' => false , 'mensagem' => 'Usuário não autenticado']);
					die();
				}
			}else{
				$stmt = $pdo->prepare("SELECT * FROM AUTENTICACAO_AUX WHERE token = :token AND NOW() < dta_expiracao");
				$stmt->execute(array(
				':token' => $obj->token
				));

				if($row = $stmt->fetch()){
					session_start();
					$_SESSION['id'] = session_id();
					$_SESSION['id_login'] =	$row['id_login'];
					$_SESSION['token_autenticacao'] = sha1("snorlax" . gmdate('Y-m-d h:i:s \G\M\T'));

					$stmt = $pdo->prepare("DELETE FROM AUTENTICACAO_AUX WHERE id_login = :id_login;");
					$stmt->execute(array(
					':id_login' => $_SESSION['id_login']
					));
					
					$stmt = $pdo->prepare("INSERT INTO AUTENTICACAO_AUX (id_login, dta_expiracao, token) VALUES (:id_login, date_add(now(), interval 15 MINUTE), :token);");
					$stmt->execute(array(
					':id_login' => $_SESSION['id_login'],
					':token' => $_SESSION['token_autenticacao']
					));
					
					echo json_encode(['sucesso' => true , 'mensagem' => 'Usuário autenticado com sucesso.', 'id' => $_SESSION['id_login'], 'token' => $_SESSION['token_autenticacao']]);
				}else{
					echo json_encode(['sucesso' => false , 'mensagem' => 'Usuário não autenticado']);
					die();
				}
			}	
		}
	}catch(Exception $e){
		echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);		
	}
}


?>