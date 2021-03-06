<?php

namespace stphp;

require_once __DIR__ . '/config/AutoLoad.class.php';

/**
 * Description of STPHP
 *
 * @author thiago
 */
class STPHP {
  
  const VERSION = "0.0.1";
  
  /**
   *
   * @var stphp\http\HttpRequest
   */
  private $request;
  /**
   *
   * @var stphp\http\HttpResponse
   */
  private $response;
  
  function __construct() {

    $request = new \stphp\rest\Request();
    $this->request = $request->getRequest();
    $this->response = $request->getResponse();
    
  }

  
  public static function registerAutoload(){
    \stphp\config\AutoLoad::registerAutoloader();
  }
  
  public static function registerExtensions(){
    $extensions = array('.class.php', '.php');
    \stphp\config\AutoLoad::setExtensions($extensions);
  }

  /**
   * Find a way to improve the method.
   * 
   */
  public function handle(){
    
    $full_url = filter_input(INPUT_SERVER, "REQUEST_URI");
    $full_url = str_replace("index.php", "", $full_url);
    $namespace  = "controller";
    $class = "View";
    $method = "notFound";

    $parts_url = explode("?", $full_url);

    if (isset($parts_url[1])) {
        $parts_url = explode("/", $parts_url[1]);    
    }
    foreach ($parts_url as $part){
      $path_invoke = explode(".", $part);
      
      if (count($path_invoke) == 2) {

        $class = ucfirst($path_invoke[0]);
        $method_splited = explode("&", $path_invoke[1]);
        $method = strtolower($method_splited[0]);
        
      } else {
          $namespace = "view";
      }
      
    }

    $full_name = "app\\" . $namespace.  "\\" . $class;
    $this->invoke($full_name, $method);

  }
  
  public function invoke($path, $method){

    $rc = new \ReflectionClass($path);
    $obj = $rc->newInstance();

    if (!is_null( $method ) && method_exists($obj, $method) ) {
      $data = call_user_func(array($obj, $method), $this->response);
    }
    
    $this->sendData($data);
    
  }
 
  private function sendData(\stphp\http\HttpResponse $response) {
    $response->output();
  }
  
}
