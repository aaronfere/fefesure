<?php
	require_once  'vendor/autoload.php';

	$credentials = parse_ini_file(".conection.ini");

	$db = new mysqli($credentials["host"],$credentials["user"],'',$credentials["name"]);
	$db->query("SET NAMES 'utf8'");


	header('Access-Control-Allow-Origin: *');
	header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
	header("Allow: GET, POST, OPTIONS, PUT, DELETE");
	$method = $_SERVER['REQUEST_METHOD'];
	if($method == "OPTIONS") {
		die();
	}
	$app = new \Slim\Slim();
	$app->get("/pruebas", function() use($app){
		echo "Hola gente aqui con Slim PHP";
	});

	//Método post para guardar cliente en la BD

	$app->post('/clientes', function() use($app, $db){
		$json = $app->request->post('json');
		$data = json_decode($json, true);
		if(!isset($data['nombre'])){
			$data['nombre']=null;
		}
		if(!isset($data['apellido'])){
			$data['apellido']=null;
		}
		if(!isset($data['dni'])){
			$data['dni']=null;
		}
		if(!isset($data['direccion'])){
			$data['direccion']=null;
		}
		if(!isset($data['cuentabancaria'])){
			$data['cuentabancaria']=null;
		}
		$query = "INSERT INTO cliente (nombre,apellido,dni,direccion,cuentabancaria) VALUES(".
		"'{$data['nombre']}',".
		"'{$data['apellido']}',".
		"'{$data['dni']}',".
		"'{$data['direccion']}',".
		"'{$data['cuentabancaria']}'".
		");";
		$insert = $db->query($query);
		$result = array(
			'status' => 'error',
			'code' => 404,
			'message' => 'Cliente NO se ha creado'
		);
		if($insert){
			$result = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'Cliente creado correctamente'
			);
		}
		echo json_encode($result);
	});

//Metodo para devolver el listado de clientes guardados

	$app->get('/clientes', function() use($db, $app){
		$sql = 'SELECT * FROM cliente ORDER BY id DESC;';
		$query = $db->query($sql);
		$clientes = array();
		while ($cliente = $query->fetch_assoc()) {
			$clientes[] = $cliente;
		}
		$result = array(
			'status' => 'success',
			'code' => 200,
			'data' => $clientes
		);
		echo json_encode($result);
	});

//Metodo para buscar un cliente

	$app->get('/cliente/:id', function($id) use($db, $app){
		$sql = 'SELECT * FROM cliente WHERE id = '.$id;
		$query = $db->query($sql);
		$result = array(
			'status' => 'error',
			'code' => 404,
			'message' => 'cliente no disponible'
		);
		if($query->num_rows == 1){
			$cliente = $query->fetch_assoc();
			$result = array(
				'status' => 'success',
				'code' => 200,
				'data' => $cliente
			);
		}
		echo json_encode($result);
    });
    
//Metodo para borrar cliente

	$app->get('/deleteCliente/:id', function($id) use($db, $app){
		$sql = 'DELETE FROM cliente WHERE id = '.$id;
		$query = $db->query($sql);
		if($query){
			$result = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'El cliente se ha eliminado correctamente!!'
			);
		}else{
			$result = array(
				'status' => 'error',
				'code' => 404,
				'message' => 'El cliente no se ha eliminado!!'
			);
		}
		echo json_encode($result);
	});
	
	//Metodo para actualizar el cliente
	
	$app->post('/updateCliente/:id', function($id) use($db, $app){
		$json = $app->request->post('json');
		$data = json_decode($json, true);
		$sql = "UPDATE cliente SET ".
		"nombre = '{$data["nombre"]}', ".
		"apellido = '{$data["apellido"]}', ".
		"direccion = '{$data["direccion"]}', ".
		"cuentabancaria = '{$data["cuentabancaria"]}' ";
		$sql .= "WHERE id = {$id}";
		$query = $db->query($sql);

		if($query){
			$result = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'El cliente se ha actualizado correctamente!!',
				'query' => $sql
			);
		}else{
			$result = array(
				'consulta' => $sql,
				'status' => 'error',
				'code' => 404,
				'message' => 'El cliente no se ha actualizado!!'
			);
		}
		echo json_encode($result);
	});
	
	//Metodo para subir imagen
	
	$app->post('/upload-file', function() use($db, $app){
		$result = array(
			'status' 	=> 'error',
			'code'		=> 404,
			'message' 	=> 'El archivo no ha podido subirse'
		);

		if(isset($_FILES['uploads'])){
			$piramideUploader = new PiramideUploader();
			$upload = $piramideUploader->upload('image', "uploads", "uploads", array('image/jpeg', 'image/png', 'image/gif'));
			$file = $piramideUploader->getInfoFile();
			$file_name = $file['complete_name'];

			if(isset($upload) && $upload["uploaded"] == false){
				$result = array(
					'status' 	=> 'error',
					'code'		=> 404,
					'message' 	=> 'El archivo no ha podido subirse'
				);
			}else{
				$result = array(
					'status' 	=> 'success',
					'code'		=> 200,
					'message' 	=> 'El archivo se ha subido',
					'filename'  => $file_name
				);
			}
		}

		echo json_encode($result);
	});

