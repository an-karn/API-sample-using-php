<?php
require_once('constants.php');
class Rest {
    protected $request;
    protected $serviceName;
    protected $param;

    public function __construct()
    {
        IF($_SERVER['REQUEST_METHOD']!== 'POST'){
            $this->throwError(REQUEST_METHOD_NOT_VALID, "No post method found");
        }
        $handler = fopen('php://input', 'r');
        $this->request = stream_get_contents($handler);
        $this->Validaterequest($this->request);
        
    }
    public function Validaterequest($request){
        echo $_SERVER['CONTENT_TYPE'];exit;
        if($_SERVER['CONTENT_TYPE']!== 'application/json'){
            $this->throwError(REQUEST_CONTENTTYPE_NOT_VALID, 'request content type is not valid');
        }
        $data = json_decode($this->request, true);

        if(!isset($data['name']) || $data['name'] == "") {
            $this->throwError(API_NAME_REQUIRED, "API name is required.");
        }
        $this->serviceName = $data['name'];

        if(!is_array($data['param'])) {
            $this->throwError(API_PARAM_REQUIRED, "API PARAM is required.");
        }
        $this->param = $data['param'];
    }

    public function executeapi(){

    }
    public function processapi(){
        try {
            $api = new API;
            $rMethod = new reflectionMethod('API', $this->serviceName);
            if(!method_exists($api, $this->serviceName)) {
                $this->throwError(API_DOST_NOT_EXIST, "API does not exist.");
            }
            $rMethod->invoke($api);
        } catch (Exception $e) {
            $this->throwError(API_DOST_NOT_EXIST, "API does not exist.");
        }
        
    }
    public function validateParameter($fieldname, $value, $datatype, $required= true){
        if($required == true && empty($value) == true) {
            $this->throwError(VALIDATE_PARAMETER_REQUIRED, $fieldname . " parameter is required.");
        }
        switch ($datatype) {
            case BOOLEAN:
                if(!is_bool($value)) {
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldname . '. It should be boolean.');
                }
                break;
            case INTEGER:
                if(!is_numeric($value)) {
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldname . '. It should be numeric.');
                }
                break;

            case STRING:
                if(!is_string($value)) {
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldname . '. It should be string.');
                }
                break;
            
            default:
                $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldname);
                break;
        }
    return $value;
    
    }
    public function throwError($code, $message){
        header("content-type: application/json");
        $error_msg = json_encode(['error' =>['status' =>$code, 'message' => $message]]);
        echo $error_msg; exit;

    }
    public function returnResponse($code, $data){
        header("content-type: application/json");
			$response = json_encode(['resonse' => ['status' => $code, "result" => $data]]);
			echo $response; exit;

    }
}
?>