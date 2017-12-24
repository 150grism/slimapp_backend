<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

//Add CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($req, $res, $next) {
  $response = $next($req, $res);
  return $response
          ->withHeader('Access-Control-Allow-Origin', '*')
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
          ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

//Get all users

$app->get('/api/users', function(Request $request, Response $response) {
  $sql = "SELECT * FROM users";
  
  try {
    //Get DB object
    $db = new db();
    //Connect
    $db = $db->connect();

    $stmt = $db->query($sql);
    $users = $stmt->fetchALL(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($users);

  } catch(PDOException $e) {
    echo '{"error": {"text": ' . $e->getMessage() . '}';
  }
});