//Método post para guardar poliza en la BD ********************************************************************************************************************************

	$app->post('/polizas', function() use($app, $db){
		$json = $app->request->post('json');
		$data = json_decode($json, true);
		$fechainicio = date("d/m/Y");
		$fechafin = substr($fechainicio, 0, 6).(intval(substr($fechainicio, 6))+1);
		$query = "INSERT INTO poliza (cliente,tipo,modelo,matricula,conductor,direccion,subtipo,fechainicio,fechafin) VALUES(".
		"'{$data['cliente']}',".
		"'{$data['tipo']}',".
		"'{$data['modelo']}',".
		"'{$data['matricula']}',".
		"'{$data['conductor']}',".
		"'{$data['direccion']}',".
		"'{$data['subtipo']}',".
		"'{$fechainicio}',".
		"'{$fechafin}'".
		");";
		$insert = $db->query($query);
		$result = array(
			'status' => 'error',
			'code' => 404,
			'message' => 'Poliza NO se ha creado'
		);
		if($insert){
			$result = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'Poliza creada correctamente'
			);
		}
		echo json_encode($result);
	});


//Metodo para devolver el listado de polizas guardadas

	$app->get('/polizas', function() use($db, $app){
		$sql = 'SELECT * FROM poliza ORDER BY id DESC;';
		$query = $db->query($sql);
		$polizas = array();
		while ($poliza = $query->fetch_assoc()) {
			$polizas[] = $poliza;
		}
		$result = array(
			'status' => 'success',
			'code' => 200,
			'data' => $polizas
		);
		echo json_encode($result);
	});

//Metodo para buscar una poliza

	$app->get('/poliza/:id', function($id) use($db, $app){
		$sql = 'SELECT * FROM poliza WHERE id = '.$id;
		$query = $db->query($sql);
		$result = array(
			'status' => 'error',
			'code' => 404,
			'message' => 'poliza no disponible'
		);
		if($query->num_rows == 1){
			$poliza = $query->fetch_assoc();
			$result = array(
				'status' => 'success',
				'code' => 200,
				'data' => $poliza
			);
		}
		echo json_encode($result);
	});

//Metodo para borrar poliza

	$app->get('/deletePoliza/:id', function($id) use($db, $app){
		$sql = 'DELETE FROM poliza WHERE id = '.$id;
		$query = $db->query($sql);
		if($query){
			$result = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'El poliza se ha eliminado correctamente!!'
			);
		}else{
			$result = array(
				'status' => 'error',
				'code' => 404,
				'message' => 'El poliza no se ha eliminado!!'
			);
		}
		echo json_encode($result);
	});
	
	//Metodo para actualizar la poliza
	
	$app->post('/updatePoliza/:id', function($id) use($db, $app){
		$json = $app->request->post('json');
		$data = json_decode($json, true);
		$sql = "UPDATE poliza SET ".
		"cliente = '{$data["cliente"]}',".
		"tipo = '{$data["tipo"]}',".
		"modelo = '{$data["modelo"]}',".
		"matricula = '{$data["matricula"]}',".
		"conductor = '{$data["conductor"]}',".
		"direccion = '{$data["direccion"]}',".
		"subtipo = '{$data["subtipo"]}'";
		$sql .= "WHERE id = {$id}";
		$query = $db->query($sql);
		if($query){
			$result = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'La poliza se ha actualizado correctamente!!'
			);
		}else{
			$result = array(
				'status' => 'error',
				'code' => 404,
				'message' => 'La poliza no se ha actualizado!!',
				'query' => $sql
			);
		}
		echo json_encode($result);
	});

