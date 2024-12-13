<?php
    /* clase Fotos.php  

    1. Se manejaran varios tamaños de archivos para las fotos de productos.
       Estas son:
       a) Thumbnails: 100x100
       b) Pequeñas: 300x300
       c) Medianas: 600x600
       c) Grandes: 1000x1000
       
       Abreviaturas para los sufijos:  
       TH: Thumbnail
       SM: Pequeñas o small.
       MD: Medianas.
       LG: Grandes o Large.
       (Si se requieren más grandes usar XL o XXL).

    1. Las fotos de los productos tienen que tener el siguiente formato en la 
       carpeta origen: codigo-consecutivo.formato ejemplo: YYYY-YYYY-X-LEFRE-ZZ.png, donde:
       YYYY-YYYY: codigo del producto (no confundir con el ID)
       X: nro. consecutivo de foto.
       ZZ: Tamaño archivo (TH,SM,MD o LG)
    4. Se deben organizar los archivos de fotos de productos en 1 carpeta por cada tipo de tamaño.
       Paso 1: Ubicar archivo en carpeta origen.
       Paso 2: Si está en otro formato distinto a jpg hay que convertirlo.
       Paso 3: Cambiar el tamaño del archivo a la dimensiones esecificadas más adelante.
       Paso 4: Pasar el archivo dimensionado a su respectiva carpeta.
    6. Se manejaran las siguientes carpetas:
        ./biblioteca_fotos/temp
        ./biblioteca_fotos/fotos-th (thumbnail)
        ./biblioteca_fotos/fotos-sm (pequeñas)
        ./biblioteca_fotos/fotos-md (medianas)
        ./biblioteca_fotos/fotos-lg (grandes)
        ./biblioteca_fotos/marcas_logos

    7. Los tamaños de los archivos de logos de marcas puede ser cualquiera
       siempre y cuando la proporción se 3x2 (el ancho de la foto viene
       dado por la siguiente formula: ancho= altura / 2 * 3)    

*/
    // Aseguramos que cada parametro de cada función sea del mismo 
    // tipo definido. 
    declare(strict_types=1); 

class foto {
    /* Las fotos son cuadradas y estos valores indican el tamaño
       en pixeles de cada imagen*/
    public $size_thumb = 100 ; 
    public $size_small = 300 ; 
    public $size_medio = 600 ; 
    public $size_large = 1000 ; 

    public $url_biblioteca; // Ruta de la biblioteca de fotos.
    public $url_temp;    // URL carpeta Temporal
    public $url_thumb;   // URL carpeta Thumbnails
    public $url_small;   // URL carpeta imagenes pequeñas
    public $url_medio;   // URL carpeta imagenes medianas
    public $url_large;   // URL carpeta imagenes grandes
    public $url_marcas;  // URL carpeta imagenes de marcas y logos

    private $carpeta_biblioteca; // Ruta de la biblioteca de fotos.
    public  $carpeta_temp; // Carpeta Temporal
    private $carpeta_thumb; // Carpeta Thumbnails
    private $carpeta_small; // Carpeta imagenes pequeñas
    private $carpeta_medio; // Carpeta imagenes medianas
    private $carpeta_large; // Carpeta imagenes grandes

    /* Estas propiedades son los datos proporcionados por el array $_FILES
       cuando se sube una foto. Se agregan estas propiedades para validar 
       correctamente cada archivo subido.*/
    public $name;
    public $tmp_name;
    public $upload_error;

    /* Propiedades necesarias para convertir las imagenes al tamaño y formato 
       necesario para ser usado por el sistema */
    public $source_archivo;  // Nombre de la imagen a convertir tanto jpeg como webp 
    public $destino_archivo; // Nombre del archivo que será colocado en las carpetas de imagenes
    public $source_width;
    public $source_height;
    public $source_ext;
    

