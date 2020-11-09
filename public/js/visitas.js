//
// Código JAVASCRIPT...
//


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

// Constructs the suggestion engine
VISITAS.clientes = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.whitespace,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
        url: VISITAS.pathCliente + "?search=%QUERY%",
        wildcard: "%QUERY%"
    }
});

VISITAS.modif_Input = function (nombre, idprod, valor) {
	let elem = "input[name='" + nombre + idprod +  "']"
	// Modifico inputs...
	$(elem).val(valor);
}

VISITAS.suma_ProdsDeClies = function (idpro, dejOrec) {
	let suma = 0;

	VISITAS.prodsDeClies.forEach(function (element) {
		if (element.idpro == idpro) {
			let cant = element[dejOrec] || 0;
			suma += cant;
		}
	});

	return suma;
}

// Inputs movimiento productos retirados
// prodRet-{{ producto.idprod }} - devu-{{ producto.idprod }}
VISITAS._onKeyUpRetira = function (elem) {
	let nombre = elem.name;
	let idprod = nombre.substring(5);
	let valor  = elem.value || 0;

	// Suma de lo entregado (Venta)
	let sumDej = parseInt( VISITAS.suma_ProdsDeClies(idprod, "deja") );

//console.log('Suma dejados: ' + sumDej + ' - Retirado: ' + valor );

	if (sumDej > valor) {
		VISITAS._alerta("Lleva menos de lo dejado en clientes!!", $(this));
	}
	// Calculo el valor de lo devuelto
	let valDev = parseInt(valor) - sumDej;
	// Modifico inputs...
	VISITAS.modif_Input("devu-", idprod, valDev);
	VISITAS.modif_Input("prodDev-", idprod, valDev);
	VISITAS.modif_Input("prodRet-", idprod, valor);
	// Actualiza array de productos
	VISITAS._actualizArrProductos(idprod, 'retira', valor);
}

// Devuelve el codigo domicilio - name = deja_{idcli}x{iddom}_{idprod}o{orden}
VISITAS._getIdDomi = function (inputName) {
	let arr = inputName.split('_');
	let pos = arr[1].indexOf('x');
	let id = arr[1].substr(pos + 1);

	return parseInt(id);
}

// Evento onKeyUp para inputs dejados a cliente...
VISITAS._onkeyupDeja = function (elem) {
	let nombre = elem.name;
	let valor  = elem.value;
	let idprod = VISITAS._getIdProd(nombre);
	let idclie = VISITAS._getIdClie(nombre);
	let iddom  = VISITAS._getIdDomi(nombre);

//console.log('Input deja: ' + nombre + ' - Valor: ' + valor + ' - Id prod: ' + idprod + ' - Id cli: ' + idclie + ' - Id dom: ' + iddom);

	if ( idprod > 0 && valor >= 0 ) {
		// Valor Devuelve inicial (para volver si hay error...)
		//let valDevInic = $("input[name='devu-" + idprod + "']").val();
		//let valDejInic = $("input[name='dejado-" + idprod + "']").val();
		let inpReti = 'reti-' + idprod.toString();
		let valReti = $("input[name='" + inpReti + "']").val();
		// Asigno la cant ingresada al array...
		let idx = VISITAS.prodsDeClies.findIndex(x => x.idpro == idprod && x.idcli == idclie && x.iddom == iddom);

		VISITAS.prodsDeClies[idx].deja = parseInt( valor );
		// sumo productos de ese idprod...
		let sumaProdDeId = VISITAS.suma_ProdsDeClies(idprod, 'deja');
		// Calculo prods devueltos
		let prodDevuelto = valReti - sumaProdDeId;

//console.log('Name input ret: ' + inpReti +' - Retira: ' + valReti + ' - Suma de prods: ' + sumaProdDeId + ' - Devuelto: ' + prodDevuelto);
//console.log('Dejado inic: ' + valDejInic );

		// Verifico que no sea menor a 0
		if (prodDevuelto < 0) {

			VISITAS._alerta('No puede dejar más de lo Retirado !!', $(this));

//console.log('Id prod: ' + idprod + 'Venta: ' + valor +  ' - Devuelto: ' + prodDevuelto );

			// Vuelvo todos los resultados atras...
			elem.value = 0;  // $(this).val('');
			// Pongo 0 al array
			VISITAS.prodsDeClies[idx].deja = 0;
			// Vuelvo a hacer los cálculos..
			sumaProdDeId = VISITAS.suma_ProdsDeClies(idprod, 'deja');
			prodDevuelto = valReti - sumaProdDeId;
			// Modifico inputs...
			$("input[name='" + nombre + "']").val( 0 );
			VISITAS.modif_Input('dejado-', idprod, sumaProdDeId);
			VISITAS.modif_Input('devu-', idprod, prodDevuelto);
			VISITAS.modif_Input('prodDev-', idprod, prodDevuelto);			

		} else {
			// Modifico inputs...
			VISITAS.modif_Input('devu-', idprod, prodDevuelto);
			VISITAS.modif_Input('prodDev-', idprod, prodDevuelto);
			VISITAS.modif_Input('dejado-', idprod, sumaProdDeId);
		}

//console.log('Id prod: ' + idprod + ' Venta: ' + valor +  ' - Id Cliente: ' + idclie );

	}
}

