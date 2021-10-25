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
		case "ASSOCIACAO":
			Associacao($obj);
			break;
		case "EDIT":
			Edit($obj);
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
			
			// $stmt = $pdo->prepare('SELECT * FROM LOGIN WHERE email = :email AND senha = :senha');
			$stmt = $pdo->prepare('SELECT L.* FROM LOGIN L LEFT JOIN ASSOCIACAO A ON L.cod_assoc = A.cod WHERE L.fl_ativo = 1 AND A.fl_ativo = 1 AND L.email = :email AND L.senha = :senha AND A.alias = :alias');
			$stmt->execute(array(
			':email' => $obj->email, 
            ':senha' => sha1($obj->senha),
			':alias' => strtolower($obj->alias)
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
					
					$stmt = $pdo->prepare("SELECT A.*, L.nome, L.imagem FROM AUTENTICACAO_AUX A LEFT JOIN LOGIN L ON L.id = A.id_login WHERE A.id_login = :id_login and A.token = :token AND NOW() < A.dta_expiracao");
					
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
						
						echo json_encode(['sucesso' => true , 'mensagem' => 'Usuário autenticado com sucesso.', 'id' => $_SESSION['id_login'], 'token' => $_SESSION['token_autenticacao'], 'nome' => $row['nome'], 'imagem' => $row['imagem']]);
						//echo json_encode(['sucesso' => true , 'mensagem' => 'Usuário autenticado com sucesso.', 'id' => $_SESSION['id_login'], 'token' => $_SESSION['token_autenticacao']]);
					}else{
						echo json_encode(['sucesso' => false , 'mensagem' => 'Usuário não autenticado']);
						die();
					}
				}else{
					echo json_encode(['sucesso' => false , 'mensagem' => 'Usuário não autenticado']);
					die();
				}
			}else{
				$stmt = $pdo->prepare("SELECT A.*, L.nome, L.imagem FROM AUTENTICACAO_AUX A LEFT JOIN LOGIN L ON L.id = A.id_login WHERE A.token = :token AND NOW() < A.dta_expiracao");
				$stmt->execute(array(
				':token' => $obj->token
				));

				if($row = $stmt->fetch()){
					session_start();
					$_SESSION['id'] = session_id();
					$_SESSION['id_login'] =	$row['id_login'];
					$_SESSION['token_autenticacao'] = sha1("snorlax" . gmdate('Y-m-d h:i:s \G\M\T'));
					$_SESSION['nome_login'] = $row['nome'];

					$stmt = $pdo->prepare("DELETE FROM AUTENTICACAO_AUX WHERE id_login = :id_login;");
					$stmt->execute(array(
					':id_login' => $_SESSION['id_login']
					));
					
					$stmt = $pdo->prepare("INSERT INTO AUTENTICACAO_AUX (id_login, dta_expiracao, token) VALUES (:id_login, date_add(now(), interval 15 MINUTE), :token);");
					$stmt->execute(array(
					':id_login' => $_SESSION['id_login'],
					':token' => $_SESSION['token_autenticacao']
					));
					
					echo json_encode(['sucesso' => true , 'mensagem' => 'Usuário autenticado com sucesso.', 'id' => $_SESSION['id_login'], 'token' => $_SESSION['token_autenticacao'], 'nome' => $row['nome'], 'imagem' => $row['imagem']]);
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

function Associacao($obj){
	try{
		$pdo = OpenDB();
		
		if($pdo){
			
			$stmt = $pdo->prepare('SELECT * FROM ASSOCIACAO WHERE alias = :alias AND fl_ativo = 1');
			$stmt->execute(array(
				':alias' => strtolower($obj->alias)
			));
			
			if($row = $stmt->fetch()){
				$a = new stdClass();
				$a->cod = $row['cod'];
				$a->nome = $row['nome'];
				$a->alias = $row['alias'];
				$a->logo = $row['logo'];
				$a->fl_ativo = $row['fl_ativo'];
				$a->dta_inscricao = $row['dta_inscricao'];
				$a->dta_vencimento = $row['dta_vencimento'];
				
				echo json_encode(['sucesso' => true , 'dados' => $a]);
				
            }else{
				echo json_encode(['sucesso' => false , 'mensagem' => 'Associação inválida.']);
				die();
			}
		}
	
	}catch(Exception $e){
		echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);		
	}
}

function Edit($obj){
	try {
		
		$pdo = OpenDB();
		$pdo->beginTransaction();
		
		if($pdo){
			
			$stmt = $pdo->prepare('UPDATE LOGIN SET senha = :senha, nome = :nome, imagem = :imagem WHERE id = :id');
			$stmt->execute(array(
				':senha' => sha1($obj->senha),
				':nome' => $obj->nome, 
				':imagem' => $obj->imagem, 
				':id' => $obj->id
				));
			

			if($stmt->rowCount() > 0){
				$stmt = $pdo->prepare('INSERT INTO LOGIN_LOG (dt_Alteracao, id_login, observacao) VALUES (NOW(), :id, :mensagem)');
				$stmt->execute(array(
				':id' => $obj->id,
				':mensagem' => 'UPDATE / ' . $obj->useragent
				));
				$pdo->commit();
				echo json_encode(['sucesso' => true , 'mensagem' => 'Cadastro alterado com sucesso.', 'id' => $obj->id]);
				
			}else{
				echo json_encode(['sucesso' => false , 'mensagem' => 'erro ao alterar cadastro']);
				$pdo->rollBack();		
			}
		}
	
	}catch(Exception $e){
		echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);		
	}	
}

?>