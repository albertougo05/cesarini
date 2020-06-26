//
// Código JS
// 

// Constructs the suggestion engine
COMPROB.clientes = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.whitespace,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
        url: COMPROB.pathCliente + '?search=%QUERY',
        wildcard: '%QUERY'
    }
});

// Datos cliente seleccionado
COMPROB.clienteSel = {id: 0, cliente: ''}

COMPROB.datosAcliente = function (data) {
	this.clienteSel.id      = data.id;
	this.clienteSel.cliente = data.cliente;

	return null;
}

// Busca el número de comprobante según los datos de pantalla
COMPROB.buscarNroFact = function () {
	let comp = $('input:radio[name=radComp]:checked').val();
	let tipo = $("#selTipoFact option:selected").val();
	let sucur = $("#selSucursal option:selected").val();
	let dataJson;

	$.get( COMPROB.pathNroComprob, { comp: comp, tipo: tipo, sucur: sucur } )
	  	.done(function( data ) {
	  		dataJson = $.parseJSON(data);
	  		document.getElementById("nroComp").innerHTML = " - " + dataJson.nrocomp;
	  	})
  		.fail(function() {
    		console.log('Error buscando número comprobante...');
    		document.getElementById("nroComp").innerHTML = " - 00000001";
	});
}

// Mensaje de alerta
COMPROB.alerta = function (contenido, element) {
   	$.alert( {
        title: '<strong>Atención !!</strong>',
        content: "<p class='text-center'>" + contenido + "</p",
        type: 'red',
		typeAnimated: true,
		closeIcon: true,
		icon: 'fas fa-exclamation-triangle',
		buttons: {
			confirma: {
            	text: ' Ok ',
            	btnClass: 'btn-red',
            	action: function () {
					$(element).focus();
				}
            }
        }
    });
}

// Verifica si un string está vacio
COMPROB.isEmpty = function (str) {
    return (!str || 0 === str.length);
}