// Devuelve el codigo de producto
VISITAS._getIdProd = function (inputName) {
	let arr1 = inputName.split('_');
	let str1 = arr1[2];
	let arr3 = str1.split('o');

	return parseInt(arr3[0]);
}

// Cuando sale de foco en inputs saldos y entregas
VISITAS._onFocusOut = function (elem, input) {
	let nombre = elem.name;
	let valor  = elem.value;
	let idprod = VISITAS._getIdProd(nombre);
	let idclie = VISITAS._getIdClie(nombre);
	let iddom  = VISITAS._getIdDomi(nombre);

//console.log('Valor en focusout: ' + valor + ' - Nombre: ' + nombre);

	// quito los puntos de miles y cambio las comas por punto decimal
	valor = valor.replace(".", "");
	valor = valor.replace(",", ".");

	// Asigno la cant ingresada al array...
	let idx = VISITAS.prodsDeClies.findIndex(x => x.idpro == idprod && x.idcli == idclie && x.iddom == iddom);

	if (input === 'entr') {
		VISITAS.prodsDeClies[idx].entr = parseFloat( valor );
		// sumo las entregas para la pantalla
		let sumaEntregas = VISITAS._funSumaEntregas();
		// Actualizo pantalla suma de entregas
		let str = 'Suma de entregas: $ ';
		str += VISITAS._currencyFormat(sumaEntregas);
		$('#divSumaEntr p').text(str);

	} else if (input === 'sald') {
		VISITAS.prodsDeClies[idx].saldo = parseFloat( valor );

	} else if (input === 'debi') {
		VISITAS.prodsDeClies[idx].debit = parseFloat( valor );

	} 
}

// Evento onKeyUp para inputs RETIRADOS a cliente... (Envases)
VISITAS._onKeyUpEnv = function (elem) {
	let nombre = elem.name;
	let valor  = elem.value;
	let idprod = VISITAS._getIdProd(nombre);
	let idclie = VISITAS._getIdClie(nombre);

	if ( idprod > 0 && valor >= 0 ) {
		// Asigno la cant ingresada al array...
		let idx = VISITAS.prodsDeClies.findIndex(x => x.idpro == idprod && x.idcli == idclie);
		VISITAS.prodsDeClies[idx].recu = parseInt( valor );
		// sumo productos retirados de ese idprod...
		let sumaProdDeId = VISITAS.suma_ProdsDeClies(idprod, 'recu');

		// Modifico inputs...
		VISITAS.modif_Input('recu-', idprod, sumaProdDeId);
		VISITAS.modif_Input('prodRecu-', idprod, sumaProdDeId);
	}
}

// Suma de entregas
VISITAS._funSumaEntregas = function () {
	let suma = 0;

	VISITAS.prodsDeClies.forEach( function (elem) {
		suma += ( elem['entr'] || 0 );
	});

	return suma;
}

// Formatea float a string de moneda
VISITAS._currencyFormat = function (num) {
  return (
    num
      .toFixed(2) // always two decimal digits
      .replace('.', ',') // replace decimal point character with ,
      .replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
  ) // use . as a separator
}

// name = deja_9_8o2
VISITAS._getIdClie = function (inputName) {
	let arr1 = inputName.split('_');
	let str1 = arr1[1];

	return parseInt(str1);
}

