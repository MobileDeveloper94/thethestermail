<?php

header('Content-type: application/json');
header('Access-Control-Allow-Origin: https://hmlthethester.netlify.app');
header('Access-Control-Allow-Origin: https://thethester.com.br');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: *');

$Nome			= $_POST["Nome"];		
$Empresa		= $_POST["Empresa"]; 	
$Cargo			= $_POST["Cargo"];	
$Telefone		= $_POST["Telefone"];	
$Email 			= $_POST["Email"];	
$Id				= $_POST["Id"];		
$key			= $_POST["Key"];		//key em sha1 para liberar acesso à API


require_once("db/db.php");

if(!ValidaAPI($key)){
	echo json_encode(['sucesso' => false , 'mensagem' => 'Key inválida']);
	die();
}


try {
	$pdo = OpenDB();
  
	if($pdo){

		$stmt = $pdo->prepare('INSERT INTO PARTICIPANTE_MATERIAL (nome, empresa, cargo, telefone, email, id_material, dt_acesso) VALUES(:nome, :empresa, :cargo, :telefone, :email, :id_material, :dt_acesso)');
		$stmt->execute(array(
		':nome' => $Nome,
		':empresa' => $Empresa,
		':cargo' => $Cargo,
		':telefone' => $Telefone,
		':email' => $Email,
		':id_material' => $Id,
		':dt_acesso' => date('Y-m-d H:i:s')
		));

		if($res = $pdo->lastInsertId()){
			echo json_encode(['sucesso' => true , 'mensagem' => 'Participante incluído com sucesso']);
			$pdo->commit();
		}
	
		$pdo = null;
	}
	

}catch(Exception $e){
	echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);
	$pdo->rollback();
}




?>