//Método post para guardar siniestro en la BD ********************************************************************************************************************************

$app->post('/siniestros', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);
	$query = "INSERT INTO siniestro (idpoliza, fechasiniestro, lugar, cp, provincia, descripcion) VALUES(".
	"'{$data['idpoliza']}',".
	"'{$data['fechasiniestro']}',".
	"'{$data['lugar']}',".
	"'{$data['cp']}',".
	"'{$data['provincia']}',".
	"'{$data['descripcion']}'".
	");";
	$insert = $db->query($query);
	$result = array(
		'status' => 'error',
		'code' => 404,
		'message' => 'Siniestro NO se ha creado'
	);
	if($insert){
		$result = array(
			'status' => 'success',
			'code' => 200,
			'message' => 'Siniestro creado correctamente'
		);
	}
	echo json_encode($result);
});


//Metodo para devolver el listado de siniestros guardadas

$app->get('/siniestros', function() use($db, $app){
	$sql = 'SELECT * FROM siniestro ORDER BY id DESC;';
	$query = $db->query($sql);
	$siniestros = array();
	while ($siniestro = $query->fetch_assoc()) {
		$siniestros[] = $siniestro;
	}
	$result = array(
		'status' => 'success',
		'code' => 200,
		'data' => $siniestros
	);
	echo json_encode($result);
});

//Metodo para buscar una siniestro

$app->get('/siniestro/:id', function($id) use($db, $app){
	$sql = 'SELECT * FROM siniestro WHERE id = '.$id;
	$query = $db->query($sql);
	$result = array(
		'status' => 'error',
		'code' => 404,
		'message' => 'siniestro no disponible'
	);
	if($query->num_rows == 1){
		$siniestro = $query->fetch_assoc();
		$result = array(
			'status' => 'success',
			'code' => 200,
			'data' => $siniestro
		);
	}
	echo json_encode($result);
});

//Metodo para borrar siniestro

$app->get('/deleteSiniestro/:id', function($id) use($db, $app){
	$sql = 'DELETE FROM siniestro WHERE id = '.$id;
	$query = $db->query($sql);
	if($query){
		$result = array(
			'status' => 'success',
			'code' => 200,
			'message' => 'El siniestro se ha eliminado correctamente!!'
		);
	}else{
		$result = array(
			'status' => 'error',
			'code' => 404,
			'message' => 'El siniestro no se ha eliminado!!'
		);
	}
	echo json_encode($result);
});

//Metodo para actualizar la siniestro

$app->post('/updateSiniestro/:id', function($id) use($db, $app){
	$json = $app->request->post('json');
	$data = json_decode($json, true);
	$sql = "UPDATE siniestro SET ".
	"idpoliza = '{$data["idpoliza"]}',".
	"fechasiniestro = '{$data["fechasiniestro"]}',".
	"lugar = '{$data["lugar"]}',".
	"cp = '{$data["cp"]}',".
	"provincia = '{$data["provincia"]}',".
	"descripcion = '{$data["descripcion"]}'";
	$sql .= "WHERE id = {$id}";
	$query = $db->query($sql);
	if($query){
		$result = array(
			'status' => 'success',
			'code' => 200,
			'message' => 'El siniestro se ha actualizado correctamente!!'
		);
	}else{
		$result = array(
			'status' => 'error',
			'code' => 404,
			'message' => 'El siniestro no se ha actualizado!!',
			'query' => $sql
		);
	}
	echo json_encode($result);
});

