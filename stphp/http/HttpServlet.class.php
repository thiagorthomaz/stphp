<?php

namespace stphp\http;


/**
 * Description of HttpServlet
 *
 * @author thiago
 */
abstract class HttpServlet implements \stphp\http\HttpCommand {

  /**
   *
   * @var HttpRequest
   */
  protected $request;
  
  /**
   *
   * @var HttpResponse 
   */
  protected $response;
  
  public function __construct() {
    $request = new \stphp\http\HttpRequest();
    $this->executeRequest($request);
  }

  public function executeRequest(\stphp\http\HttpRequest $request) {

    $http_host = filter_input(INPUT_SERVER, "HTTP_HOST");
    $request_url = filter_input(INPUT_SERVER, "REQUEST_URI");
    $request->setHost($http_host);
    $request->setUrl($http_host . $request_url);
    $this->recoverGetData($request);
   
    $this->recoverPostData($request);
    $this->request = $request;
    
    return $this->getRequest();

  }
  
  public function executeResponse(\stphp\http\HttpResponse $response) {
    $this->response = $response;
    $response->output();
  }
  
  /**
   * 
   * @return HttpRequest
   */
  public function getRequest(){
    return $this->request;
  }
  
  /**
   * 
   * @return HttpResponse
   */
  public function getResponse(){
    return $this->response;
  }
  
  private function recoverGetData(\stphp\http\HttpRequest &$request){
    
    $string_parameters = filter_input(INPUT_SERVER, "QUERY_STRING");

    if (!empty($string_parameters) && strpos($string_parameters, "&")){
      $array_parameters = explode("&", $string_parameters);

      foreach ($array_parameters as $parameter){

        $param = explode("=", $parameter);
        $key = $param[0];
        if (!isset($param[1])) {
          continue;
        }
        $value = $param[1];

        $request->addParam($key,$value);

      }

    }
    
  }
  
  private function recoverPostData(\stphp\http\HttpRequest &$request){
    
    if ($request->isPost() || $request->isPut() || $request->isDelete()){
      $data = array();
      $json_to_array = json_decode(file_get_contents('php://input'), true);
      if (is_null($json_to_array)){
        $data[$request->getMethod()] = $_REQUEST;
      } else {
        $data[$request->getMethod()] = array_merge($_REQUEST, $json_to_array);
      }

      $request->setParams(array_pop($data));

    }
    
  }

}
