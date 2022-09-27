<?php
require_once PROJECT_ROOT_PATH . "/Model/DatabaseConnector.php";

 
class SecretGateway extends DatabaseConnector
{

    public function getSecrets()
    {
        return $this->select("SELECT * FROM secrets");
    }

    public function getSecret($hash)
    {
        $queryParams=[];
        $validSecret=[];
        array_push($queryParams,$hash);
        $res = $this->select("SELECT * FROM secrets WHERE hash=?",$queryParams);
        error_log(print_r(($res), TRUE)); 
        $now = new DateTime();
        $now=$now->format('Y-m-d H:i:s');

        if( $res['remaining_views'] != 0 && $res['expires_at'] > $now){
            $validSecret[] = array('hash'=> $res['hash'], 'secretText'=> $res['secret_text'], 'createdAt'=> $res['created_at'], 'expiresAt'=> $res['expires_at'],'remainingViews'=>$res['remaining_views']-1);
            $this->updateRemainingViews($hash);
        }
        return $validSecret;
    }

    public function createSecret($input)
    {   
        $queryParams=[];
        $hash = bin2hex(random_bytes(20));
        $secretText=$input['secret'];
        $remainingMinutes=$input['expireAfter'];
        $now = new DateTime();
        $createdAt=$now->format('Y-m-d H:i:s');
        $expiresAt=$now->modify("+". $remainingMinutes ." minutes")->format('Y-m-d H:i:s');
        $remainingViews=$input['expireAfterViews'];;
        array_push($queryParams,$hash,$secretText,$createdAt,$expiresAt,$remainingViews);
        

        $query = "INSERT INTO secrets (hash, secret_text, created_at, expires_at, remaining_views) VALUES (?, ?, ?, ?, ?)";
        $res = $this->insert($query, $queryParams);

        //error_log(print_r($res, TRUE)); 
        if($res==1){
            $createdSecret[] = array('hash'=> $hash, 'secretText'=> $secretText, 'createdAt'=> $createdAt, 'expiresAt'=> $expiresAt,'remainingViews'=>$remainingViews);
        }
        return $createdSecret;
    }

    public function updateRemainingViews($hash){
        
    }
}