/* jshint esversion: 6 */



// Para comprobar si la validacion fue rechazada (false)
CLIENTE.cuilDniOk = false;
CLIENTE.initCuilDni = '';

//
/** Funcionamiento boton UpScroll  **/
// 
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


// Funciones javascript
//

// Si un string es vacio
CLIENTE.isEmpty = function (str) {
    return (!str || 0 === str.length);
}

// Valida email
CLIENTE.emailIsValid = function (email) {
  return /\S+@\S+\.\S+/.test(email)
}

// Setup Autonumeric.js
CLIENTE.setupAutonum = {
    allowDecimalPadding: "floats",
    decimalCharacter: ",",
    decimalCharacterAlternative: ".",
    digitGroupSeparator: "."
};

// Inicialization input CostoAbono
CLIENTE.CostoAbono = new AutoNumeric('#CostoAbono', CLIENTE.setupAutonum);



//
// Funciones para jQuery
//

CLIENTE.errorEnInput = function (elemento, tipo, mensaje) {
	let error = false;
	let name = elemento.attr('name');
	let valInput = elemento.val();
	let divError = 'div#' + name + 'Error';

	// Verifica que el div no exista (0) para agregarlo (puede existir de previa validación...)
	if ( $(divError).length != 1 ) {
		elemento.removeClass('is-valid').addClass('is-invalid');
		$("<div id='" + name + "Error' class='invalid-feedback'>" + mensaje + "</div>").insertAfter(tipo + '#' + name);
		error = true;
	}

	return error;
}

CLIENTE.ocultaInputError = function (elemento) {
	var name = elemento.attr('name');

	if (elemento.val() != '') {
		var divError = $('div#' + name + 'Error');

		if (divError.length == 1) {
			elemento.removeClass('is-invalid').addClass('is-valid');
			divError.remove();
		}
	}
}

CLIENTE.comprobarCuilDni = function (valCuil) {
	// busca via AJAX
	$.getJSON( CLIENTE.pathcomprobarcuitdni, { CUIT: valCuil } )
	  	.done(function( json ) {
	    	//console.log( "JSON Data: " + json.existe );
	    	// Si existe el usuario...
	    	if (json.existe) {
	    		CLIENTE.errorEnInput($('input#CUIT'),'input', 'Cuil/Dni ya EXISTE en otro cliente !!');
	    		$('input#CUIT').focus();
	    		CLIENTE.cuilDniOk = false;
	    		return false;
	    	}
	  	})
	  	.fail(function( jqxhr, textStatus, error ) {
	    	var err = textStatus + ", " + error;
	    	console.log( "Request Failed: " + err );
	    	alert('Fallo al validar cuit/dni de cliente...');
	    	return false;
		});
}



//
// Codigo jQuery
//

