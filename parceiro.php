<?php

header('Content-type: application/json');
header('Access-Control-Allow-Origin: https://hmlthethester.netlify.app');
header('Access-Control-Allow-Origin: https://thethester.com.br');
header("Access-Control-Allow-Origin: *");//test only
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: *');
require_once("db/db.php");


$userID = 1;

try{
	$json = $_POST["obj"];
	$obj = json_decode($json);

	if(!ValidaAPI($obj->key)){	
		echo json_encode(['sucesso' => false , 'mensagem' => 'Key inválida']);
		die();
	}
	
	switch ($obj->action) {
		case "SELECT":
			Select($obj);
			break;	
		case "INSERT":
			Insert($obj);
		  break;
		case "UPDATE":
			Update($obj);
		  break;
		case "DELETE":
		  Delete($obj);
		  break;
		  
		default:
			echo json_encode(['sucesso' => false , 'mensagem' => 'Parametro action deve ser alimentado.']);
			die();
	  }
}
catch(Exception $e){
	echo $e;
}


function Select($obj){
	try {
		$pdo = OpenDB();
		
		$query = 'SELECT * FROM PARCEIRO WHERE id = :id OR :id = 0';
		
		if($pdo){
			
			$stmt = $pdo->prepare($query);
			$stmt->execute(array(
			':id' => $obj->id
			));
						
			$res = array();

			while($row = $stmt->fetch()){
				$a = new stdClass();
				$a->id = $row['id'];
				$a->nome = $row['nome'];
				$a->cpf_cgc = $row['cpf_cgc'];
				$a->descricao = $row['descricao'];
				$a->telefone = $row['telefone'];
				$a->telefone2 = $row['telefone2'];
				$a->email = $row['email'];
				$a->site = $row['site'];
				$a->facebook = $row['facebook'];
				$a->instagram = $row['instagram'];
				$a->twitter = $row['twitter'];
				$a->linkedin = $row['linkedin'];
				$a->logo = $row['logo'];
				$a->banner = $row['banner'];
				$a->dt_inscricao = $row['dt_inscricao'];
				$a->dt_validade = $row['dt_validade'];
				$a->fl_inscricao = $row['fl_inscricao'];
				array_push($res, $a);

			}

			$pdo = null;

			echo json_encode(['sucesso' => true , 'dados' => $res]);
		}

	}catch(Exception $e){
		echo $e;
	}	
}

function Insert($obj){
	global $userID;

	try {
		$pdo = OpenDB();
		$pdo->beginTransaction();

		$query = 'INSERT INTO PARCEIRO (nome, cpf_cgc, descricao, telefone, telefone2, email, site, facebook, instagram, twitter, linkedin, logo, banner, dt_inscricao, dt_validade, fl_inscricao) VALUES (:nome, :cpf_cgc, :descricao, :telefone, :telefone2, :email, :site, :facebook, :instagram, :twitter, :linkedin, :logo, :banner, :dt_inscricao, :dt_validade, :fl_inscricao)';
		$stmt = $pdo->prepare($query);
		$stmt->execute(array(
		':nome' => $obj->nome,
		':cpf_cgc' => $obj->cpf_cgc, 
		':descricao' => $obj->descricao, 
		':telefone' => $obj->telefone, 
		':telefone2' => $obj->telefone2, 
		':email' => $obj->email, 
		':site' => $obj->site, 
		':facebook' => $obj->facebook, 
		':instagram' => $obj->instagram, 
		':twitter' => $obj->twitter, 
		':linkedin' => $obj->linkedin, 
		':logo' => $obj->logo, 
		':banner' => $obj->banner, 
		':dt_inscricao' => $obj->dt_inscricao, 
		':dt_validade' => $obj->dt_validade, 
		':fl_inscricao' => $obj->fl_inscricao
		));
		

		if($res = $pdo->lastInsertId()){
			$stmt = $pdo->prepare("INSERT INTO PARCEIRO_LOG (dt_alteracao, id_login, id_parceiro, observacao) VALUES (NOW(), :userid, :id_parceiro, 'INSERT')");
			$stmt->execute(array(':id_parceiro' => $res, ':userid' => $userID));
			echo json_encode(['sucesso' => true , 'mensagem' => 'Parceiro incluído com sucesso', 'dados' => $obj]);
			$pdo->commit();
		}
		

	}
	catch(Exception $e){
		echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);
		$pdo->rollback();
	}

}

