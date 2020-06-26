
$(document).ready(function(){

	// Set focus to select id="selectProducto"
	$("input#apellidonombre").focus();

	// Si el div clase mensaje existe...
	if ($("div.mensaje").length > 0) {
		// Hace desaparecer el div con la línea del mensaje
        $('div.mensaje').delay(5000).fadeOut('slow');
	};

	// Boton del Modal para seleccionar Empleado
	$('button#btnSelecEmp').click(function(){
		var idEmp = $('select#selectEmpleado').val();
		$('#modalEmpleados').modal('hide');

		// BUSCA DATOS POR AJAX
		$.getJSON( global_dataempleado, { idempl: idEmp } )
		  .done(function( json ) {
		    //console.log( "JSON Data: " + json.ApellidoNombre );
		    $('input#idempl').val(json.Id);
		    $('input#apellidonombre').val(json.ApellidoNombre);
		    $('select#categoria').val(json.IdCategoria);   // Ver como se pone el selected
		    $('select#categ').trigger('change');
		    $('input#cuil').val(json.Cuil);
		    $('input#domicilio').val(json.Domicilio);
		    $('input#localidad').val(json.Localidad);
		    $('input#provincia').val(json.Provincia);
		    $('input#CodPostal').val(json.CodPostal);
		    $('input#telefono').val(json.Telefono);
		    $('input#celular').val(json.Celular);
		    $('input#mail').val(json.Mail);
		    $('select#estado').val(json.Estado);
		    $('select#estado').trigger('change');
		    $('li#accion').html('Modificar');
		    $('li#linkBorrar').removeClass('disabled');
		    $('li#linkBorrar').addClass('active');
		    $('a#linkBorrar').removeAttr('disabled');
		  })
		  .fail(function( jqxhr, textStatus, error ) {
		    var err = textStatus + ", " + error;
		    console.log( "Request Failed: " + err );
		    alert('Fallo al traer datos Empleado...');
		});

    });

	// Accion de Borrar del navbar
	$('a#linkBorrar').click(function(){
		if ( $('a#linkBorrar').attr('disabled') == undefined ) {
		 	var idEmpl = $('#idempl').val();
		 	var nomEmpl = $('#apellidonombre').val();

		 	$.confirm({
		 		columnClass: 'medium',
				title: 'Confirmar !',
				content: '\n¿ Da de baja empleado: <strong>' + nomEmpl + '</strong> ?\n',
				type: 'red',
				typeAnimated: true,
				buttons: {
					confirma: function () {
						location.assign(global_eliminarEmpleado + "?idempl=" + idEmpl);
					},
					cancela: function () {
						// Nada sucede si cancela
					}
				}
			});
		}
	});

	// Máscara para campo Cuil
	var cuil = document.getElementById('cuil');
	var maskOptions = {
		mask: '00-00000000-0',
		placeholderChar: '_',
		lazy: true
	};
	var mascCuil = new IMask(cuil, maskOptions);


	// Cuando hace submit...
	$('form').submit(function(event) {
		$('#btnConfirma').prop({disabled: true});
		$('a#btnCancela').addClass('disabled');
		$('div#enviando').css('display','initial');
		//event.preventDefault();
	});

});