// Variables globales
var _validNroSerie = false;
var _validNroInterno = false;


// Funciones para jQuery:

function validaInputConAjax(elemento, link) {
	let name = elemento.attr('name');

//console.log('Valor de ' + name + ': ' + elemento.val());

	$.getJSON( link, { valor: elemento.val() } )
	    .done(function( json ) {
		    //console.log( "Status para " + name + ": " + json.status );
		    // Si el número existe...
		    if (json.status) {
		    	elemento.removeClass('is-valid').addClass('is-invalid');
		    	$("<div id='" + name + "Error' class='invalid-feedback'>El valor ya existe.</div>").insertAfter('input#' + name);

				// Si el valor existe pongo en true el error..
				if (name == 'nroSerie') {
					_validNroSerie = true;
				} else if (name == 'nroInterno') {
					_validNroInterno = true;
				}
		    } else {
		    	// 
				if (name == 'nroSerie') {
					_validNroSerie = false;
				} else if (name == 'nroInterno') {
					_validNroInterno = false;
				}
		    }
	    })
	    .fail(function( jqxhr, textStatus, error ) {
	    	let err = textStatus;
	    	alert('No se puede validar '+ name +' por el momento...' + '\nError: ' + err);
	    	console.log( "Request Failed: " + err );

			if (name == 'nroSerie') {
				_validNroSerie = true;
			} else if (name == 'nroInterno') {
				_validNroInterno = true;
			} 
	});

	return null;  // Devuelve true si existe registro idéntico
}

function ocultaInputError(elemento) {
	let name = elemento.attr('name');

	if (elemento.val() != '') {
		let divError = $('div#' + name + 'Error');

		if (divError.length == 1) {
			elemento.removeClass('is-invalid').addClass('is-valid');
			divError.remove();

			let divError2 = $('div#existe' + name + 'Error');
			if (divError2.length == 1) {
				$(divError2).remove();
			}
		}
	}
}

function errorEnInput(elemento, tipo = 'input') {
	let error = false;
	let name = elemento.attr('name');
	let valInput = elemento.val();
	let divError = 'div#' + name + 'Error';

	if (valInput.trim() === '' || valInput.trim() == 0 ) {
		// Verifica que el div no exista (0) para agregarlo (puede existir de previa validación...)
		if ( $(divError).length != 1 ) {
			elemento.removeClass('is-valid').addClass('is-invalid');
			$("<div id='" + name + "Error' class='invalid-feedback'>Debe ingresar un valor.</div>").insertAfter(tipo + '#' + name);
		}
		error = true;
	}

	return error;
}


// Codigo jQuery:
// 
$(document).ready(function(){
    // Boton de ir arriba
    $('#scrollUp').click( function () {
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

	// Set focus to select id="nroSerie"
	$("input#nroSerie").focus();

	// Si el div clase mensaje existe...
	if ($("div.mensaje").length > 0) {
		// Hace desaparecer el div con la línea del mensaje
        $('div.mensaje').delay(3000).fadeOut('slow');
	};

	// Accion de Borrar del navbar
	$('a#linkBorrar').click(function(){
		if ( $('a#linkBorrar').attr('disabled') == undefined ) {
		 	let idDisp = $('#id').val();
		 	let modelo = $('#modelo').val();

		 	$.confirm({
		 		columnClass: 'medium',
		 		type: 'red',
				title: '<h4 class="text-danger"><strong>Confirmar !</strong></h4>',
				content: '\n¿ Elimina dispenser: <strong>' + modelo + '</strong> ?\n',
				buttons: {
					confirma: {
						btnClass: 'btn-danger',
					 	action: function () {
							location.assign(global_eliminaDisp + "?id=" + idDisp);
						}
					},
					cancela: function () {
						// Nada sucede si cancela
					}
				}
			});
		}
	});

// QUITO VALIDACION DE NROSERIE
/* 
	// Quita mensaje de error si cambia nro serie y luego valida la existencia en DB
	$('input#nroSerie').change(function() {
		ocultaInputError($(this));
		validaInputConAjax($(this), global_validaSerie);
	});
*/

	// Quita mensaje de error si cambia nro interno y luego valida la existencia en DB
	$('input#nroInterno').change(function() {
		ocultaInputError($(this));
		validaInputConAjax($(this), global_nroInterno);
	});

	// Quita mensaje de error si cambia la opcion 0
	$('select#selectTipo').change(function() {
		if ($(this).val() != 0) {
			if ($('div#selectTipoError').length == 1) {
				$(this).removeClass('is-invalid').addClass('is-valid');
				$('div#selectTipoError').remove();
			}
		}
	});

	// Quita mensaje de error si cambia modelo
	$('input#modelo').change(function() {
		ocultaInputError($(this));
	});

	// Quita mensaje de error si cambia fecha alta
	$('input#fechaAlta').change(function() {
		ocultaInputError($(this));
	});

	// Valida submit del form
	$('form').submit(function (event) {
		let errores = [];
		let cantArr = 0;

// QUITO VALIDACION DE NROSERIE Y NROINTERNO...
/*
		// Si numero de serie es vacio...
		errores.push( errorEnInput($('input#nroSerie')) );
		// Si no es vacio, comprueba si el número de serie está repetido, via ajax
		if (errores[0] === false) {
			errores.push( _validNroSerie );
		}
*/

		// Validar Número Interno
		cantArr = errores.push( errorEnInput($('input#nroInterno')) );
		// Si no es vacio, comprueba si el número interno está repetido, via ajax
		if (errores[cantArr - 1] === false) {
			errores.push( _validNroInterno );
		} 

		// Validar selectTipoDisp que no sea 0
		errores.push( errorEnInput($('select#selectTipo'), 'select') );
		// Validar modelo que no sea vacia
		errores.push( errorEnInput($('input#modelo')) );
		// Validar fecha de alta que no sea vacia
		errores.push( errorEnInput( $('input#fechaAlta')) );
		// fechaUltService y fechaBaja NO tienen validación. Pueden ser vacio.
		// Si en el array errores hay algun true (error), devuelve el indice, si no es -1
		let indexErr = errores.indexOf(true);

		// Si hay errores NO se envía el form
		if (indexErr != -1) {
			$('input#nroSerie').focus();
			event.preventDefault();
			return false;
		}

		// Finalmente si está todo Ok... Enviar el form !
		$('#btnConfirma').prop({disabled: true});
		$('a#btnCancela').addClass('disabled');
		$('div#enviando').css('display','initial');
	});




});  // End (document).ready()
