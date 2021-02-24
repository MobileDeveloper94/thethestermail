<?php



$Nome			= $_POST["Nome"];	// Pega o valor do campo Nome
$EmailFrom		= $_POST["EmailFrom"]; // Pega o valor do campo Email do cliente
$EmailTo		= $_POST["EmailTo"];	// Pega o valor do campo Email que vai receber
$Mensagem		= $_POST["Mensagem"];	// Pega os valores do campo Mensagem
$Assunto 		= $_POST["Assunto"];	//assunto do email
$Alias			= $_POST["Alias"];		//alias do email, ex: The Thester


require_once("phpmailer/class.phpmailer.php");

define('GUSER', 'paulo_sergio_duarte@hotmail.com');	// <-- Insira aqui o seu GMail
define('GPWD', 'Paulsccp');		// <-- Insira aqui a senha do seu GMail

$msg = "Voce recebeu uma mensagem:\n";
$msg = $msg . "De: " . $Nome . "\n";  
$msg = $msg . "Email: " . $EmailFrom . "\n";  
$msg = $msg . "Mensagem: \n" . $Mensagem . "\n";  


function smtpmailer($para, $de, $de_nome, $assunto, $corpo) { 
	global $error;
	$mail = new PHPMailer();
	$mail->IsSMTP();		// Ativar SMTP
	$mail->SMTPDebug = 0;		// Debugar: 1 = erros e mensagens, 2 = mensagens apenas
	$mail->SMTPAuth = true;		// Autenticação ativada
	$mail->SMTPSecure = 'tls';	// SSL REQUERIDO pelo GMail
	$mail->Host = 'smtp-mail.outlook.com';	// SMTP utilizado
	$mail->Port = 587;  		// A porta 587 deverá estar aberta em seu servidor
	$mail->Username = GUSER;
	$mail->Password = GPWD;
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

// Insira abaixo o email que irá receber a mensagem, o email que irá enviar (o mesmo da variável GUSER), 
//o nome do email que envia a mensagem, o Assunto da mensagem e por último a variável com o corpo do email.

if(smtpmailer($EmailTo, GUSER, $Alias, $Assunto, $msg)){
	$res = ['sucesso' => true , 'mensagem' => $error];
	$success = true;
}else{
	$res = ['sucesso' => false , 'mensagem' => $error];
	$success = false;
}

$hoje = date('Y-m-d H:i:s');


try {
	$pdo = new PDO('mysql:host=localhost;dbname=id16240844_thethester', 'id16240844_thethester_base', 'P@ralelepiped0');
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
	$stmt = $pdo->prepare('INSERT INTO EMAIL_LOG (sucesso, mensagem, data, email_cliente, nome_cliente, mensagem_cliente) VALUES(:sucesso, :mensagem, :data, :email_cliente, :nome_cliente, :mensagem_cliente)');
	$stmt->execute(array(
	  ':sucesso' => $success,
	  ':mensagem' => $error,
	  ':data' => $hoje,
	  ':email_cliente' => $EmailTo,
	  ':nome_cliente' => $Nome,
	  ':mensagem_cliente' => $Mensagem
	));
  
	
  }catch(PDOException $e){
	echo $e;
}

header('Content-type: application/json'); 
echo json_encode($res);

?>