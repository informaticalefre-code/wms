/***********************************************************************************************
 *  Función Toggle:
 *  Muestra o quita el formulario de busqueda al darle al botón buscar de la barra de menu
 **********************************************************************************************/  
function toggle_barra_search(){
    var element = document.getElementById("barra-cont-form-search");
    var css_styles = getComputedStyle(element);
    var css_opacity = css_styles.getPropertyValue('opacity');
    // console.log("boton toggle de la barra buscadora");
    // console.log("Valor de Opacity1:"+css_opacity);
    if (element.style.opacity == 0){
        element.style.opacity    = 1;
        element.style.transform  = 'translateY(56px)';
        element.style.transition = 'all ease-in-out .45s';
        document.getElementById("search-input").focus();
    }else{
        element.style.opacity    = 0;
        element.style.transform  = 'translateY(-56px)';
        element.style.transition = 'all ease-in-out .45s';
    }
    var css_opacity = css_styles.getPropertyValue('opacity');
    element.toggleAttribute("opacity");
    element.classList.toggle("gone");
};