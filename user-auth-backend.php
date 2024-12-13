<?php
// if session status is none then start the session
if( session_status() == PHP_SESSION_NONE ) {
    session_start();  
}

if(!isset($_SESSION["username"])){
    die;
    exit(); 
}
?>
