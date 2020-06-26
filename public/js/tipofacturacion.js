//
// Código JS
// 

$.validator.setDefaults({

	submitHandler: function () {
		//alert( "submitted!" );
		$('#btnConfirma').prop({disabled: true});
		$('#spinner').removeClass('invisible').addClass('visible');
		document.getElementById("formTiposFact").submit(); 
	}

});

TIPOFACT.mensajeError = function (elem, mens) {
	$(elem).addClass('is-invalid');
	$("<div id='error' class='invalid-feedback'>" + mens + "</div>").insertAfter( elem );
	$(elem).focus();
};


//
// Código jQuery
// 
$(document).ready(function(){

	// Set focus to select id="selectProducto"
	$("input#descripcion").focus();

	$('input#importe').inputmask("numeric", {
		radixPoint: ",",
		groupSeparator: ".",
		digits: 2,
		autoGroup: true,
		//prefix: '$ ', //Space after $, this will not truncate the first character.
		rightAlign: false,
		unmaskAsNumber: true, 
		oncleared: function () { self.value(''); }
	});

	// Si el div clase mensaje existe...
	if ($("div.mensaje").length > 0) {
		// Hace desaparecer el div con la línea del mensaje
        $('div.mensaje').delay(3000).fadeOut('slow');
	};

	// Boton del Modal para seleccionar tipo fact
	$('button#btnSelecTipo').click(function(){
		let idTipo = $('select#selectTipoFact').val();
		$('#modalBuscar').modal('hide');

		// BUSCA DATOS POR AJAX
		$.getJSON( TIPOFACT.pathDataTipoFact, { idtipo: idTipo } )

		  	.done(function( json ) {
		    	//console.log( "JSON Data: " + json.Descripcion );
		    	$('input#idtipo').val(json.Id);
		    	$('input#descripcion').val(json.Descripcion);
			    $('input#importe').val(json.MontoAbono);

			    $('li#accion').html('Modificar');
			    $('li#linkBorrar').removeClass('disabled');
			    $('li#linkBorrar').addClass('active');
		    	$('a#linkBorrar').removeAttr('disabled');
		    })

		    .fail(function( jqxhr, textStatus, error ) {
		    	let err = textStatus + ", " + error;
		    	console.log( "Request Failed: " + err );
		    	alert('Error al buscar tipo de facturación !');
		});
    });				

	// Accion de Borrar del navbar
	$('a#linkBorrar').click(function(){
		if ( $('a#linkBorrar').attr('disabled') == undefined ) {
		 	let idTipo = $('#idtipo').val();
		 	let descrp = $('#descripcion').val();

		 	$.confirm({
		 		columnClass: 'medium',
				title: '<h5 class="text-danger"><strong>Confirmar !!</strong></h5>',
				content: '\n¿ Elimina tipo facturación: <strong>' + descrp + '</strong> ?\n',
		        type: 'red',
		        typeAnimated: true,
		        closeIcon: true,
				buttons: {
					confirma: {
                		text: 'Confirma',
                		btnClass: 'btn-red',
						action: function () {
							location.assign(TIPOFACT.pathEliminaTipoFact + "?idtipo=" + idTipo);
						}
					},
					cancela: function () {
						// Nada sucede si cancela
					}
				}
			});
		}
	});

	// Validacion por jQuery.validator()
	$( "#formTiposFact" ).validate( {
		rules: {

			descripcion: {
				required: true,
				minlength: 3
			},

			importe: {
				required: true,
				minlength: 1
			}
		},
		messages: {

			descripcion: {
				required: "Por favor ingrese descripción !",
				minlength: "Descripción debe tener al menos 3 letras."
			},
			importe: {
				required: "Debe ingresar un importe !",
				minlength: "Importe deber al menos 2 cifras !"
			}
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


});
