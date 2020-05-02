<?php

include "curl.php";

$curl = new Curl("https://icanhazip.com","GET");
$response = $curl->send();

print_r("\n");
print_r($response["status_code"]);
print_r("\n");
print_r($response["headers"]);
print_r("\n");
print_r($response["info"]);
print_r("\n");
print_r($response["content"]);
print_r("\n");

?>