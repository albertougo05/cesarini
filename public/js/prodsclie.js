// Código JS
// 
var _cliente = {id: 0, cliente: '', iddom: 0, direccion: ''};

function _datosAcliente(data) {
	_cliente.id      = data.id;
	_cliente.cliente = data.cliente;
	_cliente.iddom   = data.iddom;
	_cliente.direccion = data.direccion;
	// quito el tilde este check, cuando selecciona cliente
	$('#chkVerCliNoGR').prop('checked', false);

	return null;
} 

// Constructs the suggestion engine
var clientes = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.whitespace,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
        url: _pathCliente + '?search=%QUERY',
        wildcard: '%QUERY'
    }
});

// Muestra popup de alerta
function _alerta(contenido, element) {
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


// Codigo jQuery:
// 
$(document).ready( function() {

	// Filtro para inputs que acepten solo numeros enteros
	$('.numero').keyup(function(e) {
	    if (/\D/g.test(this.value)) {
	    	// Filter non-digits from input value.
	    	this.value = this.value.replace(/\D/g, '');
	  	}
	});

    // Initializing the typeahead with remote dataset without highlighting
    $('.typeahead').typeahead(
        {
            minLength: 3,
            highlight: true
        },
        {
            name: 'cliente',
            source: clientes,
            // display: 'cliente'
            display: function(item) {
              // Setea la salida del menu de opciones...
              return item.cliente + ' - ' + item.direccion;
            },
            limit: 10 /* Specify max number of suggestions to be displayed */
    });

	// Evento select del typeahead...
	$('.typeahead').bind('typeahead:select', function(ev, suggestion) {
		// { id: 4, cliente: "CANALE Sandra Isabel", iddom: 4, direccion: "La Toma 456" }
	    //console.log('Select cliente: ' + suggestion.id + ' - ' + suggestion.cliente);
	    $('input#inputBuscarCli').css({'font-weight': 'bold', 'background': '#f5f5f5', 'color': 'black'});
	    _datosAcliente(suggestion);
	    $('#inputCliPorCod').val('');
	});

	// Evento autocomplete del typeahead...
	$('.typeahead').bind('typeahead:autocomplete', function(ev, suggestion) {
	    //console.log('Autocomp. cliente: ' + suggestion.id + ' - ' + suggestion.cliente);
	    $('input#inputBuscarCli').css({'font-weight': 'bold', 'background': '#f5f5f5', 'color': 'black'});
	    _datosAcliente(suggestion);
	    $('#inputCliPorCod').val('');
	});

	// On focus del input
	$('input#inputBuscarCli').focus(function (event) {
		$(this).css({'font-weight': 'normal', 'background': '#ffffff'});
		$(this).select();
	});

	// Click boton Buscar Cliente por Codigo
	$('button#btnCliPorCodigo').click(function (event) {
		let codigo = $('#inputCliPorCod').val();

		if (codigo > 0) {
			// Busca por ajax...
		    $.ajax( { url : _pathCodcliente,
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
							_datosAcliente(dataObj);
							$('#inputBuscarCli').val(dataObj.cliente + ' - ' + dataObj.direcc);
							$('input#inputBuscarCli').css({'font-weight': 'bold', 'background': '#f5f5f5', 'color': 'black'});

						} else{

							$('#inputCliPorCod').val('');
							_alerta('Código no encontrado !!', $('#inputBuscarCli'));
						}
		        	  },
		        	  error: function(jqXHR, textStatus, errorThrown) 
		        	  {
		            	// if fails
		          		console.log('Status: ' + textStatus);
		            	_alerta('Error al buscar código !!', $('#inputBuscarCli'));
		        	}
		    } );
		}

	});

	// Al hacer click en 'Ver clientes que NO están en GR'
	$('#chkVerCliNoGR').click(function (event) {
		if ( $(this).is(':checked') ) {
			$('#inputBuscarCli').val('')
								.css({'font-weight': 'normal', 'background': '#ffffff', 'color': 'black'});
			$('#inputCliPorCod').val('');
			$('#chkTodosDom').prop('checked', false);
			$('#chkDia').prop('checked', false);
			$('#chkTurno').prop('checked', false);
		}
	});

	// Click boton 'Generar listado FORM'
	$('#btnGenList').click(function (event) {
		// string a pasar como parametro
        let paramString = '?';
        let inputClie = $('input#inputBuscarCli').val();

		if (inputClie == '' && !$('#chkVerCliNoGR').is(':checked')) {
			paramString += 'idcli=';
			paramString += '&iddom=';
			_cliente = {id: 0, cliente: '', iddom: 0, direccion: ''};
			_alerta('No ha ingresado cliente !', $('input#inputBuscarCli'))

			return false;

		} else {
			paramString += 'idcli=' + _cliente.id;
			paramString += '&iddom=' + _cliente.iddom;
		}

		paramString += '&todosdom=' + $('#chkTodosDom').is(':checked');
		paramString += '&clisNoGR=' + $('#chkVerCliNoGR').is(':checked');
		paramString += '&ordGuiRe=' + $('#chkGuiaRep').is(':checked');
		paramString += '&ordDia='   + $('#chkDia').is(':checked');
		paramString += '&ordTurno=' + $('#chkTurno').is(':checked');

console.log('Params: ' + paramString);

		// Envio de datos a controller por get...
		window.open(_pathListProdsClie + paramString, '_blank');
	});



});
