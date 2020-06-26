// Código javascript

// Variable para saber que boton está modificando fecha
var _idRadioFecha = '';
// String para filtro de fecha
var _strOrdenFecha = '';
// Array con ids de clientes selecionados
var _arrClientes = [];

// Funciones para jQuery
// 
function ordenar() {
    // Ordenar...
    var ordenar = $("input:radio[name='radioOrden']:checked").prop('id');
    var orden   = $("input:radio[name='radOrden']:checked").val();

    return '&ordenar=' + ordenar + '&orden=' + orden;
}

function agrupar() {
    // Agrupar...
    var agruparPor = [];
    var idx = 0;
    var param = '';

    $('input:checkbox.agrupar').each(function (index) {
        // Si el cb está tildado
        if ($(this).is(':checked')) {
            agruparPor[idx] = $(this).prop('id');
            idx++;
        }
    });
    if (agruparPor.length != 0) {
        param += 'agrupar=';
        // recorro el array
        for (var i in agruparPor) {
            // extraigo letras chk...
            if (agruparPor[i] === 'chkTipo') {
                agruparPor[i] = 'IdTipo';
            } else {
                agruparPor[i] = agruparPor[i].substr(3);
            }
            param += agruparPor[i];
            if (i != agruparPor.length - 1) {
                param += '-';
            }
        }
    } 

    return param;
}

function filtrar() {
    var param = '';
    //var selEstado = $('select#selEstado').val(); 
    var selCliente = $('select#selCliente').val();
    var selTipo = $('select#selTipo').val();

    // ESTADO
    var estado = '&filtEstado=';
    $("input:checkbox:checked.estado").each(   
        function() {
            estado += $(this).val() + '-';
        }
    );
    if (estado != '&filtEstado=') { param = estado.substring(0, estado.length - 1); }

    // TIPOS
    var tipo = '&filtTipo=';
    for (var i in selTipo) {
        if (selTipo[i] != 0) {
            tipo += selTipo[i];
            if (i != selTipo.length - 1) {
                tipo += '-';
            }
        }
    }
    // Si tipo tiene parametros..
    if (tipo != '&filtTipo=') { param += tipo; } 

    // CLIENTE/S
    var cliente = '&filtCliente=';
    for (var i in _arrClientes) {
        if (!isNaN(_arrClientes[i])) {
            cliente += _arrClientes[i];
            if (i != _arrClientes.length - 2) {
                cliente += '-';
            }
        }
    }
    // Si cliente tiene parametros..
    if (cliente != '&filtCliente=') { param += cliente; }

    return param;
}

// Arma en div, lista de clientes seleccionados
function _listaClientesSel() {

    if (_arrClientes.length == 0) {

        $('#clienteSel').addClass('invisible').html('');
        $('input#conClie').prop('checked', false);

    } else {
        var listclie = '<ul>';

        for (var i = 1; i < _arrClientes.length; i = i+2) {
            var chkbox = "<input class='form-check-input' type='checkbox' value='" + _arrClientes[i-1];
            chkbox += "' id='idCli" + _arrClientes[i-1] + "' checked>";
            chkbox += "<label class='form-check-label' for='idCli" + _arrClientes[i-1];
            chkbox += "'>" + _arrClientes[i] + "</label>";
            listclie += "<li>" + chkbox + '</li>';
        }
        listclie += '</ul>';

        if ($('#clienteSel').hasClass('invisible')) {
            $('#clienteSel').removeClass('invisible');
        }
        $('#clienteSel').html(listclie);

        // Checkbox detalle cliente
        if ($('input#conClie').is(':checked')) {
            // Nada
        } else {
            $('input#conClie').prop('checked', true);
        }

        // Asociar el evento click al checkbox
        $('li input:checkbox').on('click', function (e) {
            var valChk = $(this).val();
            // Indice del valor...
            var idxVal = _arrClientes.indexOf(valChk);
            var nombre = _arrClientes[idxVal + 1];
            // Quito el valor del array...
            var filteredAry = _arrClientes.filter(function(e) { return e != valChk });
            _arrClientes = filteredAry.filter(function(e) { return e != nombre });
            // Muestro de nuevo la lista...
            _listaClientesSel();
        });
    }
}

function mostrar() {
    var retorno = '';

    if ($('input#conMovs:checked').val() !== undefined) {
        retorno += '&conmovim=true';
    } else retorno += '&conmovim=false';

    if ($('input#conClie:checked').val() !== undefined) {
        retorno += '&conclie=true';
    } else retorno += '&conclie=false';

    return retorno;
}



