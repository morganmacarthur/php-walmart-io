<?php

// You need the phpseclib folder extracted into your folder so that these includes work
include_once('./Crypt/RSA.php');
include_once('./Math/BigInteger.php');

$rsa = new Crypt_RSA();

// This is the example URL given on Walmart IO
$url = 'https://developer.api.walmart.com/api-proxy/service/affil/product/v2/taxonomy';

$privatekey = '<YOUR WALMART IO PRIVATE KEY GOES HERE>';
$consumerid = '<YOUR WALMART IO CONSUMER ID GOES HERE>';
$keyversion = '<YOUR WALMART IO KEY VERSION GOES HERE>';

// The timestamp needs to be an integer and lasts a minute or so
$timestamp = round(microtime(true) * 1000); //microtime();

// This is what you need to encrypt for Walmart to match and confirm the query is from you
$message = $consumerid . "\n" . $timestamp . "\n" . $keyversion . "\n";
$decodedPrivateKey = base64_decode($privatekey);

// All the encryption stuff happens here with phpseclib
$rsa->setPrivateKeyFormat(CRYPT_RSA_PRIVATE_FORMAT_PKCS8);
$rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_PKCS8);
$rsa->loadKey($decodedPrivateKey, CRYPT_RSA_PRIVATE_FORMAT_PKCS8);
$rsa->setHash('sha256');
$rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
$signature = base64_encode($rsa->sign($message));

// These headers are required for the lookup to succeed
$headers = array(
"WM_SEC.KEY_VERSION: {$keyversion}",
"WM_CONSUMER.ID: {$consumerid}",
"WM_CONSUMER.INTIMESTAMP: {$timestamp}",
"WM_SEC.AUTH_SIGNATURE: {$signature}",
"Accept: application/json"
);

// We use PHP cURL for the https connection
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

// Now you can do as you wish with your $response
echo $response;