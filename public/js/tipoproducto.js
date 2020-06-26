
$(document).ready(function(){

	// Set focus to select id="selectProducto"
	$("input#descripcion").focus();

	// Si el div clase mensaje existe...
	if ($("div.mensaje").length > 0) {
		// Hace desaparecer el div con la línea del mensaje
        $('div.mensaje').delay(3000).fadeOut('slow');
	};

	// Boton del Modal para seleccionar Empleado
	$('button#btnSelecTipo').click(function(){
		let idTipo = $('select#selectTipoProd').val();
		$('#modalBuscar').modal('hide');

		// BUSCA DATOS POR AJAX
		$.getJSON( global_dataTipoProd, { idtipo: idTipo } )
		  .done(function( json ) {
		    console.log( "JSON Data: " + json.Descripcion );
		    $('input#idtipo').val(json.Id);
		    $('input#descripcion').val(json.Descripcion);
		    $('li#accion').html('Modificar');
		    $('li#linkBorrar').removeClass('disabled');
		    $('li#linkBorrar').addClass('active');
		    $('a#linkBorrar').removeAttr('disabled');
		  })
		  .fail(function( jqxhr, textStatus, error ) {
		    let err = textStatus + ", " + error;
		    console.log( "Request Failed: " + err );
		});
    });				

	// Accion de Borrar del navbar
	$('a#linkBorrar').click(function(){
		if ( $('a#linkBorrar').attr('disabled') == undefined ) {
		 	let idTipo = $('#idtipo').val();
		 	let descrp = $('#descripcion').val();

		 	$.confirm({
		 		columnClass: 'medium',
				title: 'Confirmar !',
				content: '\n¿ Elimina tipo producto: <strong>' + descrp + '</strong> ?\n',
				buttons: {
					confirma: function () {
						location.assign(global_eliminarTipoProd + "?idtipo=" + idTipo);
					},
					cancela: function () {
						// Nada sucede si cancela
					}
				}
			});
		}
	});

	// Valida submit si la Descripcion está escrita
	$('form').submit(function (event) {
		let descrip = $('#descripcion').val();

		// Si la descripcion es vacia...
		if (descrip.trim() === '' ) {
				$('input#descripcion').addClass('is-invalid');
				$('<div id="agregado" class="invalid-feedback">Debe ingresar descripción.</div>').insertAfter('input#descripcion');
				$('input#descripcion').focus();
				event.preventDefault();
				return false;
		}

		// Finalmente si está todo Ok... Enviar el form !
		$('#btnConfirma').prop({disabled: true});
		$('a#btnCancela').addClass('disabled');
		$('div#enviando').css('display','initial');
	});

});
