<?php
    declare(strict_types=1);
    // namespace Packing;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\OAuth;
    use League\OAuth2\Client\Provider\Google;
    require_once '../vendor/autoload.php';

class Email {
  
    // Para manejo de errores o validaciones
    public  $error ; // Indica si se detectó un error o advertencia en algún método.
    public  $error_nro;      // Nro de Error. Si es una excepción de usuario se coloca 45000
    public  $error_msj;      // Mensaje de Error.
    public  $error_file;     // Archivo del error.
    public  $error_line;     // Linea del error.
    public  $error_tpo;      // Tipo de error (2 valores): "warning" o "error"


    function send_email($tpo_contenido, $destinatario,$token){
        $this->error = false;
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
     
        //Set the encryption mechanism to use:
        // - SMTPS (implicit TLS on port 465) or
        // - STARTTLS (explicit TLS on port 587)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        
        $mail->SMTPAuth = true;
        $mail->AuthType = 'XOAUTH2';
        
        $email = 'lefre.sistemas@gmail.com'; // the email used to register google app
        $clientId = '298356996491-ti4gsau2i9jcug0kosqo55ehoqcmt7p1.apps.googleusercontent.com';
        $clientSecret = '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';
        $refreshToken = '1//05OBRSZ2v4fQqCgYIARAAGAUSNwF-L9IrsjaJGcU5xmWhUpNJ2FEi_dV7qw_XJo_691phalURqkPB6iNmLX4t2mJnoIFLvENrG1c';
        
        // $email = 'jcfreitesbacalao@gmail.com'; // the email used to register google app
        // $clientId = '686028162650-bvmauqs9f86iju6ovif6v0bgbdpc4jpa.apps.googleusercontent.com';
        // $clientSecret = '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';
        //$refreshToken = '1//05V8TyGyiOGM1CgYIARAAGAUSNwF-L9IrYZ3_1_FGx1hFOHC7lsOrLy9DFQopStF9bqwfvHyVaGt-uH8N2OMxl_736Vdhy3h4eS8';
    
        //Create a new OAuth2 provider instance
        $provider = new Google(
            [
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
            ]
        );
        
        //Pass the OAuth provider instance to PHPMailer
        $mail->setOAuth(
            new OAuth(
                [
                    'provider' => $provider,
                    'clientId' => $clientId,
                    'clientSecret' => $clientSecret,
                    'refreshToken' => $refreshToken,
                    'userName' => $email,
                ]
            )
        );
        
        $mail->setFrom($email, 'Inversiones Lefre');
        $mail->addAddress($destinatario, 'lefre.sistemas@gmail.com');
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        if ($tpo_contenido == 'user-registro'){
            // Para validar usuario nuevo.
            // $mail->Subject = mb_convert_encoding('Validación de Usuario Lefre',"UTF-8");
            $mail->Subject = utf8_decode('Validación de Usuario Lefre');
            $mail->Body = $this->html_registro($token);
        }elseif ($tpo_contenido == 'user-clave'){
            // Para recuperar contraseñas.
            $mail->Subject = 'Recuperación de Contraseña Lefre';
            //$mail->Subject = utf8_decode('Recuperación de Contraseña Lefre');
            $mail->Body = $this->html_clave($token);
        }
        
        //send the message, check for errors
        if (!$mail->send()) {
            $this->error     = true;
            $this->error_msj = $mail->ErrorInfo;
        }    
    }

    private function html_registro($token){
        $enlace = $_SERVER['HTTP_ORIGIN'].'/wms/user-dashboard.php?token='.$token;
        $body   = '<!doctype html>';
        $body  .= '<html lang="es">';
        $body  .= '<meta charset="utf-8">';
        $body  .= '<body>';
        $body  .= '<div style="position: absolute;left: 50%;transform: translateX(-50%); font-family: Helvetica, Arial,\'Rockwell\', sans-serif;">';        
        $body  .= '<h1>Bienvenido a LEFRE</h1>';
        $body  .= '<p>Ya te falta poco</p>';
        $body  .= '<p>Para culminar tu registro dale clic al siguiente botón</p>';
        $body  .= '<a href="'.$enlace.'">';
        $body  .= '<button type="button" style="padding: 15px 30px;border-radius:30px;border:0;font-size:1.5rem;background-color:#0e7201;color:white;font-weight:600;width:300px;-webkit-box-shadow: -1px 10px 13px 4px rgba(50,50,50,0.51);box-shadow: -1px 10px 13px 4px rgba(50,50,50,0.51);position: absolute;left: 50%;transform: translateX(-50%);">Haz clic Aquí</button>';
        $body  .= '</a>';
        $body  .= '</div>';
        $body  .= '</body></html>';
        unset($token, $enlace);
        return ($body);
    }

    private function html_clave($token){
        $enlace = $_SERVER['HTTP_ORIGIN'].'/wms/user-clave-cambio.php?token='.$token;
        $body   = '<!doctype html>';
        $body  .= '<html lang="es">';
        $body  .= '<meta charset="utf-8">';
        $body  .= '<body>';
        $body  .= '<div style="position: absolute;left: 50%;transform: translateX(-50%); font-family: Helvetica, Arial,\'Rockwell\', sans-serif;">';
        $body  .= '<h1>Recuperación de Clave</h1>';
        $body  .= '<p>Has solicitado recuperar tu contraseña de inicio a nuestros sistemas.</p>';
        $body  .= '<p>Para generar una nueva contraseña haz click en el siguiente botón</p>';
        $body  .= '<a href="'.$enlace.'">';
        $body  .= '<button type="button" style="padding: 15px 30px;border-radius:30px;border:0;font-size:1.5rem;background-color:#0e7201;color:white;font-weight:600;width:300px;-webkit-box-shadow: -1px 10px 13px 4px rgba(50,50,50,0.51);box-shadow: -1px 10px 13px 4px rgba(50,50,50,0.51);position: absolute;left: 50%;transform: translateX(-50%);">Haz clic Aquí</button>';
        $body  .= '</a>';
        $body  .= '</div>';
        $body  .= '</body></html>';
        unset($token, $enlace);
        return ($body);
    }
}
?>