//Metodo para crear usuarios **************************************************************************************************
	$app->post('/usuarios', function() use($app, $db){
		$json = $app->request->post('json');
		$data = json_decode($json, true);

		$pass = password_hash($data['pass'], PASSWORD_DEFAULT);
		echo "pasword: ".$pass." ";
		$data['pass'] = $pass;
		$data['imagen']='resources/user.png';
		
		$query = "INSERT INTO usuarios (username,pass,rol,imagen) VALUES(".
		"'{$data['username']}',".
		"'{$data['pass']}',".
		"'{$data['rol']}',".
		"'{$data['imagen']}'".
		");";
		$insert = $db->query($query);
		$result = array(
			'status' => 'error',
			'code' => 404,
			'sql' => $query,
			'message' => 'usuario NO se ha creado'
		);
		if($insert){
			$result = array(
				'status' => 'success',
				'code' => 200,
				'sql' => $query,
				'message' => 'usuario creado correctamente'
			);
		}
		echo json_encode($result);
	});
	
	//Metodo para devolver el listado de usuarios guardados  
	
	$app->get('/usuarios', function() use($db, $app){
		$sql = 'SELECT * FROM usuarios;';
		$query = $db->query($sql);
		$usuarios = array();
		while ($usuario = $query->fetch_assoc()) {
			$usuarios[] = $usuario;
		}
		$result = array(
			'status' => 'success',
			'code' => 200,
			'data' => $usuarios
		);
		echo json_encode($result);
	});
	
	//Metodo para login
	
	$app->get('/usuario/:id/:pass', function($id,$pass) use($db, $app){
		$sql = 'SELECT * FROM usuarios WHERE username = "'.$id.'"';
		$query = $db->query($sql); 
		$result = array(
			'status' => 'error',
			'code' => 404,
			'message' => 'usuario no disponible'
		);
		if($query->num_rows == 1){
			$usuario = $query->fetch_assoc();
			$passbd = $usuario['pass'];

			if(password_verify($pass , $passbd)){
			$result = array(
				'status' => 'success',
				'code' => 200,
				'data' => $usuario
			);
			}else{
				$result = array(
					'status' => 'error',
					'code' => 404,
					'message' => 'contrasenya erronea'
				);
			}
		}
		echo json_encode($result);
	});

	//Metodo para buscar un usuario
	
	$app->get('/usuariom/:id', function($id) use($db, $app){
		$sql = 'SELECT * FROM usuarios WHERE username = "'.$id.'"';
		$query = $db->query($sql);
		$result = array(
			'status' => 'error',
			'code' => 404,
			'message' => 'usuario no disponible'
		);
		if($query->num_rows == 1){
			$usuario = $query->fetch_assoc();
			$result = array(
				'status' => 'success',
				'code' => 200,
				'data' => $usuario
			);
		}
		echo json_encode($result);
	});
	
	//Metodo para borrar usuario
	
	$app->get('/deleteUser/:id', function($id) use($db, $app){
		$sql = 'DELETE FROM usuarios WHERE username = "'.$id.'";';
		$query = $db->query($sql);
		if($query){
			$result = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'El usuario se ha eliminado correctamente!!'
			);
		}else{
			$result = array(
				'status' => 'error',
				'code' => 404,
				'message' => 'El usuario no se ha eliminado!!'
			);
		}
		echo json_encode($result);
	});

	//Metodo para actualizar el usuario //necesario nombre y apellido

	$app->post('/updateUser/:id', function($id) use($db, $app){
		$json = $app->request->post('json');
		$data = json_decode($json, true);
		
		$pass = password_hash($data['pass'], PASSWORD_DEFAULT);
		echo "pasword: ".$pass." ";
		$data['pass'] = $pass;

		$sql = "UPDATE usuarios SET ".
		"pass = '{$data["pass"]}' ";
		if(isset($data['imagen']) && $data['imagen']!=null){
			$sql .= ", imagen = '{$data["imagen"]}' ";
		}
		$sql .= "WHERE username = '".$id."'";
		$query = $db->query($sql);

		if($query){
			$result = array(
				'status' => 'success',
				'consulta' => $sql,
				'code' => 200,
				'message' => 'El usuario se ha actualizado correctamente!!'
			);
		}else{
			$result = array(
				'consulta' => $sql,
				'status' => 'error',
				'code' => 404,
				'message' => 'El usuario no se ha actualizado!!'
			);
		}
		echo json_encode($result);
	});

		$app->run();