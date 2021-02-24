<!Doctype Html5>
<html>
<head>
<title>Teste de envio de e-mail</title>
</head>
<body>
<form action="email.php" method="post">
	
	<h3>Infos remetente</h3>
	<hr>
	<label for="EmailFrom">E-mail From:</label>
	<input type="text" name="EmailFrom" size="35" />

	<label for="Alias">Alias:</label>
	<input type="text" name="Alias" size="35" />
	
	<label for="Assunto">Assunto:</label>
	<input type="text" name="Assunto" size="35" />
	
	<h3>Infos destinatário</h3>
	<hr>
	<label for="EmailTo">E-mail To:</label>
	<input type="text" name="EmailFrom" size="35" />

	<label for="Nome">Nome:</label>
	<input type="text" name="Nome" size="35" />

	<label for="Mensagem">Mensagem:</label>
	<textarea name="Mensagem" rows="8" cols="40"></textarea>

	<input type="submit" name="Enviar" value="Enviar" />
</form>
</body>
</html>