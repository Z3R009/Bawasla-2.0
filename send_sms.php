<?php
require_once 'pear/pear/HTTP/Request2.php';
require_once 'pear/pear/Net/URL2.php';

// Retrieve the mobile number from the form or define it manually
$edit_mobile_number = isset($_POST['edit_mobile_number']) ? $_POST['edit_mobile_number'] : null;

if (!$edit_mobile_number) {
    die("Recipient phone number is required.");
}

// Replace this with your Infobip authorization token
$authToken = 'App f7b65e9af4d7cf393e4db95cb9ad3f83-9bc275df-2ee2-44cc-abeb-044d1f4c930d';

// Replace this with your desired message
$messageText = "Congratulations on sending your first message.\nGo ahead and check the delivery report in the next step.";

$request = new HTTP_Request2();
$request->setUrl('https://e5dxn2.api.infobip.com/sms/2/text/advanced');
$request->setMethod(HTTP_Request2::METHOD_POST);
$request->setConfig(array(
    'follow_redirects' => TRUE
));
$request->setHeader(array(
    'Authorization' => $authToken,
    'Content-Type' => 'application/json',
    'Accept' => 'application/json'
));

// Set the body of the request with the recipient's phone number and message
$request->setBody(json_encode(array(
    'messages' => array(
        array(
            'destinations' => array(
                array('to' => $edit_mobile_number)
            ),
            'from' => '447491163443', // Replace with your sender ID
            'text' => $messageText
        )
    )
)));

try {
    $response = $request->send();
    if ($response->getStatus() == 200) {
        echo $response->getBody();
    } else {
        echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
            $response->getReasonPhrase();
    }
} catch (HTTP_Request2_Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>