<?php

header('Content-type: application/json');
header('Access-Control-Allow-Origin: https://hmlthethester.netlify.app');
header('Access-Control-Allow-Origin: https://thethester.com.br');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: *');

$Nome			= $_POST["Nome"];		// Pega o valor do campo Nome
$EmailFrom		= $_POST["EmailFrom"]; 	// Pega o valor do campo Email do cliente
$EmailTo		= $_POST["EmailTo"];	// Pega o valor do campo Email que vai receber
$Mensagem		= $_POST["Mensagem"];	// Pega os valores do campo Mensagem
$Assunto 		= $_POST["Assunto"];	//assunto do email
$Alias			= $_POST["Alias"];		//alias do email, ex: The Thester
$key			= $_POST["Key"];		//key em sha1 para liberar acesso à API


require_once("phpmailer/class.phpmailer.php");
require_once("db/db.php");

global $MAILUSER, $MAILPWD;

if(!ValidaAPI($key)){
	
	
	echo json_encode(['sucesso' => false , 'mensagem' => 'Key inválida']);
	die();
}

$msg = "Voce recebeu uma mensagem:\n";
$msg = $msg . "De: " . $Nome . "\n";  
$msg = $msg . "Email: " . $EmailFrom . "\n";  
$msg = $msg . "Mensagem: \n" . $Mensagem . "\n";  


function smtpmailer($para, $de, $de_nome, $assunto, $corpo) { 
	global $error;
	global $MAILUSER, $MAILPWD;
	$mail = new PHPMailer();
	$mail->IsSMTP();		// Ativar SMTP
	$mail->SMTPDebug = 0;		// Debugar: 1 = erros e mensagens, 2 = mensagens apenas
	$mail->SMTPAuth = true;		// Autenticação ativada
	$mail->SMTPSecure = 'tls';	// SSL REQUERIDO pelo GMail
	$mail->Host = 'smtp-mail.outlook.com';	// SMTP utilizado
	$mail->Port = 587;  		// A porta 587 deverá estar aberta em seu servidor
	$mail->Username = $MAILUSER;
	$mail->Password = $MAILPWD;
	$mail->SetFrom($de, $de_nome);
	$mail->Subject = $assunto;
	$mail->Body = $corpo;
	$mail->AddAddress($para);
	if(!$mail->Send()) {
		$error = 'Mail error: '.$mail->ErrorInfo; 
		return false;
	} else {
		$error = 'Mensagem enviada!';
		return true;
	}
}



if(smtpmailer($EmailTo, $MAILUSER, $Alias, $Assunto, $msg)){
	$res = ['sucesso' => true , 'mensagem' => $error];
	$success = true;
}else{
	$res = ['sucesso' => false , 'mensagem' => $error];
	$success = false;
}

try {
	$pdo = OpenDB();
  
	if($pdo){

		$stmt = $pdo->prepare('INSERT INTO EMAIL_LOG (sucesso, mensagem, data, email_cliente, nome_cliente, mensagem_cliente) VALUES(:sucesso, :mensagem, :data, :email_cliente, :nome_cliente, :mensagem_cliente)');
		$stmt->execute(array(
		':sucesso' => $success,
		':mensagem' => $error,
		':data' => date('Y-m-d H:i:s'),
		':email_cliente' => $EmailFrom,
		':nome_cliente' => $Nome,
		':mensagem_cliente' => $Mensagem
		));
	
		$pdo = null;
	}
	

}catch(Exception $e){
	echo $e;
}


echo json_encode($res);

?>