    // Para manejo de errores o validaciones
    public $error;      // Indica si se detectó un error o advertencia en algún método.
    public $error_nro;  // Nro de Error. Si es una excepción de usuario se coloca 45000
    public $error_msj;  // Mensaje de Error.
    public $error_file; // Archivo del error.
    public $error_line; // Linea del error.
    public $error_tpo;  // Tipo de error (2 valores): "warning" o "error"    

    
    /******************************************************************************
    * Constructor 
    *  Parametros:
    *  1. Id. Empresa: 
    *  1. Id. Producto:
    *  2. Id. Condicion Venta:
    *********************************************************************************/   
    function __construct(){
        $this->url_biblioteca  = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"].'/biblioteca-fotos-web' ; // Ruta de la biblioteca de fotos.
        $this->url_temp     = $this->url_biblioteca . '/temp/'; // Carpeta Temporal
        $this->url_thumb    = $this->url_biblioteca . '/fotos-100/'; // Carpeta Thumbnails
        $this->url_small    = $this->url_biblioteca . '/fotos-300/'; // Carpeta imagenes pequeñas
        $this->url_medio    = $this->url_biblioteca . '/fotos-600/'; // Carpeta imagenes medianas
        $this->url_large    = $this->url_biblioteca . '/fotos-1000/'; // Carpeta imagenes grandes
        $this->url_marcas   = $this->url_biblioteca . '/marcas-logos/'; // Carpeta imagenes de marcas y logos
        
        $this->carpeta_biblioteca = $_SERVER["DOCUMENT_ROOT"]."/biblioteca-fotos-web";
        $this->carpeta_temp       = $this->carpeta_biblioteca . '/temp/'; // Carpeta Temporal
        $this->carpeta_thumb      = $this->carpeta_biblioteca . '/fotos-100/'; // Carpeta Thumbnails
        $this->carpeta_small      = $this->carpeta_biblioteca . '/fotos-300/'; // Carpeta imagenes pequeñas
        $this->carpeta_medio      = $this->carpeta_biblioteca . '/fotos-600/'; // Carpeta imagenes medianas
        $this->carpeta_large      = $this->carpeta_biblioteca . '/fotos-1000/'; // Carpeta imagenes grandes
    }

    /**************************************************************
    *                 GENERA HTML FOTO
    * Genera el html necesario para las fotos. 
    * El parámetro $psize: debe ser LG, MD, SM, TH
    **************************************************************/
    function genera_html_foto ($pfilename,$psize){
        $lcsize = strtolower($psize);
        if ($lcsize == 'th'){
            $url    = $this->url_thumb;
            $folder = $this->carpeta_thumb;
        }elseif ($lcsize == 'sm'){
            $url    = $this->url_small;
            $folder = $this->carpeta_small;
        }elseif ($lcsize == 'md'){
            $url = $this->url_medio;
            $folder = $this->carpeta_medio;
        }elseif ($lcsize == 'lg'){
            $url    = $this->url_large;
            $folder = $this->carpeta_large;
        }
        $lbexiste_webp = false;
        $lbexiste_jpg  = false;
        $lcfilename = $url . $pfilename . $lcsize;
        $out  = '<picture>';
        if (file_exists($folder.$pfilename.$lcsize.'.webp')){
            $out .= '<source type="image/webp" srcset="'.$lcfilename.'.webp">';
        };
        // if ($lbexiste_webp){
        //    $out .= '<source type="image/webp" srcset="'.$lcfilename.'.webp">';
        // }
        // if ($lbexiste_jpg){
        //    $out .= '<source type="image/jpeg" src="'.$lcfilename.'.jpg" alt="'.$pfilename.'">';
        // }
        // if (!$lbexiste_jpg && !$lbexiste_webp){
            $out .= '<img class="img-fluid" src="./img/img-soon-lefre-'.$lcsize.'.jpg">';
        // }
        $out .= '</picture>';
        unset($lcsize,$url,$lbexiste_webp,$lbexiste_jpg,$lcfilename);
        return $out;
    }

