<?php
function pi_log($message) {
    error_log($message . "\n", 3, '/var/log/lighttpd/pihole_php.log');
}
function die_and_log($message) {
    pi_log($message);
    die($message);
}
if(!isset($_POST['domain'], $_POST['list'], $_POST['token'])) {
    die_and_log("Missing POST variables");
}
$AUTHORIZED_HOSTNAMES = [
    $_SERVER['SERVER_ADDR'],
    'pi.hole'
];
// Check CORS
$CORS_ALLOW_ORIGIN = false;
if(in_array($_SERVER['HTTP_ORIGIN'], $AUTHORIZED_HOSTNAMES)) {
    $CORS_ALLOW_ORIGIN = $_SERVER['HTTP_ORIGIN'];
} else if(in_array($_SERVER['HTTP_HOST'], $AUTHORIZED_HOSTNAMES)) {
    $CORS_ALLOW_ORIGIN = $_SERVER['HTTP_HOST'];
}
if (!$CORS_ALLOW_ORIGIN)
    die_and_log("Failed CORS");
header("Access-Control-Allow-Origin: $CORS_ALLOW_ORIGIN");
session_start();
// Check CSRF token
if(!hash_equals($_SESSION['token'], $_POST['token']))
    die("Wrong token");

switch($_POST['list']) {
    case "white":
        exec("sudo pihole -w -q -d ${_POST['domain']}");
        break;
    case "black":
        exec("sudo pihole -b -q -d ${_POST['domain']}");
        break;
}
