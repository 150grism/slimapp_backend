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

//Get usersavedbreeds
$app->get('/api/usersavedbreeds', function(Request $request, Response $response) {
  $sql = "SELECT * FROM usersavedbreeds";
  
  try {
    //Get DB object
    $db = new db();
    //Connect
    $db = $db->connect();

    $stmt = $db->query($sql);
    $usersavedbreeds = $stmt->fetchALL(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($usersavedbreeds);

  } catch(PDOException $e) {
    echo '{"error": {"text": ' . $e->getMessage() . '}';
  }
});

//Get saved breeds for a user
$app->get('/api/user/{id}', function(Request $request, Response $response) {
  $id = $request->getAttribute('id');
  $sql = "SELECT b.breed_name FROM usersavedbreeds AS ub INNER JOIN breeds AS b ON ub.breed_id = b.breed_id";
  
  try {
    //Get DB object
    $db = new db();
    //Connect
    $db = $db->connect();

    $stmt = $db->query($sql);
    $usersavedbreeds = $stmt->fetchALL(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($usersavedbreeds);

  } catch(PDOException $e) {
    echo '{"error": {"text": ' . $e->getMessage() . '}';
  }
});

//Save breed for a user
$app->post('/api/users/{id}/save', function(Request $request, Response $response) {
  $id = $request->getAttribute('id');
  $breed = $request->getParam('breed');
  $sql1 = "INSERT INTO breeds (breed_name) VALUES (:breed)";
  $sql2 = "INSERT INTO usersavedbreeds (user_id, breed_id) SELECT :id, breed_id FROM breeds WHERE breed_name=:breed";
  
  try {
    //Get DB object
    $db = new db();
    //Connect
    $db = $db->connect();

    //1
    $stmt = $db->prepare($sql1);
    
    // $stmt->bindParam(':id', $id);
    $stmt->bindParam(':breed', $breed);

    $stmt->execute();

    //2
    $stmt = $db->prepare($sql2);
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':breed', $breed);

    $stmt->execute();
    $db = null;

    echo '{"notice": {"text": "Breed added"}}';
  } catch(PDOException $e) {
    echo '{"error": {"text": ' . $e->getMessage() . '}}';
  }
});