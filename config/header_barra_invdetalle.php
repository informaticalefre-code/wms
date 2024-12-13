    <div id="panel_superior">
        <?php
            require_once 'barra_search_filter.html';
        ?>
        <div id="barra-cont-form-search">
            <form class="barra-form-search" method="post" id="form-search" onsubmit="barra_buscar()">
                <input type="search" name="search-text" id="search-input" class="w-75" autocomplete="off" placeholder="¿Qué quieres buscar?"/>
                <button class="btn" type="submit"> <img class="nav-botones" src="img/svg/search.svg" width="30"></button>
                <div id="search-options" style="color:white;">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search-options" id="inlineRadio1" value="codigo" checked onchange="activa_search_focus()">
                        <label class="form-check-label text-light"  for="inlineRadio1">Código</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search-options" id="inlineRadio2" value="nombre" onchange="activa_search_focus()">
                        <label class="form-check-label text-light" for="inlineRadio2">Nombre</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search-options" id="inlineRadio3" value="ubicacion" onchange="activa_search_focus()">
                        <label class="form-check-label text-light" for="inlineRadio3">Ubicación</label>
                    </div>
                </div>
            </form>            
        </div>
    </div>