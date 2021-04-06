//
// Funciones para modal búsqueda Movimientos de dispenser
// 

const BUSCARMOVS = {

	// Flag para cargar lista por única vez
	listaCargada: false,

	// Arma linea de la tabla
	lineaBody: function (movim) {
		let lin = "<tr class='linTabBuscarMovDisp' onclick='BUSCARMOVS.clickSelecMovim(this);'>";
		lin += "<td style='display: none;''>" + movim.Id + "</td>";
		lin += "<td class='cellFecha'>" + this.convertirFecha(movim.Fecha) + "</td>";
		lin += "<td class='cellInterno filter-cell'>" + movim.NroInterno + "</td>";
		lin += "<td class='cellModelo'>" + movim.Modelo + "</td>";
		lin += "<td class='cellEmpl'>" + movim.Empleado + "</td>";
		lin += "<td class='cellClie'>" + movim.Cliente || '' + "</td>";
		lin += "<td class='cellEstado'>" + movim.Estado + "</td></tr>";

		return lin;
	},

	// Evento click al seleccionar y redireccionar a Movimiento de disp
	clickSelecMovim: function (elem) {
		let idMov = $(elem).find('td:first').html();

		// Redirecciona a mostrar movimiento seleccionado
		location.assign( global_movimDisp + "?id=" + idMov );
	},

	// Funcion carga lista movimientos
	cargaListaMovimDisp: function () {
		let lin;
		const desde = $('#fechaDesde').val(),
			  hasta = $('#fechaHasta').val();

		$.get( global_buscarMovimDisp, {desde: desde, hasta: hasta}, function( data ) {

			$.each(data, function(index, item) {
				lin = BUSCARMOVS.lineaBody(item);
				$("#bodyTablaModalBuscar").append( lin );
			});
			// Indica que la lista ya está cargada...
			BUSCARMOVS.listaCargada = true;
		}, "json" );
	},

	// Funcion convierte fecha
	convertirFecha: function (fecha) {
		return fecha.split("-").reverse().join("/");
	},

    // Funcion para cambiar celda de busqueda
    cambiarCeldaBusq: function (celda) {
        $('input#filter').val('');   // Vacio el input

//console.log(celda);

        const celdaSel = ['cellInterno', 'cellEmpl', 'cellClie', 'cellEstado'];
        // Remuevo clase filter-cell de todos los td
        for (let i = 0; i < celdaSel.length; i++) {
            if ($("td." + celdaSel[i]).hasClass("filter-cell")) {
                $("td." + celdaSel[i]).toggleClass("filter-cell");
            }
        }
        // Busco el indice
        const index = celdaSel.findIndex(function (data) {
            return data == celda;
        });
        $("td." + celdaSel[index]).toggleClass("filter-cell");
        $("input#filter").focus();
        $("input#filter").change();
    },

};



// Radio buttons click radSerie
$('#radioBtsBuscarMovim input:radio').click(function () {
    let classCell = this.value;
    BUSCARMOVS.cambiarCeldaBusq(classCell);
});

// Filtrado de tabla buscar Visita
$('#filter-2-container').tableFilter({ tableID: '#tablaBuscarMovsDisp', 
                               filterID: '#filter',
                               filterCell: '.filter-cell',
	                           autofocus: true});

// Al cargar el modal, busca via ajax los datos
$('#modalBuscarMovimDisp').on('show.bs.modal', function (event) {

	if ( !BUSCARMOVS.listaCargada ) {
		// Si no ha sido cargada la lista...
		$("#bodyTablaModalBuscar").empty();
	}
	BUSCARMOVS.cargaListaMovimDisp();
	// Vacio el input de filtro
	$('#filter').val('').focus();
});

// Click a ver mas visitas link
$('#btnActualizar').click(function (event) {
	// Vacio el body de la tabla
	$("#bodyTablaModalBuscar").empty();
	// Para que cargue nueva lista
	BUSCARMOVS.listaCargada = false;
	// Vuelvo a cargar la lista
	BUSCARMOVS.cargaListaMovimDisp();
	// Vacio el input de bucar y le doy foco
	$('#filter').val('').focus();
});
