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

        if( $res[0]['remaining_views'] !== 0 && ($res[0]['expires_at'] > $now || $res[0]['expires_at'] == null ))
        {
            $validSecret[] = array('hash'=> $res[0]['hash'], 'secretText'=> $res[0]['secret_text'], 'createdAt'=> $res[0]['created_at'], 'expiresAt'=> $res[0]['expires_at'],'remainingViews'=>$res[0]['remaining_views']-1);
        }else
        {
            return "Secret is no longer available! ";
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
        ($remainingMinutes == 0) ? $expiresAt=null : $expiresAt=$now->modify("+". $remainingMinutes ." minutes")->format('Y-m-d H:i:s');
        $remainingViews=$input['expireAfterViews'];
        array_push($queryParams,$hash,$secretText,$createdAt,$expiresAt,$remainingViews);
        

        $query = "INSERT INTO secrets (hash, secret_text, created_at, expires_at, remaining_views) VALUES (?, ?, ?, ?, ?)";
        $res = $this->insert($query, $queryParams);

        //error_log(print_r($res, TRUE)); 
        if($res==1){
            $createdSecret[] = array('hash'=> $hash, 'secretText'=> $secretText, 'createdAt'=> $createdAt, 'expiresAt'=> $expiresAt,'remainingViews'=>$remainingViews);
        }
        return $createdSecret;
    }

    public function updateRemainingViews($hash, $remainingViews)
    {
        $queryParams=[];
        array_push($queryParams,$remainingViews,$hash);
        
        $query = "UPDATE secrets SET remaining_views=? WHERE hash=?";
        $res = $this->update($query, $queryParams);

        return $res;
    }
}