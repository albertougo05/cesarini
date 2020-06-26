//	Código javascript
//	


// Código jQuery
// 
$(document).ready(function(){

	// Boton de ir arriba
    $('#scrollUp').click( function(){
    	$('body, html').animate({
			scrollTop: '0px'
		}, 300);
    });
    // Para chequear scroll para boton ir arriba
    $(window).scroll(function(){
    	if( $(this).scrollTop() > 0 ) {
    		$('#scrollUp').show();
    	} else {
    		$('#scrollUp').hide();
    	}
    });

    $('input#orden').inputmask("numeric", {
        radixPoint: ",",
        groupSeparator: ".",
        digits: 2,
        autoGroup: true,
        //prefix: '$ ', //Space after $, this will not truncate the first character.
        rightAlign: false,
        unmaskAsNumber: true, 
        oncleared: function () { self.value(''); }
    });

	// Set inicial Filtrado de tabla Clientes	
	$('#guiaReparto').tableFilter({ tableID: '#tablaBuscarCli', 
                                    filterID: '#filter',
                                    filterCell: '.filter-cell1',
		                            autofocus: true});

    // Click en radioBtns filtrar Apellido o Fantasia
    $("input[name=radFiltro]").click( function () {
    	// Vacio el campo filter
    	$('input#filter').val('');

    	switch ($(this).val()) {
    		case 'ape':
    			//console.log('Apellido');
				$('#guiaReparto').tableFilter({ tableID: '#tablaBuscarCli', 
			                                    filterID: '#filter',
			                                    filterCell: '.filter-cell1',
					                            autofocus: true});
				$('input#filter').prop('placeholder', 'Filtrar por apellido...');
    			break;
    		case 'fan':
				//console.log('Fantasia');
				$('#guiaReparto').tableFilter({ tableID: '#tablaBuscarCli', 
			                                    filterID: '#filter',
			                                    filterCell: '.filter-cell2',
					                            autofocus: true});
				$('input#filter').prop('placeholder', 'Filtrar por fantasía...');
    			break;
    	}
    });

	// Evento click para seleccionar y marcar linea
	$('#tablaBuscarCli tr').on('click', function(){
			let idCli = $(this).find('td:first').html();
			let nombre = $(this).find('td:eq(1)').html();
			let idDom = $(this).find('td:eq(3)').html();

			// Buscar si el idCli ESTÁ EN _idsClientes
			let indice = _idsClientes.indexOf( idCli.trim() );
			let idxDom = _idsDomicili.indexOf( idDom.trim() );

			// Remover la clase de verde por si está...
			$('.datos').children().removeClass('bg-success text-white');
			// Poner en verde las linea seleccionada (cliente)
			$(this).addClass('bg-success text-white');

			if ( indice >= 0 && idxDom >= 0 ) {

			    $.alert( {
			        title: '<strong>Atención !!</strong>',
			        content: '<p><strong>' + nombre + '</strong> ya existe en lista de visitas</p>',
			        type: 'red',
					typeAnimated: true,
					buttons: {
						ok: {
								text: 'Entendido',
								btnClass: 'btn-red',
								action: function() {
									// Remover la clase de verde
									$('.datos').children().removeClass('bg-success text-white');
            					}
					        }
				    }
			    } );

		} else {
				// Actualiza el input hidden con el id del cliente seleccionado
				$('input#idclie').val(idCli);
				// Actualiza el input hidden del id domicilio
				$('input#iddomi').val(idDom);				
				// Focus en input orden
				$('input#orden').val(_idsClientes.length + 1).focus();
		}
	});

	// Para prevenir el doble-click
	$('#tablaBuscarCli tr').on('dblclick',function(e){
    	/*  Prevents default behaviour  */
    	e.preventDefault();
    	/*  Prevents event bubbling  */
    	e.stopPropagation();

    	return;
    });

	// Boton seleccionar cliente
	$('#btnSelecClie').click(function (event) {
		let postdata = $('form#formOrden').serialize();

//console.log('Array serializado: ' + postdata);

		// Redirecciona a Guia de Reparto modificada
		location.assign(_pathGuiaReparto + '?' + $('form#formOrden').serialize() );
	});

});
