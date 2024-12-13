<?php
session_start();
session_unset(); // Quito todos los valores asociados a la sesión.
if(session_destroy()) // Destroying All Sessions
{
header("Location: index.php"); // Redirecting To Home Page
}
?>