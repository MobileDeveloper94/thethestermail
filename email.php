<?php
header('Content-type: application/json');
header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: *');

$Nome			= $_POST["Nome"];		// Pega o valor do campo Nome
$EmailFrom		= $_POST["EmailFrom"]; 	// Pega o valor do campo Email do cliente
$EmailTo		= $_POST["EmailTo"];	// Pega o valor do campo Email que vai receber
$Mensagem		= $_POST["Mensagem"];	// Pega os valores do campo Mensagem
$Assunto 		= $_POST["Assunto"];	//assunto do email
$key			= $_POST["Key"];		//key em sha1 para liberar acesso à API


require_once("phpmailer/class.phpmailer.php");
require_once("db/db.php");

global $MAILUSER, $MAILPWD;

if(!ValidaAPI($key)){
	echo json_encode(['sucesso' => false , 'mensagem' => 'Key inválida']);
	die();
}

function smtpmailer($para, $assunto, $corpo) { 
	global $error;
	global $MAILUSER, $MAILPWD;
	$mail = new PHPMailer();
	$mail->IsSMTP();		// Ativar SMTP
	$mail->SMTPDebug = 0;		// Debugar: 1 = erros e mensagens, 2 = mensagens apenas
	$mail->SMTPAuth = true;		// Autenticação ativada
	$mail->SMTPSecure = 'tls';	// SSL REQUERIDO
	$mail->Host = 'smtp-mail.outlook.com';	// SMTP utilizado
	$mail->Port = 587;  		// A porta 587 deverá estar aberta em seu servidor
	$mail->Username = $MAILUSER;
	$mail->Password = $MAILPWD;
	$mail->SetFrom($MAILUSER, "The Thester Site");
	$mail->Subject = $assunto;
	$mail->MsgHTML($corpo);
	if(isset($_POST["Anexo"])){
		$mail->AddAttachment($_POST["Anexo"]);
	}
	
	$mail->AddAddress($para);
	if(!$mail->Send()) {
		$error = 'Mail error: '.$mail->ErrorInfo; 
		return false;
	} else {
		$error = 'Mensagem enviada!';
		return true;
	}
}



if(smtpmailer($EmailTo, $Assunto, $Mensagem)){
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
		':email_cliente' => $EmailTo,
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