VISITAS._alerta = function (contenido, element) {

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

// Control de productos retirados, dejados y devueltos
VISITAS._controlProdDevueltos = function () {
	// Devueltos = Retirados - Dejados
	let retirado, sumaDeja, devuelto = 0;

	this.productos.forEach( function (elem) {
		retirado = $("input[name='reti-" + elem.id + "']").val();
		sumaDeja = VISITAS.suma_ProdsDeClies(elem.id, 'deja');
		devuelto = retirado - sumaDeja;

//console.log('Id prod: ' + elem.id + ' - Retira: ' + retirado + ' - Dejados: ' + sumaDeja + ' - Devuelto: ' + devuelto);
		// Actualiza inputs de devueltos
		$("input[name='devu-" + elem.id + "']").val(devuelto);
		$("input[name='prodDev-" + elem.id + "']").val(devuelto);
	});
}

// Actualizo array de Productos (para poner valor. OJO ! No suma ni resta !!)
VISITAS._actualizArrProductos = function (id, campo, cant) {
	let idx = this.productos.findIndex(x => x.id == id );

	switch (campo) {
		case 'retira':
			this.productos[idx].retira = parseInt(cant);
			break;
		case 'devuelto':
			this.productos[idx].devuel = parseInt(cant);
			break;
		case 'venta':
			this.productos[idx].dejado = parseInt(cant);
			break;
		case 'envase':
			this.productos[idx].envase = parseInt(cant);
			break;
	}
}

// Sumar ventas y envases de cada producto
VISITAS.recalcVentasEnvases = function () {

	VISITAS.productos.forEach(function (element) {

		ventas  = VISITAS.suma_ProdsDeClies(element.id, 'deja');
		envases = VISITAS.suma_ProdsDeClies(element.id, 'recu');
		devuelt = element.retira - ventas
		VISITAS._actualizArrProductos(element.id, 'venta', ventas);
		VISITAS._actualizArrProductos(element.id, 'envase', envases);
		VISITAS._actualizArrProductos(element.id, 'devuelto', devuelt);

//console.log('Id: ' + element.id + ' - Retira: ' + element.retira + ' - Devuelve: ' + devuelt + ' - Suma ventas: ' + ventas + ' - Envases: ' + envases);

		// Actualizar campos 
		$("input[name='devu-" + element.id + "']").val(devuelt);
		$("input[name='prodDev-" + element.id + "']").val(devuelt);
		$("input[name='dejado-" + element.id + "']").val(ventas);
		$("input[name='recu-" + element.id + "']").val(envases);
		$("input[name='prodRecu-" + element.id + "']").val(envases);
	});
}

// InputMask para importes de saldos y entregas de clientes 
VISITAS.inputmaskImportes = function () {
	$('input.nroFloat').inputmask("numeric", {
		alias: "curency",
		radixPoint: ",",
		groupSeparator: ".",
		digits: 2,
		autoGroup: true,
		rightAlign: true,
		unmaskAsNumber: true, 
		allowPlus: false,
    	allowMinus: true,
		oncleared: function () { self.value = ''; }
	});
}



//
// Codigo JQUERY:
// 

$(document).ready( function () {

	// Si hay id de visita, recalcular sumas de ventas y envases
	if (VISITAS.accion !== 'Nueva') {
		VISITAS.recalcVentasEnvases();	
	}

	// Input mask para horas
	$("#horaSalida").inputmask( { regex: "^([012][0-9]):[0-5][0-9]$" } );
	$("#horaRetorno").inputmask( { regex: "^([012][0-9]):[0-5][0-9]$" } );

	// Input Mask para orden de nuevo cliente
    $('input#ordenCli').inputmask("numeric", {
        radixPoint: ",",
        groupSeparator: ".",
        digits: 2,
        autoGroup: true,
        //prefix: '$ ', //Space after $, this will not truncate the first character.
        rightAlign: false,
        unmaskAsNumber: true, 
        allowPlus: false,
    	allowMinus: false,
        oncleared: function () { self.value(''); }
    });

	// Si el div clase mensaje existe...
	if ($("div.mensaje").length > 0) {
		// Hace desaparecer el div con la línea del mensaje
        $('div.mensaje').delay(3000).fadeOut('slow');
	}

	// Filtro para inputs que acepten solo numeros enteros
	// https://stackoverflow.com/questions/995183/how-to-allow-only-numeric-0-9-in-html-inputbox-using-jquery
	$('.numero').keyup(function(e) {
	    if (/\D/g.test(this.value)) {
	    	// Filter non-digits from input value.
	    	this.value = this.value.replace(/\D/g, '');
	  	}
	});

	// InputMask para importes de saldos y entregas de clientes
	VISITAS.inputmaskImportes();

	// Boton buscar empleado
	$('button#btnBuscarEmp').click(function (event) {
		$('#modalEmpleados').modal('show');
	});

	// Boton del Modal para seleccionar Empleado
	$('button#btnSelecEmp').click(function(){
		var idEmp = $('select#selectEmpleado').val();
		var nombEmp = $( "select#selectEmpleado option:selected" ).text();

		$('#modalEmpleados').modal('hide');
		// Actualizo pantalla
		$('p#nombreEmpleado').html('<strong>' + idEmp + ' - ' + nombEmp + '</strong>')
		                     .attr('data-idEmpl', idEmp);
		// Actualizo input hidden
		$('input#idemplead').val(idEmp);
    });

	// Buscar guia de reparto
	$('button#btnBuscarGuiaRep').click(function (event) {

		location.assign( VISITAS.pathBuscarGuiaRep + '?vienede=visitas');
	});

	// Cancelar visita
	$('button#btnCancelarVisita').click(function (event) {

		location.assign( VISITAS.pathVisitas );
	});

    // Click checkbox pendiente cambia el input hidden #pendiente
    $('input:checkbox#chkPend').click(function (event) {
    	let idvisita = $('#idvisita').val();		// Modificado 27/08/2020. v34

	    if (idvisita == 0) {

	    	return null;
	    } 
		//console.log('Id Guia reparto: ' + VISITAS.idGuiaRep);

	    if ( $(this).prop("checked") && VISITAS.nivelUsuario !== 'admin' && VISITAS.idGuiaRep !== 0 ) {
	    	// Si usuario comun, intenta tildar como pendiente NO PUEDE
	    	VISITAS._alerta('Solo usuario administrador modifica.', $(this));
	    	// Volver al estado original
	    	$(this).prop("checked", false);

	    	return null;
	    }

        if ( $(this).prop("checked") ) {
        	// Actualizo input hidden
        	$('#pendiente').val(1);
        	// Deshabilito boton guardar visita
        	$('#btnGuardarVisita').prop('disabled', false);
			// Habilito los inputs de productos y clientes
        	$('input:text').removeAttr('disabled');
        	// Excepto ..
        	$("input[name^='devu-'], input[name^='recu-'], input[name^='dejado-']").prop('disabled', true);
        	// Habilito boton recalcular sumas
        	$('#btnRecalSumsProds').prop('disabled', false);
        	// Habilito agregar cliente
        	$('#btnAgregarCli').prop('disabled', false);
        	// Habilito agregar dispenser
        	$('#btnAgregaDisp').prop('disabled', false);
        } else {
        	// Not checked...
        	$('#pendiente').val(0);
        	// Si deshabilito los inputs no se pueden guardar los form !!
        	//$('input:text').attr('disabled', true);
        }

        return null;
    });

	// Dispara el submit de formulario
	$('button#btnGuardarVisita').click(function (event) {
		// Deshabilita boton guardar...
		$(this).attr('disabled', true);
		// Muestra spinner Guardando datos...
		$('div#enviando').css('display','initial');
		// Envía el formulario...
		$('form#dataVisita').trigger('submit');

	});

	// Cuando completa el envio ajax
	$(document).ajaxComplete(function (event, xhr, settings) {

		if ( VISITAS.salvaForm ) {
			//$('button#btnGuardarVisita').attr('disabled', false);
			$('div#enviando').css('display','none');
			VISITAS.salvaForm = false;
		}
	});

	// Si fue exitoso el envio ajax
	$(document).ajaxSuccess(function(event, xhr, settings) {
		/* executes whenever an AJAX request completes successfully */

		if ( VISITAS.salvaForm ) {
	    	$('button#btnImprimirVisita').attr('disabled', true);
	    	$('#alertGuardar').slideDown( "slow" ).fadeOut(5000);

	    	// Salir de la visita guardada. Espera 3 segundos
	    	setTimeout(function () {
	    		location.assign( VISITAS.pathVisitas );
	    	}, 3000);
		}
	});

	// Envia el form dataVisita que incluye el form de productos
	$('form#dataVisita').submit(function (event) {
		event.preventDefault();
	    var datavisita = $('form#dataVisita').serialize();
	    var dataobserv = $('form#dataObserv').serialize();
	    var formurl    = $('form#dataVisita').attr("action");

	    // Control de totales de productos Reitrados - Suma de dejados = Devueltos
	    VISITAS._controlProdDevueltos();

	    var dataprods  =  $('form#dataProds').serialize();
	    datavisita += '&' + dataobserv + '&' + dataprods;
	    VISITAS.salvaForm = true;

	    // Si hay movimiento/s de dispenser
	    if ( VISITAS.movimdisp.length > 0 ) {
	    	var datadisp = JSON.stringify(VISITAS.movimdisp)
	    	datavisita += "&dispenser=" + datadisp;
	    }

	    // Si hay clientes ingresados, incluye data form clientes...
	    if ( VISITAS.hayClientes == 1 ) {
	    	var dataclies  = $('form#formProdClie').serialize();
	    	datavisita += '&' + dataclies;
	    }

//console.log('Disp: ' + datadisp);
//console.log('Data: ' + datavisita);
//return false;

	    $.ajax(
	    {
	        url : formurl,
	        type: "POST",
	        data: datavisita,
	        //dataType: 'json',
	        success: function(data, textStatus, jqXHR) 
	        {
	            // data: returning of data from the server
				let dataObj = $.parseJSON(data);
				// Actualizo los inputs con el id
	            $('input#id').val(dataObj.id);
	            $('input#idvisita2').val(dataObj.id);
	            $('input#idvisita3').val(dataObj.id);
	        },
	        error: function(jqXHR, textStatus, errorThrown) 
	        {
	            // if fails
	            console.log('Status: ' + textStatus);
	            VISITAS._alerta('Error al salvar datos visita !!', $('button#btnCancelarVisita'));
	        }
	    });
	});

	// Link elimina visita
	$('a#linkBorrar').click(function(){

		if ( $('a#linkBorrar').attr('disabled') == undefined ) {
		 	let idVisita = $('input#id').val();

		 	$.confirm({
		 		title: '<h5 class="text-danger"><strong>Confirmar !!</strong></h5>',
				content: '\n¿ Elimina Visita con ID: <strong>' + idVisita + '</strong> ?\n',
		        type: 'red',
		        typeAnimated: true,
		        closeIcon: true,
		        icon: 'fas fa-exclamation-triangle',
				buttons: {
					confirma: {
                		text: 'Confirma',
                		btnClass: 'btn-red',
						action: function () {
							location.assign( VISITAS.pathEliminar + "?idvisita=" + idVisita);
						}
					},
					cancela: function () {
						// Nada sucede si cancela
					}
				}
			});
		}
	});

	$('button#btnImprimirVisita').unbind('click');

	// Click Boton Imprimir
	$('button#btnImprimirVisita').click(function (event) {
		let id = $('input#id').val();

		window.open( VISITAS.pathImprimir + '?idvisita=' + id, '_blank');
	});

    // Initializing the typeahead with remote dataset without highlighting
    $('.typeahead').typeahead(
        {
            minLength: 3,
            highlight: true
        },
        {
            name: 'cliente',
            source: VISITAS.clientes,
            // display: 'cliente'
            display: function(item) {
              // Setea la salida del menu de opciones...
              return item.cliente + ' - ' + item.direccion;
            },
            limit: 10 /* Specify max number of suggestions to be displayed */
        }
    );

    // Boton Borrar de lista de clientes con productos
    $('button#btnElimCli').click( function (event) {
        var nomCli = $(this).data('nomcli');
        var boton  = $(this);

        $.confirm({
            columnClass: 'medium',
            type: 'red',
            typeAnimated: true,
            title: 'Confirmar !',
            content: "<p class='text-center'>¿ Desea eliminar a: " + nomCli + ' ?</p>',
            buttons: {
                confirma: {
                    text: 'Confirmar',
                    btnClass: 'btn-danger',
                    action: function () {
                    	let idcli  = boton.data('idcli');
                    	let iddom  = boton.data('iddom');
                    	let idprod = boton.data('idprod');
                    	let retira = $("input[name='reti-" + idprod + "']").val();

                        boton.closest('tr').remove();

                        // elimino el cliente
                        VISITAS.prodsDeClies = VISITAS.prodsDeClies.filter(function(el) { return el.idpro != idprod && el.idcli != idcli; }); 
                    	let sumaDej = suma_ProdsDeClies(idprod, 'deja');
						// Calculo el valor de lo devuelto
						let valDev = parseInt(retira) - sumaDej;
						// Modifico inputs...
						VISITAS.modif_Input('devu-', idprod, valDev);
						VISITAS.modif_Input('prodDev-', idprod, valDev);

//console.log('Id cli: ' + idcli + ' - Id prod: ' + idprod);
//console.log('Retirados: ' + retira + ' - Suma dejados: ' + sumaDej + ' - Nuevo val dev: ' + valDev);

                    }
                },
                cancela: function () {
                    // Nada sucede si cancela
                }
            }
        });
 	});

    // Click en boton Recalcular sumas productos
    $('#btnRecalSumsProds').click(function (event) {
    	let ventas, envases, devuelt = 0;

    	// Sumar ventas y envases de cada producto
    	VISITAS.recalcVentasEnvases();
    	// Mostrar aviso de realizado
    	$('#alertRecalcula').slideDown( "slow" ).fadeOut(5000);
    });


});  // End $(document).ready()
