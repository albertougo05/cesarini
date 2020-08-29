//
// Funciones para el modal de búsqueda de Visitas
// 

BUSCARVIS = {

	// Flag para cargar lista por única vez
	listaCargada: false,

	// Arma linea de la tabla
	lineaBody: function (visita) {
		let lin = "<tr class='linTablaBuscaVisita' " + "onclick='BUSCARVIS.clickBuscVis(this);'>";
		lin += "<td class='cellRight'>" + visita.id + "</td>";
		lin += "<td>" + this.convertirFecha( visita.fecha ) + "</td>";
		lin += "<td>" + visita.diasem + "</td>";
		lin += "<td>" + visita.turno + "</td>";
		lin += "<td class='filter-cell'>" + visita.empleado  +"</td>";
		lin += "<td class='cellRight'>" + visita.salida.substring(0,5) + "</td>";
		lin += "<td class='cellRight'>" + visita.retorno.substring(0,5) + "</td>";
		lin += "<td><div class='custom-control custom-checkbox d-flex align-items-center flex-column'>";
		lin += "<input type='checkbox' class='custom-control-input' id='pendiente'";

		if (visita.pendiente == 1) {
			lin += " checked";
		}

		lin += " disabled>";
		lin += "<label class='custom-control-label' for='pendiente'></label>";
		lin += "</div></td></tr>";

		return lin;
	},

	// Evento click para seleccionar y redireccionar al seleccionar Visita
	clickBuscVis: function (elem) {
		let idVis = $(elem).find('td:first').html();
		// Redirecciona a mostrar visita seleccionada
		location.assign( VISITAS.pathVisitaConId + "?idvisita=" + idVis );
	},

	// Funcion carga lista visitas
	cargaListaVisitas: function () {
		let lin, 
		    desde = $('#fechaDesde').val(), 
		    hasta = $('#fechaHasta').val();

		$.get( VISITAS.pathListaVisitas, {desde: desde, hasta: hasta}, function( data ) {

			$.each(data, function(index, item) {
	            lin = BUSCARVIS.lineaBody(item);
	            $( "#bodyTablaModalVisitas" ).append( lin );
	        }); 

			// Cuando se cargó la lista por primera vez...
			BUSCARVIS.listaCargada = true;
		}, "json" );
	},

	// Funcion convierte fecha
	convertirFecha: function (fecha) {

		return fecha.split("-").reverse().join("/");
	},

};



// Filtrado de tabla buscar Visita
$('#filter-2-container').tableFilter({ tableID: '#tablaBuscarVisita', 
                               filterID: '#filter',
                               filterCell: '.filter-cell',
	                           autofocus: true});

// Al cargar el modal, busca via ajax los datos
$('#modalBuscaVisita').on('show.bs.modal', function (event) {

	if ( BUSCARVIS.listaCargada ) {
		// Si está cargada la lista...
		$( "#bodyTablaModalVisitas" ).empty();
	}

	BUSCARVIS.cargaListaVisitas();
	// Vacio el input de filtro
	$('input#filter').val('').focus();
});

// Click a ver mas visitas link
$('#btnActualizar').click(function (event) {

	$( "#bodyTablaModalVisitas" ).empty();
	// Vuelvo a cargar la lista
	BUSCARVIS.cargaListaVisitas();
	$('input #filter').val('').focus();
});

// Click boton Seleccionar por Id
$('#btnSelecPorId').click(function(event) {
	const idVis = parseInt( $('#inpSelId').val() ); 	// Convierto a número
	//console.log('Seleccionó: ' + idVis);

	if ( idVis > 0 ) {
		// Redirecciona a mostrar visita seleccionada
		location.assign( VISITAS.pathVisitaConId + "?idvisita=" + idVis );

	} else $('#inpSelId').val('');		// Si no es número, vacia el input
});
