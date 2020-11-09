// Javascript
//
var _check_fecha, _check_disp, _check_clie, _check_esta;


//
/** Funcionamiento boton UpScroll  **/
// 
const _botonUp = document.getElementById("scrollUp");

_botonUp.addEventListener("click", function () {
    document.body.scrollTop = 0; // For Safari
    document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
});
// When the user scrolls down 300px from the top of the document, show the button
window.onscroll = function() {
    if (document.body.scrollTop > 280 || document.documentElement.scrollTop > 280) {
        //$('#scrollUp').show();
         _botonUp.style.display = "block";
    } else {
        //$('#scrollUp').hide();
         _botonUp.style.display = "none";
    }    
};
/** end **/


function strOrdena() {
    // global_urlParam: "?column=NroInterno&amp;orden=asc&amp;desde=2019-04-20&amp;hasta=2019-04-30&amp;ordFecha=asc&amp;idDisp=10"
    const col = $("input:radio[name='radioOrden']:checked").prop('id');
    const ord = $("input:radio[name='radOrden']:checked").prop('id');
    let strOrden = 'column=' + col + '&orden=' + ord;

    // Si está puesto el filtro de fecha...
    if (_urlParams.ordFecha != '') {
        let filtroFecha = '&ordFecha=' + _urlParams.ordFecha;

        // Si el ordenamiento tambien es fecha, pongo el orden igual 
        if (col == 'Fecha') {
            strOrden = 'column=' + col + '&orden=' + _urlParams.ordFecha;
        }

        if (_urlParams.desde != '' && _urlParams.hasta == '') {
            filtroFecha += "&desde=" + _urlParams.desde;
        } else if (_urlParams.desde == '' && _urlParams.hasta != '') {
            filtroFecha += "&hasta=" + _urlParams.hasta;
        } else if (_urlParams.desde != '' && _urlParams.hasta != '') {
            filtroFecha += "&desde=" + _urlParams.desde + "&hasta=" + _urlParams.hasta;
        } 

        strOrden += filtroFecha;
    }   

    if (_urlParams.idDisp != '') strOrden += '&idDisp=' + _urlParams.idDisp;
 
    if (_urlParams.idClie != '') strOrden += '&idClie=' + _urlParams.idClie;

    if (_urlParams.estado != '') strOrden += '&estado=' + _urlParams.estado;
   
    return strOrden;
}

// Set valores de filtros segun los parametros pasados
function _setFiltrosSegunParams() {
    // set a false
    _check_fecha = _check_disp = _check_clie = _check_estado = false;
    if (_urlParams.ordFecha != '') {
        _check_fecha = true;
    }
    if (_urlParams.idDisp != '') {
        _check_disp = true;
    }
    if (_urlParams.idCli != '') {
        _check_clie = true;
    }
    if (_urlParams.estado != '') {
        _check_estado = true;
    }
}



