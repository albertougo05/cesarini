// Javascript
const _setupAutonum = {
    allowDecimalPadding: "floats",
    decimalCharacter: ",",
    decimalCharacterAlternative: ".",
    digitGroupSeparator: ".",
    emptyInputBehavior: 'null'
};

// Inicialization input Precio y excedente
var _inputPrecio  = new AutoNumeric('#precio', _setupAutonum);
var _precioExeced = new AutoNumeric('#precioExced', _setupAutonum);

// Funciones 

// Agrega mensaje de arror bajo el input
function _mensajeError(elemento, tipo, mensaje) {
	let name = elemento.attr('id');
	let divError = 'div#' + name + 'Error';

	// Verifica que el div no exista (0) para agregarlo (puede existir de previa validación...)
	if ( $(divError).length != 1 ) {
		elemento.addClass('is-invalid');
		$("<div id='" + name + "Error' class='invalid-feedback'>" + mensaje + "</div>").insertAfter(tipo + '#' + name);
	}

	return null;
}

//
// Código jQuery
//
$(document).ready(function(){

	// Set focus to select id="selectProducto"
	$("input#descripcion").focus();

	// Si el div clase mensaje existe...
	if ($("div.mensaje").length > 0) {
		// Hace desaparecer el div con la línea del mensaje
        $('div.mensaje').delay(3000).fadeOut('slow');
	};

	// Boton del Modal para seleccionar Producto
	$('button#btnSelecProd').click(function(){
		let idProd = $('select#selectProducto').val();
		$('#modalBuscar').modal('hide');
		// BUSCA DATOS VIA AJAX
		$.getJSON( global_dataProducto, { idprod: idProd } )
		    .done(function( json ) {
			    //console.log( "JSON Data: " + json.Descripcion );
			    $('input#idprod').val(json.Id);
			    $('input#descripcion').val(json.Descripcion);
			    $('select#selectTipoProd').val(json.IdTipoProducto);
			    $('select#selectTipoProd').trigger('change');
			    $('input#presentacion').val(json.Presentacion);
			    //$('input#precio').val(json.Precio);
			    _inputPrecio.set(json.Precio);
			    _precioExeced.set(json.PrecioExcedente);
			    if (json.ConStock === 1) {
			    	$('input#constock').prop('checked', true).val('1');
			    }
			    $('li#accion').html('Modificar');
			    $('li#linkBorrar').removeClass('disabled');
			    $('li#linkBorrar').addClass('active');
			    $('a#linkBorrar').removeAttr('disabled');
			    $('input#descripcion').focus();
		    })
		    .fail(function( jqxhr, textStatus, error ) {
		    	let err = textStatus + ", " + error;
		    	alert('Datos no disponibles por el momento...' + '\nError: ' + err);
		    	console.log( "Request Failed: " + err );
		});
    });				

	// Accion de Borrar del navbar
	$('a#linkBorrar').click(function(){
		if ( $('a#linkBorrar').attr('disabled') == undefined ) {
		 	let idProd = $('#idprod').val();
		 	let descrp = $('#descripcion').val();

		 	$.confirm({
		 		columnClass: 'medium',
				title: 'Confirmar !',
				content: '\n¿ Elimina producto: <strong>' + descrp + '</strong> ?\n',
				buttons: {
					confirma: function () {
						location.assign(global_eliminaProd + "?idprod=" + idProd);
					},
					cancela: function () {
						// Nada sucede si cancela
					}
				}
			});
		}
	});

	// Quita mensaje de error si cambia la opcion 0
	$('select#selectTipoProd').change(function() {
		if ($(this).val() != 0) {
			if ($('div#selectTipoProdError').length == 1) {
				$(this).removeClass('is-invalid').addClass('is-valid');
				$('div#selectTipoProdError').remove();
			}
		}
	});

	$('input').change(function () {
		if ($(this).val() != '') {
			$(this).removeClass('is-invalid').addClass('is-valid');
		}
	});

	// Valida submit si la Descripcion está escrita
	$('form').submit(function (event) {
		let descrip = $('#descripcion').val();
		let hayError = false;

		// Si la descripcion es vacia...
		if (descrip.trim() === '' ) {
			_mensajeError($('input#descripcion'),'input', 'Debe ingresar descripción.');
			hayError = true;
		}
		// Validar selectTipoProd que no sea 0
		if ($('select#selectTipoProd').val() == 0) {
			_mensajeError($('select#selectTipoProd'), 'select', 'Ingrese tipo de producto.');
			hayError = true;
		}
		// Validar presentacion que no sea vacia
		if ($('input#presentacion').val().trim() === '') {
			_mensajeError($('input#presentacion'), 'input', 'Debe ingresar presentación.'); 
			hayError = true;
		}

		// Precio NO tiene validación. Puede ser 0 o vacio.
		// Si hay errores NO se envía el form
		if (hayError) {
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
