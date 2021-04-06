// Código javascript

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


// Variable para saber que boton está modificando fecha
var _idRadioFecha = '';

/* $('input[type=checkbox]').prop('checked');
// Returns true if checked, false if unchecked. */

function actualizoRadioOrden() {
	$('input:radio#' + global_radioButtonCheck).prop("checked", true);
}


// Codigo jQuery:
$(document).ready( function () {
	// Actualiza radio buttons cuando carga la página
	actualizoRadioOrden();

    $('table#tablaBuscarGuia tr').unbind('click');

	// Evento click para seleccionar y redireccionar a mostrar dispenser
	$('table#tablaBuscarGuia tr').on('click', function(){
		var id = $(this).find('td:first').html();

        if (global_vieneDeMovimDisp) {
            // Va a form Movimdispenser
            location.assign(global_movimDispenser + "?idDisp=" + id);
        } else if (global_vieneDeInfoMovDisp) {
            // Va a form InfoMovDisp
            _urlParam = _urlParam.replace(/amp;/g, '');

//console.log('Va a: ' + global_infoMovDisp + ' urlParam: ' + _urlParam + "&idDisp=" + id);

            location.assign(global_infoMovDisp + _urlParam + "&idDisp=" + id);
        } else {
            // Va a form datos Dispenser
		    location.assign(global_dispenser + "?id=" + id);
        }
	});

    // Radio buttons click radSerie
    $('input#radSerie').click(function () {
    	cambiarCeldaBusq('cellSerie');
    });
    $('input#radInterno').click(function () {
    	cambiarCeldaBusq('cellInterno');
    });
    $('input#radModelo').click(function () {
    	cambiarCeldaBusq('cellModelo');
    });
    $('input#radTipo').click(function () {
    	cambiarCeldaBusq('cellTipo');
    });
    $('input#radAlta').click(function () {
    	cambiarCeldaBusq('cellAlta');
    });
    $('input#radEstado').click(function () {
    	cambiarCeldaBusq('cellEstado');
    });

    // Funcion para cambiar celda de busqueda
    function cambiarCeldaBusq(celda) {
    	$('input#filter').val('');   // Vacio el input
    	var celdaSel = ['cellSerie', 'cellInterno', 'cellModelo', 'cellTipo', 'cellAlta', 'cellEstado'];
    	// Remuevo clase filter-cell de todos los td
    	for (var i = 0; i < celdaSel.length; i++) {
    		if ($("td." + celdaSel[i]).hasClass("filter-cell")) {
    			$("td." + celdaSel[i]).toggleClass("filter-cell");
    		}
    	}
    	// Busco el indice
    	var index = celdaSel.findIndex(function (data) {
    		return data == celda;
    	});
    	$("td." + celdaSel[index]).toggleClass("filter-cell");
    	$("input#filter").focus();
    	$("input#filter").change();
    };

	// Filtrado de tabla dispenser	
	$('#guiaReparto').tableFilter({ tableID: '#tablaBuscarGuia', 
                                    filterID: '#filter',
                                    filterCell: '.filter-cell',
	                                autofocus: true});

    $("input:radio[name='radioOrden']").unbind('click');

	// Ordeno directo por radio buttons
	$("input:radio[name='radioOrden']").click(function (event) {
    	event.stopPropagation();
    	let idRadio = this.id;
    	let strOrden = '';
        let mostrarBajas = '&mostrarbajas=' + $('input#chkVerBajas').is(':checked');

   	    if ($(this).is(':checked')) {
	    	strOrden += 'column=' + idRadio + '&orden=asc';

            if (global_vieneDeMovimDisp) {

                location.assign(global_ordenaBuscar + "?" + strOrden + mostrarBajas + "&movimDisp=true");
            } else {

                location.assign(global_ordenaBuscar + "?" + strOrden + mostrarBajas);
            }
	    }
    });

    $('input#chkVerBajas').unbind('click');

    // Recargo la página para mostrar Bajas
    $('input#chkVerBajas').click(function(event) {
        var idRadio = '';
        var strOrden = '';
        var mostrarBajas = '&mostrarbajas=' + $('input#chkVerBajas').is(':checked');

        if ($('#NroSerie').prop('checked')) {
            idRadio = $('#NroSerie').attr('id');
        } else if ($('#NroInterno').prop('checked')) {
            idRadio = $('#NroInterno').attr('id');
        } else if ($('#Model').prop('checked')) {
            idRadio = $('#Model').attr('id');
        } else if ($('#Estado').prop('checked')) {
            idRadio = $('#Estado').attr('id');
        }

        if (idRadio === '') {
            strOrden += 'column=FechaAlta&orden=desc';
        } else strOrden += 'column=' + idRadio + '&orden=asc';

        if (global_vieneDeMovimDisp) {

            location.assign(global_ordenaBuscar + "?" + strOrden + mostrarBajas + "&movimDisp=true");
        } else {

            //console.log('String orden: ' + strOrden + mostrarBajas)

            location.assign(global_ordenaBuscar + "?" + strOrden + mostrarBajas);
        }

    });

	// para evitar doble click..
	$("input:radio[name='radioFechas']").unbind( "click" );

	// Filtro por fecha - Lanzo Modal
	$("input:radio[name='radioFechas']").click(function (event) {
		event.stopPropagation();
		// Id para identificar en el modal
    	_idRadioFecha = this.id;

    	// Cambio el titulo del MOdal
    	let nombreFecha = $("label[for='" + _idRadioFecha +"']").text();
    	$("#tituloModalFechas").text('Filtro por: ' + nombreFecha);

		// Lanza el modal fechas
		$('#modalFechas').modal('show');
		// Si sale por Cancel, vuelvo al estado original
		$("input:radio[name='radioFechas']").prop('checked', false);
		actualizoRadioOrden();
	});

	// para evitar doble click..
	$("button#btnConfFecha").unbind( "click" );

	// Boton confirma fecha/orden MODAL
	$("button#btnConfFecha").click(function (event) {
		let radioCheckOrden = $("input:radio[name='optOrden']:checked").val();
		let fechaDesde = $('input#fechaDesde').val();
		let fechaHasta = $('input#fechaHasta').val();
		let strOrden = 'column=' + _idRadioFecha + '&orden=' + radioCheckOrden;
        var mostrarBajas = '&mostrarbajas=' + $('input#chkVerBajas').is(':checked');

		$('#modalFechas').modal('hide');

		if (fechaDesde != '') {
			strOrden += '&desde=' + fechaDesde;
		} 
		if (fechaHasta != '') {
			strOrden += '&hasta=' + fechaHasta;
		}

        if (global_vieneDeMovimDisp) {

            location.assign(global_ordenaBuscar + "?" + strOrden + mostrarBajas + "&movimDisp=true");
        } else {

		    location.assign(global_ordenaBuscar + "?" + strOrden + mostrarBajas);
        }
	});


});