function Update($obj){
	global $userID;

	try {
		$pdo = OpenDB();
		$pdo->beginTransaction();

		$stmt = $pdo->prepare('SELECT * FROM PARCEIRO WHERE id = :id');
		$stmt->execute(array(
		':id' => $obj->id
		));
		
		$old = array();

		while($row = $stmt->fetch()){
			$a = new stdClass();
			$a->id = $row['id'];
			$a->nome = $row['nome'];
			$a->cpf_cgc = $row['cpf_cgc'];
			$a->descricao = $row['descricao'];
			$a->telefone = $row['telefone'];
			$a->telefone2 = $row['telefone2'];
			$a->email = $row['email'];
			$a->site = $row['site'];
			$a->facebook = $row['facebook'];
			$a->instagram = $row['instagram'];
			$a->twitter = $row['twitter'];
			$a->linkedin = $row['linkedin'];
			$a->logo = $row['logo'];
			$a->banner = $row['banner'];
			$a->dt_inscricao = $row['dt_inscricao'];
			$a->dt_validade = $row['dt_validade'];
			$a->fl_inscricao = $row['fl_inscricao'];
			array_push($old, $a);

		}
		
		if($pdo){
			$query = 'UPDATE PARCEIRO SET nome = :nome, descricao = :descricao, telefone = :telefone, telefone2 = :telefone2, site = :site, facebook = :facebook, instagram = :instagram, twitter = :twitter, linkedin = :linkedin, logo = :logo, banner = :banner, dt_inscricao = :dt_inscricao, dt_validade = :dt_validade, fl_inscricao = :fl_inscricao WHERE id = :id';
			$stmt = $pdo->prepare($query);
			$stmt->execute(array(
			':id' => $obj->id,
			':nome' => $obj->nome,
			// ':cpf_cgc' => $obj->cpf_cgc, 
			':descricao' => $obj->descricao, 
			':telefone' => $obj->telefone, 
			':telefone2' => $obj->telefone2, 
			// ':email' => $obj->email, 
			':site' => $obj->site, 
			':facebook' => $obj->facebook, 
			':instagram' => $obj->instagram, 
			':twitter' => $obj->twitter, 
			':linkedin' => $obj->linkedin, 
			':logo' => $obj->logo, 
			':banner' => $obj->banner, 
			':dt_inscricao' => $obj->dt_inscricao, 
			':dt_validade' => $obj->dt_validade, 
			':fl_inscricao' => $obj->fl_inscricao
			));

			if($stmt->rowCount() > 0){

				$log = json_encode(['operacao' => 'UPDATE', 'antes' => $old, 'depois' => $obj]);

				$stmt = $pdo->prepare("INSERT INTO PARCEIRO_LOG (dt_alteracao, id_login, id_parceiro, observacao) VALUES (NOW(), :userid, :id_parceiro, :observacao)");
				$stmt->execute(array(':id_parceiro' => $obj->id, ':observacao' => $log, ':userid' => $userID));
				
				echo json_encode(['sucesso' => true , 'mensagem' => 'Parceiro alterado com sucesso', 'dados' => $obj]);
				$pdo->commit();
			}else{
				echo json_encode(['sucesso' => false , 'mensagem' => 'Ocorreu um erro ao alterar parceiro']);
				$pdo->rollback();

			}
		}
	}
	catch(Exception $e){
		echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);
		$pdo->rollback();
	}	
}

function Delete($obj){
	global $userID;

	try {
		$pdo = OpenDB();
		$pdo->beginTransaction();

		$stmt = $pdo->prepare("DELETE FROM PARCEIRO_LOG WHERE id_parceiro = :id_parceiro");
		$stmt->execute(array(':id_parceiro' => $obj->id));

		$stmt = $pdo->prepare('DELETE FROM PARCEIRO WHERE id = :id');
		$stmt->execute(array(':id' => $obj->id));

		echo json_encode(['sucesso' => true , 'mensagem' => 'Parceiro deletado com sucesso', 'dados' => $obj]);
		$pdo->commit();

	}catch(Exception $e){
		echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);
		$pdo->rollback();
	}	
}

?>