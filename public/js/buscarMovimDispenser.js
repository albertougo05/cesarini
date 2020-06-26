
// Codigo jQuery:
$(document).ready( function () {

    // Boton de ir arriba
    $('div#scrollUp').click( function () {
        $('body, html').animate({
            scrollTop: '0px'
        }, 300);
    });

    // Para chequear scroll para boton ir arriba
    $(window).scroll( function () {
        if( $(this).scrollTop() > 0 ) {
            $('#scrollUp').show();
        } else {
            $('#scrollUp').hide();
        }
    });

	// Evento click para seleccionar y redireccionar a mostrar dispenser
	$('table#tablaBuscarGuia tr').on('click', function(){
		var id = $(this).find('td:first').html();

		location.assign(global_movimdispenser + "?id=" + id);
	});

    // Radio buttons click radSerie
    $('input:radio').click(function () {
        var classCell = this.value;

        cambiarCeldaBusq(classCell);
    });

    // Funcion para cambiar celda de busqueda
    function cambiarCeldaBusq(celda) {
        $('input#filter').val('');   // Vacio el input
        var celdaSel = ['cellFecha', 'cellSerie', 'cellInterno', 'cellModelo', 'cellEmpleado', 'cellCliente', 'cellEstado'];
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

    // Filtrado de tabla movimiento dispenser  
    $('#guiaReparto').tableFilter({ tableID: '#tablaBuscarGuia', 
                                   filterID: '#filter',
                                   filterCell: '.filter-cell',
                                   autofocus: true});


});
