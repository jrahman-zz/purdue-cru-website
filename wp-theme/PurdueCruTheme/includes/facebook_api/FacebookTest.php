<?php

require_once "facebook.php";

$app_id = "414221521986994";
$app_secret = "63c4bd70a0ea48e689f41e3b6ff9a4d1";

$facebookConfig = array();
$facebookConfig['appId'] = $app_id;
$facebookConfig['secret'] = $app_secret;
$facebookClient = new Facebook($facebookConfig);


$app_token_url = "https://graph.facebook.com/oauth/access_token?"
        . "client_id=" . $app_id
        . "&client_secret=" . $app_secret 
        . "&grant_type=client_credentials";

$response = file_get_contents($app_token_url);
$params = null;
parse_str($response, $params);

echo("This app's access token is: " . $params['access_token'] . "\n");


try {
    $user_profile = $facebookClient->api('/19914187568','GET', array('access_token' => $params['access_token']));
    echo "Name: " . $user_profile['name'];
} catch (Exception $e) {
    print_r($e);
}

?>