// Codigo jQuery:
$(document).ready( function () {

    _setFiltrosSegunParams();

    $('#tablaMovimDisp tr').unbind('click');

    // Click en tabla para mostrar MODAL datos de Movimiento completo
    $('#tablaMovimDisp tr').on('click', function(){
        var idMov = $(this).find('td:first').html();

        // Traer datos via ajax que llenará el modal...
        $.getJSON( global_datamovimdisp, { idmov: idMov } )
            .done(function( json ) {

                // lleno datos del MODAL...
                $('td#idDisp').html(json.IdDispenser);
                // $('td#nroSerie').html(json.NroSerie);
                $('td#nroInter').html(json.NroInterno);
                //$('td#modeloDisp').html(json.Modelo);
                $('td#observac').html(json.Observaciones);
                $('td#estadoDisp').html(json.EstadoDisp);
                $('p#nombreEmpl').html('<strong>' + json.Empleado + '</strong>');
                if (json.Cliente != '') {
                    $('p#nombreClie').html('<strong>' + json.Cliente + '</strong>');
                    $('p#direccClie').html('<em>' + json.Direccion + ', ' + json.Localidad + '</em>');
                    $('p#telcelClie').html('<em>Tel. ' + json.Telefono + ' - Cel. ' + json.Celular + '</em>');
                } else {
                    $('p#nombreClie').html('');
                    $('p#direccClie').html('');
                    $('p#telcelClie').html('');
                }
                $('input#Fecha').val(json.Fecha);
                $('input#Estado').val(json.Estado);
                $('input#Observaciones').val(json.Observaciones);

            })
            .fail(function( jqxhr, textStatus, error ) {
                var err = textStatus + ", " + error;
                console.log( "Request Failed: " + err );
                alert('Fallo al traer datos de movimiento...');
                return;
        });

        // Lanza el MODAL ...
        $('#modalMovimDispenser').modal('show');

    });

    // para evitar doble click..
    $("input:radio[name='radioOrden']").unbind( "click" );

    // Ordeno directo por radio buttons
    $("input:radio[name='radioOrden']").click(function (event) {

        if ($(this).is(':checked')) {

            location.assign(global_ordenainforme + "?" + strOrdena());
        }
    });

    // para evitar doble click..
    $("input:radio[name='radOrden']").unbind( "click" );

    // Ordeno por botones de Orden...
    $("input:radio[name='radOrden']").click(function (event) {

        if ($(this).is(':checked')) {

            location.assign(global_ordenainforme + "?" + strOrdena());
        }
    });

    // Evito doble click
    $('input:checkbox#btnFiltroFecha').unbind('click');

    // Click boton Filtro por fecha
    $('input:checkbox#btnFiltroFecha').click(function (event) {
        _check_fecha = $(this).prop( "checked" );

        // Si el check se pone...
        if (_check_fecha) {
            // Lanza el modal fechas
            $('#modalFechas').modal('show');
        } else {
            // Saco el tilde
            $(this).prop( "checked", false );
            _check_fecha = false;
            // Borro los parametros de la url
            _urlParams.ordFecha = _urlParams.desde = _urlParams.hasta = '';

            // Vuelvo al orden que está tildado...
            location.assign(global_ordenainforme + "?" + strOrdena());
        }
    })

    $('button#btnCancela').click(function(event) {
        /* Cambia estado del checkbox Fecha */
        $('input:checkbox#btnFiltroFecha').prop( "checked", !_check_fecha );
    });

    $('button#btnClose').click(function(event) {
        /* Cambia estado del checkbox Fecha */
        $('input:checkbox#btnFiltroFecha').prop( "checked", !_check_fecha );
    });

    // para evitar doble click..
    $("button#btnConfFecha").unbind( "click" );

    // Boton confirma fecha/orden MODAL
    $("button#btnConfFecha").click(function (event) {
        // Orden fecha del modal
        let ordenFecha = $("input:radio[name='optOrden']:checked").val();
        // Orden de los radios... (que no son fecha)
        let colOrden = $("input:radio[name='radioOrden']:checked").prop('id');
        let ordenCol = $("input:radio[name='radOrden']:checked").val();
        let fechaDesde = $('input#fechaDesde').val();
        let fechaHasta = $('input#fechaHasta').val();
        let strOrden = 'column=' + colOrden + '&orden=' + ordenCol;

        // Esconde el modal Fecha
        $('#modalFechas').modal('hide');

        // Si ambas fechas están vacias...
        if (fechaDesde == '' && fechaHasta == '') {
             $('input:checkbox#btnFiltroFecha').prop( "checked", false );
            return false;
        }

        if (fechaDesde != '') {
            strOrden += '&desde=' + fechaDesde;
        } 
        if (fechaHasta != '') {
            strOrden += '&hasta=' + fechaHasta;
        }
        strOrden += '&ordFecha=' + ordenFecha;

        location.assign(global_ordenainforme + "?" + strOrden);
    });

    // Evito doble click
    $('input:checkbox#btnFiltroDisp').unbind('click');

    // Click boton Filtro por dispenser
    $('input:checkbox#btnFiltroDisp').click(function (event) {
        _check_disp = $(this).prop( "checked" );

        // Si el check se pone...
        if (_check_disp) {
            // buscar dispenser global_buscardisp
           location.assign(global_buscardisp + "?infoMovDisp=true");

        } else {
            // Saco el tilde
            $(this).prop( "checked", false );
            _check_disp = false;
            _urlParams.idDisp = '';

            // Vuelvo al orden que está tildado...
            location.assign(global_ordenainforme + "?" + strOrdena());
        }
    })

    // Evito doble click
    $('input:checkbox#btnFiltroClie').unbind('click');

    // Click boton filtro cliente
    $('input:checkbox#btnFiltroClie').click( function (event) {
        _check_clie = $(this).prop( "checked" );

        // Si el check se pone...
        if (_check_clie) {
            // Boton Filtro Cliente, lanza MODAL
            $('#modalBuscaClie').modal('show');

        } else {
            // Saco el tilde
            $(this).prop( "checked", false );
            _check_clie = false;
            _urlParams.idClie = '';

            // Vuelvo al orden que está tildado...
            location.assign(global_ordenainforme + "?" + strOrdena());
        }

    });

    // Filtrado de tabla Clientes
    $('#filter-container').tableFilter({ tableID: '#tablaBuscarCli', 
                                   filterID: '#filter',
                                   filterCell: '.filter-cell',
                                   autofocus: true});

    $('#tablaBuscarCli tr').unbind('click');

    // Evento click para seleccionar y cerrar MODAL
    $('#tablaBuscarCli tr').on('click', function(){
        const idClie = $(this).find('td:first').html();
        // Cierro el MODAL Clientes
        $('#modalBuscaClie').modal('hide');
        _check_clie = true;
        _urlParams.idClie = idClie;

        // Voy a filtrar el listado
        location.assign(global_ordenainforme + "?" + strOrdena());
    });

    // Boton Cerrar Modal Buscar Cliente
    $('#btnCerrModBusCli').click(function (event) {
        estadoCheckBox = $('input:checkbox#btnFiltroClie').prop("checked");
        if (estadoCheckBox) {
            $('input:checkbox#btnFiltroClie').prop("checked", false);
        } else {}
    });

    // Boton X Modal Buscar Cliente
    $('#btnXxModBusCli').click(function (event) {
        estadoCheckBox = $('input:checkbox#btnFiltroClie').prop("checked");
        if (estadoCheckBox) {
            $('input:checkbox#btnFiltroClie').prop("checked", false);
        }
    });

    // Evito doble click
    $('input:checkbox#btnFiltroEsta').unbind('click');

    // Click boton filtro estado
    $('input:checkbox#btnFiltroEsta').click( function (event) {
        //_check_clie = $(this).prop( "checked" );

        // Si el check se pone...
        if ($(this).prop( "checked" )) {
            // Boton filtro por estado, lanza MODAL
            $('#modalEstados').modal('show');

        } else {
            // Saco el tilde
            $(this).prop( "checked", false );
            //_check_clie = false;
            _urlParams.estado = '';

            // Vuelvo al orden que está tildado...
            location.assign(global_ordenainforme + "?" + strOrdena());
        }
    });

    // Evento click para seleccionar y cerrar MODAL estados
    $('#btnSelecEstado').on('click', function(){
        const estado = $('select#selectEstado').val();
        // Cierro el MODAL Clientes
        $('#modalBuscaClie').modal('hide');
        _urlParams.estado = estado;

        // Voy a filtrar el listado
        location.assign(global_ordenainforme + "?" + strOrdena());
    });

    // Boton X (cerrar) Modal filtro Estado
    $('#closeModalEstados').click(function (event) {
        if ($('input:checkbox#btnFiltroEsta').prop("checked")) {
            // Destildo boton filtro estado
            $('input:checkbox#btnFiltroEsta').prop("checked", false);
        } 
    });

    // Boton Cerrar Modal filtro Estado
    $('#btnCerrarModalEst').click(function (event) {
        if ($('input:checkbox#btnFiltroEsta').prop("checked")) {
            // Destildo boton filtro estado
            $('input:checkbox#btnFiltroEsta').prop("checked", false);
        }
    });


});
