<?php
// if session status is none then start the session
if( session_status() == PHP_SESSION_NONE ) {
    session_start();  
}

if(!isset($_SESSION["username"])){
    http_response_code(401);
    echo json_encode("No autorizado");
    exit(); 
}
?>
