// I ran into a lot of issues that I managed to to somewhat figured out. My biggest issue was with my database at the beginning of the project. I set up my first database into the scotchbox directory and no matter what I did I could not get the data to populate outside of actually going into. After trial and error I decided to hardcode the data into labdb.sql and that still did not get the data into the api. I decided to create a whole new database called jedidb and that fixed my issue. I was getting a 500 error after that was the result of a syntax error in composer.json that got fixed after running `dump-autoload`. The 500 error resolved into a smaller issue route issue that I was able to figure out thanks to error logging. I'm at the point now in the project where I can clean up and actually figure out the real issues in my code and my next step would have been to create a apitest.php file.

<?php
namespace green\jedi;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require './vendor/autoload.php';

class App
{

   private $app;
   public function __construct($db) {

     $config['db']['host']   = 'localhost';
     $config['db']['user']   = 'root';
     $config['db']['pass']   = 'root';
     $config['db']['dbname'] = 'jedidb';

     $app = new \Slim\App(['settings' => $config]);

     $container = $app->getContainer();
     $container['db'] = $db;

     $container['logger'] = function($c) {
         $logger = new \Monolog\Logger('my_logger');
         $file_handler = new \Monolog\Handler\StreamHandler('./logs/app.log');
         $logger->pushHandler($file_handler);
         return $logger;
     };
     $app->get('/jedi', function (Request $request, Response $response) {
         $this->logger->addInfo("GET /jedi");
         $people = $this->db->query('SELECT * from jedi')->fetchAll();
         $jsonResponse = $response->withJson($people);
         return $jsonResponse;
     });
     $app->get('/jedi/{id}', function (Request $request, Response $response, array $args) {
         $id = $args['id'];

         $jedi = $this->db->query('SELECT * from jedi where id='.$id)->fetch();

         if($jedi){
           $response = $response->withJson($jedi);
         } else {
           $errorData = array('status' => 404, 'Jedi' => 'not found. Continue Order 66');
           $response = $response->withJson($errorData, 404);
         }
         return $response;
     });
     $app->put('/jedi/{id}', function (Request $request, Response $response, array $args) {
         $id = $args['id'];
         $this->logger->addInfo("PUT /jedi/".$name);

         // check that person exists
         $jedi = $this->db->query('SELECT * from jedi where id='.$id)->fetch();
         if(!$jedi){
           $errorData = array('status' => 404, 'message' => 'not found');
           $response = $response->withJson($errorData, 404);
           return $response;
         }
         // build query string
         $updateString = "UPDATE jedi SET ";
         $fields = $request->getParsedBody();
         $keysArray = array_keys($fields);
         $last_key = end($keysArray);
         foreach($fields as $field => $value) {
           $updateString = $updateString . "$field = '$value'";
           if ($field != $last_key) {
             // conditionally add a comma to avoid sql syntax problems
             $updateString = $updateString . ", ";
           }
         }
         $updateString = $updateString . " WHERE id = $id;";
         // execute query
         try {
           $this->db->exec($updateString);
         } catch (\PDOException $e) {
           $errorData = array('status' => 400, 'message' => 'Invalid data provided to update');
           return $response->withJson($errorData, 400);
         }
         // return updated record
         $person = $this->db->query('SELECT * from jedi where id='.$id)->fetch();
         $jsonResponse = $response->withJson($jedi);

         return $jsonResponse;
     });
     $app->delete('/jedi/{id}', function (Request $request, Response $response, array $args) {
       $id = $args['id'];
       $this->logger->addInfo("DELETE /jedi/".$id);
       $deleteSuccessful = $this->db->exec('DELETE FROM jedi where id='.$id);
       if($deleteSuccessful){
         $response = $response->withStatus(200);
       } else {
         $errorData = array('status' => 404, 'message' => 'not found');
         $response = $response->withJson($errorData, 404);
       }
       return $response;
     });

     $this->app = $app;
   }
   /**
       * Get an instance of the application.
       *
       * @return \Slim\App
       */
      public function get()
      {
          return $this->app;
      }
    }
