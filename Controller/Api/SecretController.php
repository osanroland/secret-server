<?php

class SecretController extends BaseController
{
    private $version=CURRENT_VERSION;
    private $path='secret';
    public function processRequest($uri)
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $uri=$this->getUriSegments($uri);
        
        if($uri[1] !== $this->version)
        {
            $strErrorDesc="Api version is not correct! The current version is: ". $this->version;
            $strErrorHeader = 'HTTP/1.1 404 Not Found';
        }
        elseif($uri[2] !== $this->path)
        {
            $strErrorDesc="URL path is incorrect!";
            $strErrorHeader = 'HTTP/1.1 404 Not Found';
        }
        elseif (strtoupper($requestMethod) == 'GET') {
            try {
                $secretGateway = new SecretGateway();
                $hash=$this->getUriSegments($uri)[3];
                $foundSecret = $secretGateway->getSecret(strval($hash));
                if(is_array($foundSecret)){
                    $responseData = json_encode($foundSecret);
                    $secretGateway->updateRemainingViews(strval($hash),$foundSecret[0]['remainingViews']);
                }else{
                    throw new Error($foundSecret);
                }
                
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage();
                $strErrorHeader = 'HTTP/1.1 404 Not Found';
            }
        } else if(strtoupper($requestMethod) == 'POST'){
            try {
                $secretGateway = new SecretGateway();
                $input = (array) json_decode(file_get_contents('php://input'), TRUE);
                if (! $this->validatesecret($input))
                {
                    throw new Error('The post body format is not correct! Please heck the documentation!');
                }else
                {
                    $responseData=$secretGateway->createSecret($input);
                    $responseData=json_encode($responseData);
                    return $responseData;
                }
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage();
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

    public function validateSecret($input)
    {
        (isset($input['secret'])) ? $secret=$input['secret'] : null;
        (isset($input['expireAfterViews'])) ? $expireAfterViews=$input['expireAfterViews'] : null;
        (isset($input['expireAfter'])) ? $expireAfter=$input['expireAfter'] : null;

        if(!isset($secret) || !is_string($secret) || $expireAfterViews <= 0 || !isset($expireAfterViews) || !isset($expireAfter)) return false;

        return true;
    }
}