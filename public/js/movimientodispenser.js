// Código javascript
// 

// Verifica si string es vacio
function isEmpty(str) {
    return (!str || 0 === str.length);
}

// Comparo fechas si fecha del mov es menor 
function comparoFechasMov(fecha) {
	var ultmov = new Date($('p#fechaUltMov').text());
	var fechaing = new Date(fecha);

	return fechaing < ultmov;
}

// Convierto string a fecha local
function convertirAFechaLocal(fecha) {
	arr = fecha.split('-');

	return arr[2] + '/' + arr[1] + '/' + arr[0];
}

function _dataDelForm() {
	var dataform = '&fecha=' + $('input#Fecha').val() + '&estado=' + $('select#Estado').val();
		dataform += '&observ=' + $('input#Observaciones').val();

	return dataform;
}

function _alertError(mensaje) {
    $.confirm({
        title: '<h5 class="text-danger"><strong>Error !!</strong></h5>',
        content: '<strong>' + mensaje + '</strong>',
        type: 'red',
        typeAnimated: true,
        closeIcon: true,
        icon: 'fas fa-exclamation-triangle',
        buttons: {
            tryAgain: {
                text: 'Cerar',
                btnClass: 'btn-red',
                action: function(){
                }
            },
        }
    });
}


// Codigo jQuery:
// 
$(document).ready( function () {

	// Si el div clase mensaje existe...
	if ($("div.mensaje").length > 0) {
		// Hace desaparecer el div con la línea del mensaje
        $('div.mensaje').delay(3000).fadeOut('slow');
	}

	// Accion CLICK de boton Buscar dispenser
	$('#btnBuscarDisp').click(function(event) {
		// Ir a buscar Dispenser...
		location.assign(global_buscarDisp + "?movimDisp=true");
	});

	$('button#btnBuscarEmp').click(function (event) {
		$('#modalEmpleados').modal('show');
	});

	// Cambios en select estado...
	$('select#Estado').change(function (event) {
		var est = $(this).val();
		console.log('Cambio estado a: ' + est);
	});

	// Boton del Modal para seleccionar Empleado
	$('button#btnSelecEmp').click(function(){
		var idEmp = $('select#selectEmpleado').val();
		var dataForm = _dataDelForm();

		$('#modalEmpleados').modal('hide');
		// Ir a mostrar empleado. Recargar página
		location.assign(global_movimDisp + "?idEmpl=" + idEmp + dataForm);
    });

	// Boton buscar cliente...
	$('button#btnBuscarCli').click(function (event) {
		// Ir a buscar Cliente. Recargar página
		location.assign( global_buscarCliente );
	});

	// Accion de Borrar del navbar
	$('a#linkBorrar').click(function(){
		if ( $('a#linkBorrar').attr('disabled') === undefined ) {
		 	var idMovDisp = $('input#Id').val();
		 	var idDisp = $('input#IdDispenser').val();
		 	var modelDisp = $('#modeloDisp').html();

		 	$.confirm({
		 		columnClass: 'medium',
		 		type: 'red',
				title: '<h4 class="text-danger"><strong>Confirmar !</strong></h4>',
				content: '¿ Elimina movimiento dispenser: <strong>' + modelDisp + '</strong> ?',
				buttons: {
					confirma: {
						btnClass: 'btn-danger',
						action: function () {
							location.assign(global_eliminarMovDisp + "?idmov=" + idMovDisp + "&iddisp=" + idDisp);
						}
					},
					cancela: function () {
						// Nada sucede si cancela
					}
				}
			});
		}
	});

	// Verificar datos antes de submit el form...
	$('form#movDisp').submit(function (event) {
		let idDisp = $('input#IdDispenser').val();
		let idEmpl = $('input#IdEmpleado').val();
		let idClie = $('input#IdCliente').val();
		let fecha  = $('input#Fecha').val();
		let hoy    = new Date();
		let fechaObj = new Date(fecha);
		let selectEstado = $('select#Estado').val();
		let estadoDispen = $('th#estadoDisp').html();

//console.log('Id dispenser: ' + idDisp);
//console.log('Id empleado: ' + idEmpl);
//console.log('Id cliente: ' + idClie);
//console.log('Fecha: ' + fecha);
//console.log('Estado select: ' + selectEstado);
//console.log('Estado dispen: ' + estadoDispen);
//console.log('Fecha: ' + new Date(fecha));
//console.log('Hoy: ' + hoy.getTime());
//console.log('Fecha: ' + fechaObj.getTime());
//console.log('Es mayor ?: ' + ( fechaObj.getTime() > hoy.getTime() ));

		if (isEmpty(idDisp)) {
			event.preventDefault();
			_alertError('Debe ingresar un dispenser.');
			return false;
		}
		if (idEmpl == 0) {
			event.preventDefault();
			_alertError('Debe ingresar un empleado.');
  		    return false;
		}
		if (selectEstado == estadoDispen && global_accion == 'Nuevo') {
			event.preventDefault();
			_alertError("Estado <i>debe ser diferente</i> al Estado del Dispenser");
  		    return false;
		}
		if (selectEstado == 'Cliente' && idClie == 0) {
			event.preventDefault();
			_alertError('Debe ingresar un cliente. <br> <small>(Estado = Cliente)</small>');
  		    return false;
		}
		// Controlo que si seleccionó un Cliente el estado sea Cliente (QUEDA SIN EFECTO. CUANDO SELEC UN DISP. CON ESTADO CLIENTE)
		//if (idClie > 0 && selectEstado != 'Cliente') {
		//	event.preventDefault();
		//	_alertError('Selecionó un cliente. <br> <strong>Estado DEBE ser: Cliente</strong>');
		//	$('select#Estado').focus();
  		//    return false;
		//}
		// Controlo que fecha no sea menor al último movimiento
		if (comparoFechasMov(fecha)) {
			let fechaLocal = convertirAFechaLocal($('p#fechaUltMov').text());
			event.preventDefault();
			_alertError('Fecha no puede ser anterior a último movimiento ! <br> ('+ fechaLocal +')');
			$('input#Fecha').focus();
			return false;
		}
		// Controlo que fecha no sea mayor a fecha actual
		if ( fechaObj.getTime() > hoy.getTime() ) {
			event.preventDefault();
			_alertError('Fecha no puede ser mayor a la actual ! <br> ('+ convertirAFechaLocal(fecha) +')');
			$('input#Fecha').focus();
			return false;
		}

//event.preventDefault();

		// Finalmente si está todo Ok... Enviar el form !
		$('#btnConfirma').prop({disabled: true});
		$('a#btnCancela').addClass('disabled');
		$('div#enviando').css('display','initial');
	})

});