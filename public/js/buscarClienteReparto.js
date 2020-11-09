//	Código javascript
//	


// Código jQuery
// 
$(document).ready(function(){

    $('input#orden').inputmask("numeric", {
        //radixPoint: ",",
        //groupSeparator: ".",
        digits: 2,
        autoGroup: true,
        rightAlign: false,
        unmaskAsNumber: true, 
        oncleared: function () { self.value(''); }
    });

	// Set inicial Filtrado de tabla Clientes	
	$('#buscarClienteRep').tableFilter({ tableID: '#tablaBuscarCli', 
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
		const idCli = $(this).find('td:first').html();
		const nombre = $(this).find('td:eq(1)').html();
		const idDom = $(this).find('td:eq(3)').html();
		const domic = $(this).find('td:eq(4)').html();
		const local = $(this).find('td:eq(5)').html();
		const celu = $(this).find('td:eq(6)').html();

		// Buscar si el idCli ESTÁ EN _clientes
		const filtro = _clientes.filter( (e) => e.idCli == idCli && e.idDomicilio == idDom );
		// Remover la clase de verde por si está...
		$('.datos').children().removeClass('bg-success text-white');
		// Poner en verde las linea seleccionada (cliente)
		$(this).addClass('bg-success text-white');

		if ( filtro.length > 0 ) {
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
		    });

		} else {

			document.getElementById('idclie').value = idCli;
			document.getElementById('apellidoNom').value = nombre;
			document.getElementById('iddomi').value = idDom;
			document.getElementById('domicilio').value = domic;
			document.getElementById('localidad').value = local;
			document.getElementById('celular').value = celu;
			$('input#orden').val(_clientes.length + 1).focus();	// Focus en input orden
			const boton = document.getElementById('btnSelecClie');
    		boton.removeAttribute("disabled");
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
		_clienteSel = true;		// Flag de cliente seleccionado
		// Lleno objeto con datos cliente selccionado..
		_cliente.id          = _clientes.length + 1;
		_cliente.idCli       = document.getElementById('idclie').value;
		_cliente.apellidoNom = document.getElementById('apellidoNom').value;
		_cliente.domicilio   = document.getElementById('domicilio').value;
		_cliente.idDomicilio = document.getElementById('iddomi').value;
		_cliente.localidad   = document.getElementById('localidad').value;
		_cliente.celular     = document.getElementById('celular').value;
		_cliente.ordenVisita = parseFloat(document.getElementById('orden').value);
		_cliente.borrado     = 0;
		// Cierro la ventana
		window.close();
	});

	// Boton cerrar ventana
	$('#btnCerrar').click(function (e) {
		window.close();
	});

});