    /*************************************************************************************************
    * GENERA_IMAGENES
    * Esta función genera los archivos JPEG y WEBP de las  imagenes que se puedan subir a la pagina,
    * en los distintos tamaños (thumb,small,medio y Large),
    * Convierte tanto en jpeg como webp una foto dada.
    * Parametros:  
    * pnombre_archivo: Nombre de archivo de imagen (sin ruta y con extensión).
    **************************************************************************************************/
    function genera_imagenes($pnombre_archivo){
        $this->error = false ;
        $this->source_archivo = $pnombre_archivo;
        $source_file = $this->carpeta_temp . $this->source_archivo;
        if (file_exists($source_file)) {
            list($this->source_width, $this->source_height, $this->source_ext) = getimagesize($source_file);
            /* Procedemos a convertir */
            $this->convierte($source_file,IMAGETYPE_JPEG, $this->size_thumb);
            if (!$this->error){
                $this->convierte($source_file,IMAGETYPE_WEBP, $this->size_thumb);
            }
            if (!$this->error && !unlink($source_file)){
                $this->error = true ;
                $this->error_msj  = 'Se han creado las imagenes para el producto '.$pnombre_archivo.' pero no se pudo eliminar la imagen temporal';  
                $this->error_tpo  = 'error';
                $this->error_file = 'clase_fotos_productos';
            }            
        }else{ 
            $this->error = true ;
            $this->error_msj  = 'No se pudo localizar imagen en carpeta temporal. File='.$this->source_archivo;
            $this->error_tpo  = 'error';
            $this->error_file = 'clase_fotos_productos';
        }
        unset($source_file);
    }

