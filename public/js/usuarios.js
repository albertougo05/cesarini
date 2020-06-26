// usuarios.js

$.validator.setDefaults( {
	submitHandler: function (form) {

		$('#btnConfirma').prop({disabled: true});
		$('a#btnCancela').addClass('disabled');
		$('div#enviando').css('display','initial');

		form.submit();
	}
} );



$(document).ready(function(){

	// Set focus to select id="selectProducto"
	$("input#usuario").focus();

	// Si el div clase mensaje existe...
	if ($("div.mensaje").length > 0) {
		// Hace desaparecer el div con la línea del mensaje
        $('div.mensaje').delay(5000).fadeOut('slow');
	};

	// Para boton menú Buscar lanza el Modal para buscar usuario
	$('button#btnSelecUser').click(function(){
		let idUser = $('select#selectUser').val();
		$('#modalUsers').modal('hide');

		//console.log('Id de usuario: ' + $idUser );
		location.assign(global_datausuario + "?iduser=" + idUser);
    });				

	// Accion de Borrar usuario
	$('a#linkBorrar').click(function(){
		if ( $('a#linkBorrar').attr('disabled') == undefined ) {
		 	var idUser = $('#iduser').val();
		 	var nomUser = $('#usuario').val();

		 	$.confirm({
		 		columnClass: 'medium',
				title: 'Confirmar !',
				content: '\n¿ Elimina a usuario: <strong>' + nomUser + '</strong> ?\n',
				buttons: {
					confirma: function () {
    					location.assign(global_eliminar + "?iduser=" + idUser);
					},
					cancela: function () {
						// Nada sucede si cancela
					}
				}
			});
		}
	});

	// Verificar si el usuario ya existe en la BD
	$('input#usuario').focusout(function() {
		usuario = $(this).val();

		if (global_accion === 'Nuevo') {
			// busca por AJAX
			$.getJSON( global_usuariodisponible, { nombreUsuario: usuario } )
			  .done(function( json ) {
			    console.log( "JSON Data: " + json.existe );
			    // Si existe el usuario...
			    if (json.existe) {
			    	alert('El usuario ya EXISTE ! \r\nCambie el nombre de usuario');
			    	$('input#usuario').val('').focus();
			    }
			  })
			  .fail(function( jqxhr, textStatus, error ) {
			    var err = textStatus + ", " + error;
			    console.log( "Request Failed: " + err );
			    alert('Fallo al validar nombre de usuario...');
			});
	    }
  	});

	// Validacion de campos con jQuery Validation plugin
	$( "#userForm" ).validate( {
		rules: {
			usuario: {
				required: true,
				minlength: 4
			},
			contrasena: {
				required: true,
				minlength: 4
			},
			confirma: {
				required: true,
				minlength: 4,
				equalTo: "#contrasena"
			}
		},
		messages: {
			usuario: {
				required: "Por favor ingrese nombre de usuario",
				minlength: "Usuario debe tener al menos 4 letras"
			},
			contrasena: {
				required: "Por favor ingrese una contraseña",
				minlength: "Contraseña debe tener al menos 4 caracteres"
			},
			confirma: {
				required: "Por favor repita la contraseña",
				minlength: "Contraseña debe tener al menos 4 caracteres",
				equalTo: "Repita la misma contraseña"
			},
		},
		errorElement: "em",
		errorPlacement: function ( error, element ) {
			// Add the `invalid-feedback` class to the error element
			error.addClass( "invalid-feedback" );

			if ( element.prop( "type" ) === "checkbox" ) {
				error.insertAfter( element.next( "label" ) );
			} else {
				error.insertAfter( element );
			}
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
		},
		unhighlight: function (element, errorClass, validClass) {
			$( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
		}
	} );



/*
	$('form#user').submit(function (event) {
		// Si está todo bien, envia datos y muestra cartel 'Enviando...'
		$('#btnConfirma').prop({disabled: true});
		$('a#btnCancela').addClass('disabled');
		$('div#enviando').css('display','initial');

		event.preventDefault();

	});
*/

});