<?php

class SecretController extends BaseController
{
    protected $secretGateway;

    public function __construct()
    {
        $this->secretGateway = new SecretGateway();

    }

    public function processRequest($uri)
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
 
        if (strtoupper($requestMethod) == 'GET') {
            try {

                $hash=$this->getUriSegments($uri)[3];
                //error_log(print_r(($hash), TRUE)); 
                $foundSecret = $this->secretGateway->getSecret(strval($hash));
                $responseData = json_encode($foundSecret);
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else if(strtoupper($requestMethod) == 'POST'){
            try {

                $input = (array) json_decode(file_get_contents('php://input'), TRUE);
                // if (! $this->validatePerson($input)) {
                //     return $this->unprocessableEntityResponse();
                // }
                
                $responseData=$this->secretGateway->createSecret($input);
                //error_log(print_r(json_encode($responseData), TRUE)); 
                return json_encode($responseData);
                
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        }
         else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }
 
        // send output
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }
}