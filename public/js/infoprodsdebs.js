//
// infovisitasresu.js
//



// Codigo jQuery:
// 
$(document).ready( function () {

	// Click boton 'Generar listado'
	$('#btnGenList').click(function (event) {
        let paramString = '?';

		paramString += 'desde=' + $('#fechaDesde').val();
		paramString += '&hasta=' + $('#fechaHasta').val();
		paramString += '&idemp=' + $('#selectEmpleado').val();

		//console.log('Params: ' + paramString);
		// Envio de datos a controller por get...
		window.open( INFO._pathInforme + paramString, '_blank');
	});


});

