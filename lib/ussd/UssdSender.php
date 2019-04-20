<?php


class MtUssdSender{
    var $server;

    public function __construct($server){
        $this->server = $server;
    }

    public function ussd($applicationId, $password, $version, $responseMsg,
                         $sessionId, $ussdOperation, $destinationAddress, $encoding, $chargingAmount){
        if (is_array($destinationAddress)) {
            return $this->ussdMany($applicationId, $password, $version, $responseMsg,
                $sessionId, $ussdOperation, $destinationAddress, $encoding, $chargingAmount);

        } else if (is_string($destinationAddress) && trim($destinationAddress) != "") {
            return $this->ussdMany($applicationId, $password, $version, $responseMsg,
                $sessionId, $ussdOperation, $destinationAddress, $encoding, $chargingAmount);

        } else {
            return "address should a string or a array of strings";
        }
    }

    private function ussdMany($applicationId, $password, $version, $message,
                              $sessionId, $ussdOperation, $destinationAddress, $encoding, $chargingAmount){

        $arrayField = array(
            "applicationId" => $applicationId,
            "password" => $password,
            "message" => $message,
            "destinationAddress" => $destinationAddress,
            "sessionId" => $sessionId,
            "ussdOperation" => $ussdOperation,
            "encoding" => $encoding,
            "version" => $version,
            "chargingAmount" => $chargingAmount);

        $jsonObjectFields = json_encode($arrayField);
        return $this->sendRequest($jsonObjectFields);
    }

    private function sendRequest($jsonObjectFields){
        $ch = curl_init($this->server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonObjectFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);

        $echo = $this->handleResponse($res);
        return $echo;
    }


    private function handleResponse($resp){
        if ($resp == "") {
            return "Server URL is invalid";
        } else {
            return $resp;
        }
    }

}

class UssdException extends Exception{

    var $code;
    var $response;
    var $statusMessage;

    public function __construct($message, $code, $response = null){
        parent::__construct($message);
        $this->statusMessage = $message;
        $this->code = $code;
        $this->response = $response;
    }

    public function getStatusCode(){
        return $this->code;
    }

    public function getStatusMessage(){
        return $this->statusMessage;
    }

    public function getRawResponse(){
        return $this->response;
    }

}

?>