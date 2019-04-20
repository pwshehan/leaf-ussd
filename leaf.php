<?php

ini_set('error_log', 'error.log');
error_reporting(E_ERROR);
ini_set('display_errors', 1);

include_once 'lib/ussd/UssdReceiver.php';
include_once 'lib/ussd/UssdSender.php';

define ("APP_NAME","appname");
define ("APP_ID","appid");
define("APP_PASSWORD","password");

define("USSD_SENDER_URL","https://api.ideamart.io/ussd/send");

$receiver = new MoUssdReceiver();
$receiverSessionId = $receiver->getSessionId();

session_id($receiverSessionId);
session_start();

$content = $receiver->getMessage();
$address = $receiver->getAddress();
$requestId = $receiver->getRequestID();
$sessionId = $receiver->getSessionId();
$ussdOperation = $receiver->getUssdOperation();

$menuOption = $_SESSION['menu-Opt'];
$mainMenu = "Welcome!!!\n\n1: Menu1\n2: Menu2\n9: Exit";

if ($ussdOperation == "mo-init") {
        $_SESSION['menu-Opt'] = "main";
        UssdSender($sessionId, $mainMenu, $address);

} else if ($ussdOperation == "mo-cont") {

    switch ($menuOption) {
        case "main":

        	if ($content == 1){
        		// Show menu 1
	            $_SESSION['menu-Opt'] = "toMain";
	            $msg = "-Menu1-\n\nThis is the first menu.\n\n9: Exit";
	            UssdSender($sessionId, $msg, $address);

        	}else if ($content == 2){
        		// Show menu 2
	            $_SESSION['menu-Opt'] = "toMain";
	            $msg = "-Menu2-\n\nThis is the second menu.\n\n9: Exit";
	            UssdSender($sessionId, $msg, $address);

        	}else if ($content == 9){
        		//Exit from the app
	            $_SESSION['menu-Opt'] = "exit";
	            UssdSender($sessionId, "", $address);
        	}else{
        		// Send again to the main menu
        		$_SESSION['menu-Opt'] = "main";
        		UssdSender($sessionId, $mainMenu, $address);
        	}
        	break;

        case "toMain":
        	// Send back to the main menu
        	$_SESSION['menu-Opt'] = "main";
        	UssdSender($sessionId, $mainMenu, $address);
        	break;
    }
}

function UssdSender($sessionId, $responseMessage, $address)
{
    $encoding = "440";
    $chargingAmount = "0";
    $version = "1.0";

    if (($_SESSION['menu-Opt'] == "exit")) {
        $ussdOperation = "mt-fin";
        if ($responseMessage == "") $responseMessage = "Thanks for using.";
        $_SESSION['menu-Opt'] = "main";
        session_destroy();

    } else {
        $ussdOperation = "mt-cont";
    }

    $sender = new MtUssdSender(USSD_SENDER_URL);
    $res = $sender->ussd(APP_ID, APP_PASSWORD, $version, $responseMessage, $sessionId, $ussdOperation, $address, $encoding, $chargingAmount);
    $response = json_decode($res, true);

    return $response;
}

?>