<?php
    require 'config/header_head.html';
?>
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/user_clave_solicitud.css">
</head>
<body>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div id="login-container">
            <div id="login-title">
                <img src="img/svg/lefre02_letras_blancas.svg">
            </div>
            <div id="login-content">
                <div id="login-header">
                    <h2 class="text-center mb-3">Recuperación de Contraseña</h2>
                    <hr style="border:0;width: 96%;background-color:#C8C8C8;height:2px;margin:0px 0px 8px 0px;">
                    <p>Introduce el email que registraste para enviar a tú correo un enlace para cambiar la contraseña.</p>
                </div>
                <div id="login-body">
                    <form id="form-password" method="post" class="login-form" onsubmit="submit_recupera_link(event)">
                        <div class="form-group">
                            <input type="email" name="user_email" id="user_email" class="mb-3 form-control" placeholder="e-mail" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary form-control"><strong>Envíame correo</strong></button>
                        </div>
                    <form>
                    <p>Ten un poco de paciencia, te llegará en escasos minutos</p>
                </div>
            </div>
            <div id="container-msj-error" class='alert alert-danger' role='alert'></div>
        </div>
    </main>
</body>
</html>
<script type="application/javascript">
    function submit_recupera_link(event){
        event.preventDefault();
        spinner.removeAttribute('hidden');
        var formData = new FormData(document.getElementById('form-password'));
        formData.append("accion","clave-recuperar");

        fetch('api/ap_usuario.php',{method:'POST', body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            if (responseJson.response == 'success'){
                Swal.fire({icon:'success', title:'Correo enviado con éxito', showConfirmButton:false, timer:2500});
                //document.getElementById("form-password").reset();
                delete formData;
                // setTimeout(() => {window.location.replace(location.origin + "/wms/index.php")}, 3000);
            }else{
                Swal.fire({icon: responseJson.error_tpo,title: 'Correo no enviado',text: responseJson.error_msj});
            }
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'Correo no enviado'});
        });
    }
</script>