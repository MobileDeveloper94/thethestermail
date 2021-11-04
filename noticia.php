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
		case "INCLUIR":
			Incluir($obj);
			break;	
		case "ALTERAR":
			Alterar($obj);
		  break;
		case "LISTAR":
			Listar($obj);
		  break;
		  case "LISTARHOME":
			ListarHome($obj);
		  break;
		
		default:
			echo json_encode(['sucesso' => false , 'mensagem' => 'Parametro action deve ser alimentado.']);
			die();
	  }
}
catch(Exception $e){
	echo $e;
}


function Incluir($obj){
	try {
		$pdo = OpenDB();
		
		if($pdo){
			
			$stmt = $pdo->prepare('INSERT INTO NOTICIA (titulo, texto, imagem, link, dta_noticia, fl_home, fl_ativo, fl_redes, id_login) VALUES (:titulo, :texto, :imagem, NOW(), :fl_home, :fl_ativo, :fl_redes, :id_login)');
			$stmt->execute(array(
			':titulo' => $obj->titulo,
			':texto' => $obj->texto, 
			':imagem' => $obj->imagem, 
			':link' => $obj->link, 
			':fl_home' => $obj->fl_home, 
			':fl_ativo' => $obj->fl_ativo, 
			':fl_redes' => $obj->fl_redes, 
			':id_login' => $obj->id_login
          	));
			
			if($idNoticia = $pdo->lastInsertId()){
				$stmt = $pdo->prepare("INSERT INTO NOTICIA_LOG (dt_Alteracao, id_login, id_noticia, observacao) VALUES (NOW(), :id_login, :id_noticia, :mensagem)");
				$stmt->execute(array(
				':id_login' => $obj->id_login,
				':id_noticia' => $idNoticia,
				':mensagem' => "INSERT / " . $obj->useragent
				));
				echo json_encode(['sucesso' => true , 'mensagem' => 'Notícia cadastrada com sucesso.', 'id' => $idNoticia]);

			}else{
				echo json_encode(['sucesso' => false , 'mensagem' => 'erro ao cadastrar noticia']);		
			}
		}

	}catch(Exception $e){
		echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);		
	}	
}

function Alterar($obj){
	try {
		$pdo = OpenDB();
		$pdo->beginTransaction();
		
		if($pdo){
			
			$stmt = $pdo->prepare('UPDATE NOTICIA SET titulo = :titulo, texto = :texto, imagem = :imagem, link = :link, fl_home = :fl_home, fl_ativo = :fl_ativo, fl_redes = :fl_redes, id_login = :id_login WHERE id = :id_noticia');
			$stmt->execute(array(
			':titulo' => $obj->titulo,
			':texto' => $obj->texto, 
			':imagem' => $obj->imagem, 
			':link' => $obj->link, 
			':fl_home' => $obj->fl_home, 
			':fl_ativo' => $obj->fl_ativo, 
			':fl_redes' => $obj->fl_redes, 
			':id_login' => $obj->id_login,
			':id_noticia' => $obj->id_noticia
          	));
			

			if($stmt->rowCount() > 0){
				$stmt = $pdo->prepare('INSERT INTO NOTICIA_LOG (dt_Alteracao, id_login, id_noticia, observacao) VALUES (NOW(), :id_login, :id_noticia, :mensagem)');
				$stmt->execute(array(
				':id_login' => $obj->id_login,
				':id_noticia' => $obj->id_noticia,
				':mensagem' => 'UPDATE / ' . $obj->useragent
				));
				$pdo->commit();
				echo json_encode(['sucesso' => true , 'mensagem' => 'Noticia alterada com sucesso.', 'id' => $obj->id_noticia]);
				
			}else{
				echo json_encode(['sucesso' => false , 'mensagem' => 'erro ao alterar noticia']);
				$pdo->rollBack();		
			}
		}

	}catch(Exception $e){
		echo json_encode(['sucesso' => false , 'mensagem' => $e->GetMessage()]);			
	}	
}

function Listar($obj){
	try {
		$pdo = OpenDB();

		//$query = 'SELECT * FROM NOTICIA WHERE (fl_ativo = :fl_ativo OR 0 = :fl_ativo) AND (id = :id_noticia OR 0 = :id_noticia) ORDER BY dta_noticia DESC';		
		$query = 'SELECT N.*, L.nome FROM NOTICIA N LEFT JOIN LOGIN L ON N.id_login = L.id WHERE (N.fl_ativo = :fl_ativo OR 0 = :fl_ativo) AND (N.id = :id_noticia OR 0 = :id_noticia) ORDER BY N.dta_noticia DESC';

		if($pdo){
			
			$stmt = $pdo->prepare($query);
			$stmt->execute(array(
				':fl_ativo' => $obj->fl_ativo,
				':id_noticia' => $obj->id_noticia
			));
						
			$res = new stdClass();
			$res->dados = array();
			$res->total = 0;
			$res->pagina = $obj->pagina;

			while($row = $stmt->fetch()){
				$a = new stdClass();
				$a->id = $row['id'];
				$a->titulo = $row['titulo'];
				$a->texto = $row['texto'];
				$a->imagem = $row['imagem'];
				$a->link = $row['link'];
				$a->dta_noticia = $row['dta_noticia'];
				$a->fl_home = $row['fl_home'];
				$a->fl_ativo = $row['fl_ativo'];
				$a->fl_redes = $row['fl_redes'];
				$a->id_login = $row['id_login'];
				$a->nome_login = $row['nome'];
				array_push($res->dados, $a);
				$res->total = $res->total + 1;
			}

			//paginação 10 em 10
			if($res->total > 10){
				$res->paginas = ceil($res->total / 10);
				$breaked = array_chunk($res->dados, 10);
				if($res->pagina <= $res->paginas){
					$res->dados = $breaked[$res->pagina - 1];
				}else{
					$res->dados = $breaked[$res->paginas - 1];
					$res->pagina = $res->paginas;
				}
			}else{
				$res->paginas = 1;
			}
			
			$pdo = null;

			echo json_encode(['sucesso' => true , 'dados' => $res]);
		}

	}catch(Exception $e){
		echo $e;
	}	
}

function ListarHome($obj){
	try {
		$pdo = OpenDB();

		$query = 'SELECT N.*, L.nome FROM NOTICIA N LEFT JOIN LOGIN L ON N.id_login = L.id WHERE fl_home = 1 ORDER BY N.dta_noticia DESC';

		if($pdo){
			
			$stmt = $pdo->prepare($query);
			$stmt->execute();
						
			$res = new stdClass();
			$res->dados = array();
			$res->total = 0;
			

			while($row = $stmt->fetch()){
				$a = new stdClass();
				$a->id = $row['id'];
				$a->titulo = $row['titulo'];
				$a->texto = $row['texto'];
				$a->imagem = $row['imagem'];
				$a->link = $row['link'];
				$a->dta_noticia = $row['dta_noticia'];
				$a->fl_home = $row['fl_home'];
				$a->fl_ativo = $row['fl_ativo'];
				$a->fl_redes = $row['fl_redes'];
				$a->id_login = $row['id_login'];
				$a->nome_login = $row['nome'];
				array_push($res->dados, $a);
				$res->total = $res->total + 1;
			}

			$pdo = null;

			echo json_encode(['sucesso' => true , 'dados' => $res]);
		}

	}catch(Exception $e){
		echo $e;
	}	
}

?>