//
// Código jQuery
// 
$(document).ready( function () {

	$('input.importe').inputmask("numeric", {
		alias: "curency",
		radixPoint: ",",
		groupSeparator: ".",
		digits: 2,
		autoGroup: true,
		rightAlign: false,
		unmaskAsNumber: true, 
		allowPlus: false,
    	allowMinus: false,
		oncleared: function () { self.value = ''; }
	});

	// Click tipo comprobante
	$("input:radio[name=radComp]").click(function() {
		COMPROB.buscarNroFact();
	});

	// Change tipo factura (A,B,C,X)
	$('#selTipoFact, #selSucursal').change(function() {
		COMPROB.buscarNroFact();
	});

    // Initializing the typeahead with remote dataset without highlighting
    $('.typeahead').typeahead(
    	{ minLength: 3,
          highlight: true },
        { name: 'cliente',
          source: COMPROB.clientes,
          // display: 'cliente'
          display: function(item) {
          		// Setea la salida del menu de opciones...
            	return item.cliente + ' - ' + item.direccion;
           },
           limit: 10 /* Specify max number of suggestions to be displayed */
    });

	// Evento select del typeahead...
	$('.typeahead').bind('typeahead:select', function(ev, suggestion) {
	    $('input#inputBuscarCli').css({'font-weight': 'bold', 'background': '#f5f5f5', 'color': 'black'});
	    COMPROB.datosAcliente(suggestion);
	    $('#inputCliPorCod').val('');
	});

	// Evento autocomplete del typeahead...
	$('.typeahead').bind('typeahead:autocomplete', function(ev, suggestion) {
	    $('input#inputBuscarCli').css({'font-weight': 'bold', 'background': '#f5f5f5', 'color': 'black'});
	    COMPROB.datosAcliente(suggestion);
	    $('#inputCliPorCod').val('');
	});

	// On focus del input
	$('input#inputBuscarCli').focus(function (event) {
		$(this).css({'font-weight': 'normal', 'background': '#ffffff'});
		$(this).select();
	});

	// Click boton Buscar Cliente por código
	$('button#btnCliPorCodigo').click(function (event) {
		let codigo = $('#inputCliPorCod').val();

		if (codigo > 0) {
			// Busca por ajax...
		    $.ajax( { url : COMPROB.pathCodcliente,
		        	  type: "GET",
		        	  data: {codcli: codigo},
		        	  //dataType: 'json',
			          success: function(data, textStatus, jqXHR) 
			          {
			            // data: returning of data from the server
						let dataObj = $.parseJSON(data);
						// datos
						if (dataObj.status == 'ok') {
//console.log('Cliente: ' + dataObj.cliente + ' - Id dom: ' + dataObj.iddom);
							// Datos a var cliente y al input de nombre
							COMPROB.datosAcliente(dataObj);
							$('#inputBuscarCli').val(dataObj.cliente + ' - ' + dataObj.direcc);
							$('input#inputBuscarCli').css({'font-weight': 'bold', 'background': '#f5f5f5', 'color': 'black'});

						} else{

							$('#inputCliPorCod').val('');
							COMPROB.alerta('Código no encontrado !!', $('#inputBuscarCli'));
						}
		        	  },
		        	  error: function(jqXHR, textStatus, errorThrown) 
		        	  {
		            	// if fails
		          		console.log('Status: ' + textStatus);
		            	COMPROB.alerta('Error al buscar código !!', $('#inputBuscarCli'));
		        	}
		    } );
		}

	});

	// Click al boton Generar comprobante
	$('#btnGenComprob').click( function (event) {
		// Verificar si hay cliente
		if ( COMPROB.isEmpty( $('#inputBuscarCli').val() ) ) {
			COMPROB.alerta('Seleccione un cliente !!', $('#inputBuscarCli'));
			return false;
		}

		// Verificar si hay fecha
		if ( COMPROB.isEmpty( $('#fechaComp').val() )) {
			COMPROB.alerta('Ingrese una fecha !!', $('#fechaComp'));
			return false;
		}

		// Verificar si hay concepto
		if ( COMPROB.isEmpty( $('#inputConcepto').val() ) ) {
			COMPROB.alerta('Ingrese un concepto !!', $('#inputConcepto'));
			return false;
		}

		// Verificar importe
		if ( COMPROB.isEmpty( $('#importe').val() ) || $('#importe').val() == 0 ) {
			COMPROB.alerta('Ingrese un importe !!', $('#importe'));
			return false;
		}		

		// Esconder boton y mostrar mensaje
		$('div#enviando').show();
		$(this).hide();

    	// Data del comprobante
    	let datacomprob = { fecha:   $('#fechaComp').val(),
    						idclie:  COMPROB.clienteSel.id,
    						cliente: COMPROB.clienteSel.cliente,
    						tipform: $('input:radio[name=radComp]:checked').val(),
    						tipo:    $("#selTipoFact option:selected").val(),
    						sucur:   $("#selSucursal option:selected").val(),
    						nrocomp: $('#nroComp').text(),
    						concept: $('#inputConcepto').val(),
    						importe: $('#importe').val() };

    	let formcsrf = $('form#formCsrf').serialize();
    	let dataJson = JSON.stringify(datacomprob);
    	let datos = formcsrf + '&data=' + dataJson;

		// Generar comprobante
	    $.ajax(
	    {
	        url : COMPROB.pathGeneraComp,
	        type: "POST",
	        data: datos,
	        //dataType: 'json',
	        success: function(data, textStatus, jqXHR) 
	        {
	            // data: returning of data from the server
				let dataObj = $.parseJSON(data);
				$('div#enviando').hide();
				$('#mensComprobGen').slideDown("slow").fadeOut(5000);

				// Recargar el formulario vacio. Espera 3 segundos
		    	setTimeout(function () {
		    		location.assign( COMPROB.pathComprobante );
		    	}, 3000);		
	        },
	        error: function(jqXHR, textStatus, errorThrown) 
	        {
	            // if fails
	            console.log('Status: ' + textStatus);
	            COMPROB.alerta('Error al salvar datos visita !!', $('#inputBuscarCli'));
	        }
	    });

	});

});