// Código jQuery
// 
$(document).ready( function () {

    $('a#btnGenInfo').unbind('click');

    // para evitar doble click..
    $("input:radio[name='radioFecha']").unbind( "click" );

    // Filtro por fecha - Lanzo Modal
    $("input:radio[name='radioFecha']").click(function (event) {
        event.stopPropagation();
        // Id para identificar en el modal
        _idRadioFecha = this.id;

        // Cambio el titulo del MOdal
        let nombreFecha = $("label[for='" + _idRadioFecha +"']").text();
        $("#tituloModalFechas").text('Filtro por: ' + nombreFecha);

        // Lanza el modal fechas
        $('#modalFechas').modal('show');
    });

    // para evitar doble click..
    $("button#btnConfFecha").unbind( "click" );

    // Boton confirma fecha/orden ( MODAL )
    $("button#btnConfFecha").click(function (event) {
        let radioCheckOrden = $("input:radio[name='optOrden']:checked").val();
        let fechaDesde = $('input#fechaDesde').val();
        let fechaHasta = $('input#fechaHasta').val();
        _strOrdenFecha = 'fecha=' + _idRadioFecha + '&ordfecha=' + radioCheckOrden;
        let txtDesHas = '';

        $('#modalFechas').modal('hide');

        if (fechaDesde != '' || fechaHasta != '') {
            if (fechaDesde != '') { 
                _strOrdenFecha += '&desde=' + fechaDesde;
                txtDesHas = 'Desde: ' + fechaDesde;
            }
            if (fechaHasta != '') {
                _strOrdenFecha += '&hasta=' + fechaHasta;
                txtDesHas += ' Hasta: ' + fechaHasta;
            } 

            txtDesHas = '<small>' + txtDesHas + '</small>';
            if ($('div#rowFechas').hasClass('d-none')) {
                $('div#rowFechas').removeClass('d-none');
                $('p#p' + _idRadioFecha).html(txtDesHas);

            } else {
                // Quito todo los html de los p
                $('div#rowFechas p').html('');
                $('p#p' + _idRadioFecha).html(txtDesHas);
            }

        } else if (fechaDesde == '' && fechaHasta == '') {
            _strOrdenFecha = '';
            $("input:radio[name='radioFecha']").prop('checked', false);
        }
    });

    $('button#btnCancela').unbind('click');

    $('button#btnCancela').click(function (event) {
        // Si sale por Cancel, cancelo todo lo de fecha
        $("input:radio[name='radioFecha']").prop('checked', false);
        $('div#rowFechas p').html('');
        _strOrdenFecha = '';
    })

    $('button#btnBuscarClie').unbind('click');

    $('button#btnBuscarClie').click(function (event) {
        // Boton Buscar Cliente, lanza MODAL
        $('#modalBuscaClie').modal('show');
    })    

    // Filtrado de tabla Clientes
    $('#filter-container').tableFilter({ tableID: '#tablaBuscarCli', 
                                   filterID: '#filter',
                                   filterCell: '.filter-cell',
                                   autofocus: true});

    $('#tablaBuscarCli tr').unbind('click');

    // Evento click para seleccionar y cerrar MODAL
    $('#tablaBuscarCli tr').on('click', function(){
        var idClie = $(this).find('td:first').html();
        var nomCli = $(this).find('td:eq(1)').html(); // :eq( 2 )

        // Chequear si el cliente YA existe en el array (ha sido seleccionado)...
        if (_arrClientes.indexOf(idClie) === -1) {
            // guarda en array cliente seleccionado
            _arrClientes.push(idClie, nomCli);
            // Arma lista de clientes seleccionados
            _listaClientesSel();
        }

        $('#modalBuscaClie').modal('hide');
    });

    // Click BOTON 'Generar informe'...
    $('a#btnGenInfo').click(function (event) {
        // string a pasar como parametro
        var paramString = '?';
        // Agrupar...
        paramString += agrupar();
        // Ordenar...
        paramString += ordenar();
        // Filtrar...
        paramString += filtrar();
        // Fechas...
        if (_strOrdenFecha != '') { paramString += '&' + _strOrdenFecha; }
        paramString += mostrar();

        //console.log('Parametros: ' + paramString);

        //location.assign(global_imprimeinforme + paramString);
        window.open(global_imprimeinforme + paramString, '_blank');
    });


});