    /*************************************************************************************************
     * CONVIERTE.
     * Dado un archivo de imagen, la convierte a todos los tamaños requeridos, en JPEG o WEBP.
     * Parametros:  
     * $psource_file: Archivo imagen con toda su ruta.
     * $pdest_ext   : Tipo de formato de imagen a convertir, son Constantes de Imagenes PHP. 
     *                Solo 2 valores: IMAGETYPE_JPEG o IMAGETYPE_WEBP.
     * $pdest_size  : Tamaño a convertir. Siempre el primero a convertir debe ser thumbnail. A partir
     *                de este formato comienza a generar los demás tamaños.
     **************************************************************************************************/
    private function convierte ($psource_file,$pdest_ext,$pdest_size) {
        if ($pdest_size == $this->size_thumb){
            $dest_folder = $this->carpeta_thumb;
            $abreviatura = 'th';
            $calidad = 70;
        }elseif ($pdest_size == $this->size_small){
            $dest_folder = $this->carpeta_small ;
            $abreviatura = 'sm';
            $calidad = 90;
        }elseif ($pdest_size == $this->size_medio){
            $dest_folder = $this->carpeta_medio ;
            $abreviatura = 'md';
            $calidad = 100;
        }elseif ($pdest_size == $this->size_large){
            $dest_folder = $this->carpeta_large ;
            $abreviatura = 'lg';
            $calidad = 100;       
        }

        /* Si la imagen es PNG le removemos el Alpha Channel */
        if ($this->source_ext == IMAGETYPE_PNG){
            $imagen_origen  = imagecreatefrompng($psource_file);
            $imagen_tmp = imagecreatetruecolor($this->source_width, $this->source_height);
            $white = imagecolorallocate($imagen_tmp,  255, 255, 255);
            imagefilledrectangle($imagen_tmp, 0, 0, $this->source_width, $this->source_height, $white);
            imagecopy($imagen_tmp, $imagen_origen, 0, 0, 0, 0, $this->source_width, $this->source_height);
            imagecopy($imagen_origen, $imagen_tmp, 0, 0, 0, 0, $this->source_width, $this->source_height);
            unset($white);
            imagedestroy($imagen_tmp);
        }elseif ($this->source_ext == IMAGETYPE_JPEG){
            $imagen_origen  = imagecreatefromjpeg($psource_file);
        }

        /* Cambiamos el tamaño del archivo*/
        $imagen_destino = imagecreatetruecolor($pdest_size, $pdest_size);
        imagecopyresized($imagen_destino, $imagen_origen, 0, 0, 0, 0, $pdest_size, $pdest_size, $this->source_width, $this->source_height);

        $this->destino_archivo = strtolower(pathinfo($this->source_archivo,PATHINFO_FILENAME)) . '-lefre-'. $abreviatura;
        if ($pdest_ext == IMAGETYPE_JPEG){ /************   ARCHIVO JPEG   ************/
            $destino = $dest_folder . $this->destino_archivo . ".jpg" ;
            $out = imagejpeg($imagen_destino,$destino,$calidad) ;
        }elseif ($pdest_ext == IMAGETYPE_WEBP){ /************   ARCHIVO WEBP   ************/
            $destino = $dest_folder . $this->destino_archivo . ".webp" ;
            $out = imagewebp($imagen_destino,$destino,$calidad) ;
        }

        unset($dest_folder,$destino,$calidad);
        imagedestroy($imagen_destino);
        imagedestroy($imagen_origen);
        if (isset($out)){
            if ($out){
                if ($pdest_size == $this->size_thumb){
                    $this->convierte($psource_file,$pdest_ext,$this->size_small);
                }elseif ($pdest_size == $this->size_small){
                    $this->convierte($psource_file,$pdest_ext,$this->size_medio);
                }elseif ($pdest_size == $this->size_medio){
                    $this->convierte($psource_file,$pdest_ext,$this->size_large);
                }
            }else{
                $this->error = true ;
                $this->error_nro  = 45100;  // Nro de Error. Si es una excepción de usuario se coloca 45000
                $this->error_msj  = 'Error creando archivos de imagenes de producto. File='.$this->source_archivo;
                $this->error_tpo  = 'error';
                $this->error_file = 'clase_fotos_productos';
            }
        }else{
            $this->error = true ;
            $this->error_nro  = 45101;  // Nro de Error. Si es una excepción de usuario se coloca 45000
            $this->error_msj  = 'No se pudo generar imagen. File='.$this->source_archivo;
            $this->error_tpo  = 'error';
            $this->error_file = 'clase_fotos_productos';
        }
    }


    /**************************************************************
    *                 VALIDA UPLOAD FILE
    * Esto es cuando se intanta subir una imagen. Se proceden
    * hacer validaciones con respecto al archivo.
    **************************************************************/
    function valida_upload_file(){
        // 1. Es un archivo correctamente subido?
        if (!is_uploaded_file($this->tmp_name)){
            $this->error     = true;
            $this->error_nro = 25400;  // Nro de Error. Si es una excepción de usuario se coloca 45000
            $this->error_msj = "el archivo ".$this->name." No es un archivo correctamente subido";  // Mensaje de Error.
            $this->error_tpo = "error";
        }
        
        // 2. Hubo algún error en la subida
        if (!$this->error && $this->upload_error !== UPLOAD_ERR_OK) {
            $this->error     = true;
            $this->error_nro = 25401; 
            $this->error_msj = "el archivo ".$this->name." no se pudo subir al servidor. Error ".$this->upload_error;  // Mensaje de Error.
            $this->error_tpo = "error";
        }

        // 3. Verificamos que de verdad sea una imagen.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (!array_search($finfo->file($this->tmp_name),array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp'))){
            $this->error     = true;
            $this->error_nro = 25402;  
            $this->error_msj = "el archivo ".$this->name." no se pudo subir al servidor. Error ".$this->upload_error;  // Mensaje de Error.
            $this->error_tpo = "error";  // Tipo de error (2 valores): "warning" o "error"    
    
        }
        $finfo = null;
        unset($finfo);
        return (!$this->error);
    }

}    
?>