$(document).ready( function() {

	if (CLIENTE.pathaccion === 'Modifica') {
		CLIENTE.initCuilDni = $('input#CUIT').val();
		CLIENTE.cuilDniOk = true;
	}

    // Cargo el campo Observaciones
    if (CLIENTE.observaciones != '') {
    	//$('textarea#Ovservaciones').val(CLIENTE.observaciones);
    	let textObs = document.getElementById("Observaciones");
    	const texto = document.createTextNode(CLIENTE.observaciones);

    	textObs.appendChild(texto);
    	console.log(CLIENTE.observaciones);
    }

	// Si el div clase mensaje existe...
	if ($("div.mensaje").length > 0) {
		// Hace desaparecer el div con la línea del mensaje
        $('div.mensaje').delay(3000).fadeOut('slow');
	}

/**
	// Mascara para input precio
	$('input#CostoAbono').inputmask("numeric", {
		radixPoint: ",",
		groupSeparator: ".",
		digits: 2,
		autoGroup: true,
		//prefix: '$ ', //Space after $, this will not truncate the first character.
		rightAlign: false,
		unmaskAsNumber: true, 
		oncleared: function () { self.Value(''); }
	});
*/

	// Accion de Borrar del navbar
	$('a#linkBorrar').click(function(){
		if ( $('a#linkBorrar').attr('disabled') == undefined ) {
		 	var idClie = $('#Id').val();
		 	var nomClie = $('#ApellidoNombre').val();

		 	$.confirm({
		 		columnClass: 'medium',
		 		type: 'red',
				title: '<h4 class="text-danger"><strong>Confirmar !</strong></h4>',
				content: '\n¿ Elimina cliente: <strong>' + nomClie + '</strong> ?\n',
				buttons: {
					confirma: {
						btnClass: 'btn-danger',
						action: function () {
							location.assign(CLIENTE.patheliminarCliente + "?Id=" + idClie);
						}
					},
					cancela: function () {
						// Nada sucede si cancela
					}
				}
			});
		}
	});

	// Valido campos del Form1
	$('form#form1 input').focusout(function (event) {
		var valor = $(this).val();
		var divError = 'div#' + this.name + 'Error';
		var inputName = this.name;

		var noValidar = (this.name != 'Telefono' && this.name != 'Celular' && this.name != 'Email' && 
			             this.name != 'NombreFantasia' && this.name != 'NroContrato' && this.name != 'FechaVencContrato' &&
			             this.name != 'FechaAltaServicio' );

		if (noValidar) {
			if (CLIENTE.isEmpty(valor)) {
				//console.log('Valor vacio..');
				CLIENTE.errorEnInput($(this), 'input', inputName + ' no puede estar vacío.');
				$(this).focus();
			} else {
				//console.log('Valor: ' + valor);
				CLIENTE.ocultaInputError($(this));
			}
		}
	});

	// Valido email
	$('input#Email').focusout(function (event) {
		var valor = $(this).val();

		if (!CLIENTE.isEmpty(valor)) {  // Si el valor NO es vacio...
			if (CLIENTE.emailIsValid(valor)) {
				// Si email es valido...
				CLIENTE.ocultaInputError($(this));
			} else {
				// Si email NO es válido...
				CLIENTE.errorEnInput($(this), 'input', 'El email NO es válido.');
				$(this).focus();
			}
		} else {
			// Si email es vacio, saco error por las dudas
			CLIENTE.ocultaInputError($(this));
		}
	});

	// Convierto cuit con guiones o dni con puntos de miles
	$('input#CUIT').change( function (event) {
		let valor = $(this).val();
		let sonNum = valor.match(/\d|[.-]/g);

		// Verifica si hay letras
		if (sonNum != null) {
			valor = sonNum.toString();
			// saco los puntos, guiones y comas (del array)
			valor = valor.replace(/\,|\.|\-/g, '');
			let cantDigit = valor.length;

			if (cantDigit === 7 || cantDigit === 8 || cantDigit === 11) {
				// Oculta mensaje error si existe
				CLIENTE.ocultaInputError($(this));
				if (cantDigit === 11) {
					// cuit
					valor = valor.slice(0, -9) + '-' + valor.slice(-9, -1) + '-' + valor.slice(-1);
				} else {
					// dni
					valor = valor.slice(0, -6) + '.' + valor.slice(-6, -3) + '.' + valor.slice(-3);
				}
				$(this).val(valor);
				CLIENTE.cuilDniOk = true;

				// Si se cambió el cuil/dni inicial...
				if (CLIENTE.pathaccion === 'Modifica' && valor != CLIENTE.initCuilDni) {

					CLIENTE.comprobarCuilDni(valor);
				}

				return false;

			} else {

				$(this).val('');
			}
		}
		CLIENTE.errorEnInput($('input#CUIT'),'input', 'Debe ingresar CUIL o DNI.');
		$(this).focus();
	});

// Poner flag en .change para volver a verificar si existe  el cuil/dni !!

	// Verificar que no exista el cuil/dni en BD
	$('input#CUIT').focusout(function(event) {
		var valCuit = $(this).val();

		if (CLIENTE.pathaccion === 'Nuevo') {

			CLIENTE.comprobarCuilDni(valCuit);

		}
	});

	// Si cambia 
	$('select#CondicionFiscal').change(function () {
		let condicFisc = $('select#CondicionFiscal').val();
		let valCuit = $('input#CUIT').val();

		CLIENTE.ocultaInputError($('input#CUIT'));

		// Si es MonoTributo o RI agrega error para ingresar cuit
		if (condicFisc === 'Monotributo' || condicFisc === 'Resp. Inscripto'  || condicFisc === 'Exento') {
			// Si el cuit es dni...
			if (valCuit.includes('.')) { //
				CLIENTE.errorEnInput($('input#CUIT'),'input', 'Debe ingresar número de Cuil.');
				$('input#CUIT').focus();
			}
		}
		if (condicFisc === 'Consumidor final' && valCuit.includes('-')) { 
			CLIENTE.errorEnInput($('input#CUIT'),'input', 'Debe ingresar número de D.N.I.');
			$('input#CUIT').focus();
		}
	});

	// Abro MODAL para otros domicilios
	$("button#btnAgregarDom").click(function(){
		// Poner en blanco los campos al inicio
		$('#IdDom').val('');
		$('#Direccion2').val('');
		$('#Localidad2').val('Jesús María');
		$('#Provincia2').val('Córdoba');
		$('#CodPostal2').val('5220');
		$('#Telefono2').val('');
		$('#Celular2').val('');
		$('#Contacto').val('');

		$('#modalOtroDom').modal('show');
		// el modal salva los datos y recarga la pagina que va a mostrar cards de domicilios
    });

	// focus en primer input del modal
	$('#modalOtroDom').on('show.bs.modal', function (e) {

	  	$('input#Direccion2').focus();
	});

	// Cuando se oculta el modal, cambio el titulo al original
	$('#modalOtroDom').on('hidden.bs.modal', function (e) {

	  	$('#tituloModalOtroDom').html('<strong>Nuevo domicilio:</strong>');
	});

	// Boton Editar 1 del Card de Otros domicilios
	$('div.card-body button').click(function (event) {
		var idDom = $(event.target).attr('data-id');
		var nroDom = $(event.target).attr('data-nro');
		var indexIdDom = 0;

		CLIENTE.arrDoms.forEach(function (item, index) {
			// Si en item 0 es el idDom...
			if (item[0] == idDom) {
				indexIdDom = index;
			}
		});

		// Cargar el Id al form del modal + datos del domicilio
		$('#IdDom').val(CLIENTE.arrDoms[indexIdDom][0]);
		$('#Direccion2').val(CLIENTE.arrDoms[indexIdDom][1]);
		$('#Localidad2').val(CLIENTE.arrDoms[indexIdDom][2]);
		$('#Provincia2').val(CLIENTE.arrDoms[indexIdDom][3]);
		$('#CodPostal2').val(CLIENTE.arrDoms[indexIdDom][4]);
		$('#Telefono2').val(CLIENTE.arrDoms[indexIdDom][5]);
		$('#Celular2').val(CLIENTE.arrDoms[indexIdDom][6]);
		$('#Contacto').val(CLIENTE.arrDoms[indexIdDom][7]);
		$('#tituloModalOtroDom').html('<strong>Edita domicilio #' + nroDom + ' </strong>');

		// Lanzo el Modal
		$('#modalOtroDom').modal('show');
	});

	// Boton Eliminar domicilio del Card de Otros Domicilios
	$('div.card-body a').click(function (event) {
		var idDom = $(event.target).attr('data-id');
		var idClie = $('input#Id').val();
		var nroDom = $(event.target).attr('data-nro');
		var direccion = CLIENTE.arrDoms[nroDom - 2][1]

	 	$.confirm({
	 		columnClass: 'medium',
	 		type: 'red',
			title: '<h4 class="text-danger"><strong>Confirmar !</strong></h4>',
			content: '<strong>¿ Elimina domicilio #' + nroDom + ' ?</strong><br/><em>&nbsp;&nbsp;&nbsp;' + direccion + '</em>',
			buttons: {
				confirma: {
					btnClass: 'btn-danger',
					action: function () {
						location.assign(CLIENTE.patheliminardomicilio + "?IdDom=" + idDom + '&Id=' + idClie);
					}
				},
				cancela: function () {
					// Nada sucede si cancela
				}
			}
		});

	});

	// Cada vez que el input cambie...
	$('form#formDom2 input').focusout(function (event) {
		var valor = $(this).val();
		var divError = 'div#' + this.name + 'Error';
		var inputName = this.name.substr(0, this.name.length - 1);
		var noTelCelCon = (this.name != 'Telefono2' && this.name != 'Celular2' && this.name != 'Contacto');

		if (noTelCelCon) {
			if (CLIENTE.isEmpty(valor)) {
				//console.log('Valor vacio..');
				CLIENTE.errorEnInput($(this), 'input', inputName + ' no puede estar vacío.');
				$(this).focus();
			} else {
				//console.log('Valor: ' + valor);
				CLIENTE.ocultaInputError($(this));
			}
		}
	});

	// Al seleccionar Tipo de facturación
	$('select#IdTipoFact').change(function(event) {
		//console.log('Importe: ' + $(this).find(":selected").attr("data-importe"));
		// Paso el valor del abono
		//$('input#CostoAbono').val( $(this).find(":selected").attr("data-importe") );
		CLIENTE.CostoAbono.set( $(this).find(":selected").attr("data-importe") );
	});

	// Submit del form 1
	$('form#form1').submit(function(event) {
		var valCuit = $('input#CUIT').val();
		var valCondF = $('select#CondicionFiscal').val();
		var apelNomb = $('input#ApellidoNombre').val();
		var direccion = $('input#Direccion').val();
		var codPost = $('input#CodPostal').val();

		// Valida Cuit/Dni
		if (valCuit.includes('.') && valCondF != 'Consumidor final' ) {
			CLIENTE.errorEnInput($('input#CUIT'),'input', 'Ingrese CUIT por ser ' + valCondF);
			event.preventDefault();
			$('input#CUIT').focus();
			return false;
		}

		// No permite campos en blanco de...
		if ( CLIENTE.isEmpty(apelNomb) || CLIENTE.isEmpty(direccion) || CLIENTE.isEmpty(codPost) || CLIENTE.isEmpty(valCuit) ) {

			event.preventDefault();

			if (CLIENTE.isEmpty(apelNomb)) {
				CLIENTE.errorEnInput($('input#ApellidoNombre'),'input', 'Ingrese Apellido Nombre ');
			} 
			if (CLIENTE.isEmpty(direccion)) {
				CLIENTE.errorEnInput($('input#Direccion'),'input', 'Ingrese dirección');
			} 
			if (CLIENTE.isEmpty(codPost)) {
				CLIENTE.errorEnInput($('input#CodPostal'),'input', 'Ingrese código postal');
			}
			if (CLIENTE.isEmpty(valCuit)) {
				CLIENTE.errorEnInput($('input#CUIT'),'input', 'Ingrese Cuil o DNI ');
			} 

			$('input#ApellidoNombre').focus();
			return false;
		}

		// Ver validacion cuil/dni
		if ( !CLIENTE.cuilDniOk ) {
			event.preventDefault();
			CLIENTE.errorEnInput($('input#CUIT'),'input', 'Ingrese Cuil o DNI válido');
			$('input#CUIT').focus();
			return false;
		}

		// Finalmente si está todo Ok... Submit form !
		$('#btnConfirma').prop({disabled: true});
		$('a#btnCancela').addClass('disabled');
		$('div#enviando').css('display','initial');

	});

	// Submit del form 2
	$('form#formDom2').submit(function(event) {
		var direccion = $('input#Direccion2').val();
		var localidad = $('input#Localidad2').val();
		var provincia = $('input#Provincia2').val();
		var codPost = $('input#CodPostal2').val();

		if ( CLIENTE.isEmpty(direccion) || CLIENTE.isEmpty(localidad) || CLIENTE.isEmpty(provincia)  || CLIENTE.isEmpty(codPost) ) {
			event.preventDefault();
			$('input#Direccion2').focus();
		}
	});

});
