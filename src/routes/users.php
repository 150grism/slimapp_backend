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

//Get saved breeds for a user
$app->get('/api/user/{id}/saved', function(Request $request, Response $response) {
  $id = $request->getAttribute('id');
  $sql = "SELECT b.breed_name FROM usersavedbreeds AS ub INNER JOIN breeds AS b ON ub.user_id = $id AND ub.breed_id = b.breed_id";
  
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

//Get saved pictures for a user
$app->get('/api/user/{id}/pictures/saved', function(Request $request, Response $response) {
  $id = $request->getAttribute('id');
  $sql = "SELECT up.picture_url FROM usersavedpictures AS up WHERE up.user_id = $id";
  
  try {
    //Get DB object
    $db = new db();
    //Connect
    $db = $db->connect();

    $stmt = $db->query($sql);
    $usersavedpictures = $stmt->fetchALL(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($usersavedpictures);

  } catch(PDOException $e) {
    echo '{"error": {"text": ' . $e->getMessage() . '}';
  }
});

//Save breed for a user
$app->post('/api/users/{id}/save', function(Request $request, Response $response) {
  $id = $request->getAttribute('id');
  $breed = $request->getParam('breed');
  $sql1 = "INSERT INTO breeds (breed_name) SELECT :breed WHERE NOT EXISTS (SELECT breed_name FROM breeds WHERE breed_name = :breed)";
  $sql2 = "INSERT INTO usersavedbreeds (user_id, breed_id) SELECT :id, b.breed_id FROM breeds AS b WHERE b.breed_name = :breed AND NOT EXISTS (SELECT breed_name FROM usersavedbreeds AS ub INNER JOIN breeds AS b ON ub.breed_id = b.breed_id WHERE b.breed_name = :breed AND ub.user_id = :id)";
  
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

    echo '{"notice": {"text": "Breed ' . $breed . ' added"}}' ;
  } catch(PDOException $e) {
    echo '{"error": {"text": ' . $e->getMessage() . '}}';
  }
});

//Get all photos for a breed
$app->get('/api/breeds/{breed}', function(Request $request, Response $response) {
  $breed = $request->getAttribute('breed');
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

//Save picture for user
$app->post('/api/users/{id}/picture/save', function(Request $request, Response $response) {
  $id = $request->getAttribute('id');
  $picture_url = $request->getParam('picture_url');
  $sql = "INSERT INTO usersavedpictures (user_id, picture_url) SELECT :id, :picture_url WHERE NOT EXISTS (SELECT * FROM usersavedpictures AS up WHERE up.user_id = :id AND up.picture_url = :picture_url)";
  
  try {
    //Get DB object
    $db = new db();
    //Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':picture_url', $picture_url);

    $stmt->execute();
    $db = null;

    echo '{"notice": {"text": "Picture added"}}' ;
  } catch(PDOException $e) {
    echo '{"error": {"text": ' . $e->getMessage() . '}}';
  }
});

//Sign up a user
$app->post('/api/users/signup', function(Request $request, Response $response) {
  $user = $request->getParam('user');
  $pass = $request->getParam('password');
  $sql = "INSERT INTO users (user_name, user_password) SELECT :user, :pass WHERE NOT EXISTS (SELECT * FROM users AS u WHERE u.user_name = :user AND u.user_password = :pass)";
  
  try {
    //Get DB object
    $db = new db();
    //Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':user', $user);
    $stmt->bindParam(':pass', $pass);

    $stmt->execute();
    $db = null;

    echo '{"notice": {"text": "User added"}}' ;
  } catch(PDOException $e) {
    echo '{"error": {"text": ' . $e->getMessage() . '}}';
  }
});

//Log in
$app->post('/api/users/login', function(Request $request, Response $response) {
  $user = $request->getParam('user');
  $pass = $request->getParam('password');
  $sql = "SELECT u.user_id FROM users AS u WHERE u.user_name = :user AND u.user_password = :pass";
  
  try {
    //Get DB object
    $db = new db();
    //Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':user', $user);
    $stmt->bindParam(':pass', $pass);

    $stmt->execute();

    $usersavedbreeds = $stmt->fetchALL(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($usersavedbreeds);

  } catch(PDOException $e) {
    echo '{"error": {"text": ' . $e->getMessage() . '}';
  }
});