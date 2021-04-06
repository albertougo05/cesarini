//
// codigo javascript
// 
 
/**
 * Funcionamiento boton UpScroll
 */ 
const _botonUp = document.getElementById("scrollUp");

_botonUp.addEventListener("click", function () {
    document.body.scrollTop = 0; // For Safari
    document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
});
// When the user scrolls down 300px from the top of the document, show the button
window.onscroll = function() {
    if (document.body.scrollTop > 280 || document.documentElement.scrollTop > 280) {
         _botonUp.style.display = "block";
    } else {
         _botonUp.style.display = "none";
    }    
};
/** end **/


var _tablaProductos = [];
var _idVisitaParaImp = 0;

function _armarLinea(id, prod, ret, dev, can) {
	let linea = '';
	linea = '<tr><td class="cellRight">' + id + '</td>';
	linea += '<td>' + id + '</td>';
	linea += '<td>' + prod + '</td>';
	linea += '<td class="cellRight">' + ret + '</td>';
	linea += '<td class="cellRight">' + dev + '</td>';
	linea += '<td></td>';

	linea += '<td class="text-center ml-0 mr-0">';
	linea += '<button type="button" id="btnBorrar" data-id="' + id + '"';
	linea += ' data-prod="' + prod + '" class="btn btn-danger btn-sm del"';
	linea += ' data-toggle="tooltip" data-placement="right" title="Eliminar">';
	linea += '<i class="fas fa-trash-alt"></i></button></td>';
	linea += '<tr>';

	return linea;
}

// Cartel de alerta por faltantes de contenido
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
            	text: 'Entendido',
            	btnClass: 'btn-red',
            	action: function () {
							$(element).focus();
						}
            }
        }
    });
}

// Convierte string '1.250,25' A '1250.25'
function _strToFloat(num) {
  num = num.replace('.', '');
  num = num.replace(',', '.');
  num = parseFloat(num);

  return num;
}


// Obtiene el precio del Producto
async function _getPrecioProducto(id, produc, retira, devuel ) {
    const param = `?id=${id}`;
    const debitoAct = parseFloat( $('input#debito').val() ) || 0; 
    let precio = 0, debito = 0;

    const data = await ( await fetch(_pathPrecioProduc + param)
            .catch(_handleErr))
            .json();

    // Parsear a Json

    if (data.code && data.code == 400) {
        _alerta('No hay precio del producto', 'input#debito' );
        return null;
    }

    precio = data.precio;
    debito = debitoAct + (precio * retira)
     $('input#debito').val(debito);
    // Agregar valores variable global
    _tablaProductos.push( { id: id, prod: produc, ret: retira, dev: devuel, ent: 0, deb: 0, precio: (precio * retira) } );
}

function _handleErr(err) {
    let resp = new Response(
        JSON.stringify({
            code: 400,
            message: "Stupid network Error"
        })
    );

    return resp;
}




//
// Codigo jQuery  ===========================================
// 
$(document).ready( function () {

	// Filtro para inputs que acepten solo numeros
	$('.numero').keyup(function(e) {
		if (/\D/g.test(this.value)) {
			// Filter non-digits from input value.
			this.value = this.value.replace(/\D/g, '');
			}
	});

    $('input#entrega, input#debito').inputmask("numeric", {
        radixPoint: ",",
        groupSeparator: ".",
        digits: 2,
        autoGroup: true,
        //prefix: '$ ', //Space after $, this will not truncate the first character.
        rightAlign: false,
        unmaskAsNumber: true, 
        oncleared: function () { self.value(''); }
    });

    // Al iniciar pongo focus en select empleado
    $('select#selectEmpleado').focus();

    // Al seleccionar empleado, lo guardo en $_SESSSION
    $('select#selectEmpleado').change( function (event) {
    	var idEmp = $(this).val();
    	var xhttp = new XMLHttpRequest();

		xhttp.onreadystatechange = function() {
		    if (this.readyState == 4 ) {
		    	if (this.status == 200) {
					console.log('Status OK: ' + this.status);
			    } else {
			    	console.log('Status error: ' + this.status);
			    }
		    }
		};
    	xhttp.open("GET", _pathGuardaIdEmpl + '?idemp=' + idEmp, true);
		xhttp.send();
    });

    // Al seleccionar producto pongo focus en input retira
    $('select#selectProducto').change(function (event) {
    	$('input#producto').val( $('#selectProducto option:selected').text() );
    	$('input#retira').focus();
    });

    // Boton buscar cliente
    $('button#btnBuscarCli').click(function (event) {

    	if ( $('#selectEmpleado').val() == 0 ) {
    		_alerta('Debe seleccionar un empleado', '#selectEmpleado');

    	} else {
	    	// Va a buscar cliente...
			location.assign( _pathBuscarcliente + '?vienede=visitaplanta' );
    	}
    });

    // Boton ingresa Producto
    $('button#btnIngresaProd').click(function (event) {
        const codsProdsDebitar = [1, 3, 5, 6, 15, 18];  // Códigos productos a debitar
    	let idprod = parseInt( $('#selectProducto').val() );
    	let produc = $('#selectProducto option:selected').text();
    	let retira = parseInt( $('input#retira').val() );
    	const devuel = $('input#devuelve').val();

    	// Chequear cliente
    	if ( $('input#idcliente').val() == 0 ) {
    		_alerta('Debe seleccionar un Cliente', '#btnBuscarCli');
    		return false;
    	}

    	if ( idprod == 0 ) {
    		_alerta('Debe seleccionar un producto !', '#selectProducto');
    		return false;
    	}

   		if ( retira == 0 && devuel == 0 ) {
   			// Alerta por cantidades vacias
   			_alerta('Debe ingresar cantidad ! (retira o devuelve)', 'input#retira');

   		} else {

    		if ( _tablaProductos.length == 0 ) {
    			// Si la tabla de productos está vacia, habilito botones
    			$('#btnConfirma').prop('disabled', false);
    			$('#btnCancela').prop('disabled', false);
    		}

            if ( codsProdsDebitar.includes(idprod) ) {
                // Si seleccionó Soda o Agua Destilada, buscar precio
                _getPrecioProducto(idprod, produc, retira, devuel);
            } else {
                // Agregar valores variable global
                _tablaProductos.push( { id: idprod, prod: produc, ret: retira, dev: devuel, ent: 0, deb: 0, precio: 0 } );
            }

			// Agregar linea a la tabla
			let linea = _armarLinea( idprod, produc, retira, devuel, _tablaProductos.length );
			// Agregar linea a tabla
			$("table tbody").append(linea);
			// Reset inputs y select
			$('input#retira').val('');
			$('input#devuelve').val('');
			$('#selectProducto').val(0).focus();
    	}
    });

    // Boton Borrar linea
    $('#tablaProds').on('click', '.del', function () {
		const id = $(this).closest('tr').find('button').attr("data-id");
		const func = function (x) { return x.id == id; }

		// Borrar del array
		const idx = _tablaProductos.findIndex(func);
        // Resta el precio del debito
        const debit = $('#debito').val((ind, val) => { val - _tablaProductos[idx].precio }); // _tablaProductos[idx].precio
        // Elimina el producto de la tabla
		_tablaProductos.splice(idx, 1);
		// eliminar de la tabla (html)
    	$(this).closest('tr').remove('tr');

    	if ( _tablaProductos.length === 0) {
    		// Si la tabla de productos está vacia, deshabilito botones
    		$('#btnConfirma').prop( "disabled", true );
    		$('#btnImprimir').prop( "disabled", true );
    	}
    });

    // Boton cancela
    $('#btnCancela').click(function (event) {
    	// Retorna a pagina inicio...
    	location.assign( _pathVisitaPlanta );
    })

    // Boton confirma
    $('#btnConfirma').click(function (event) {
    	// Chequear Empleado
    	if ( $('#selectEmpleado').val() == 0 ) {
    		_alerta('Debe seleccionar un Empleado', '#selectEmpleado');
    		return null;
    	}
    	// Chequear cliente
    	if ( $('input#idcliente').val() == 0 ) {
    		_alerta('Debe seleccionar un Cliente', '#btnBuscarCli');
    		return null;
    	}

        const debito  = _strToFloat( $('#debito').val() );
        const entrega = _strToFloat( $('#entrega').val() );
        // Si ha ingresado importe de entrega
        if ( entrega > 0 || debito > 0 ) {
            // Si no hay productos ingresados...
            if ( _tablaProductos.length === 0 ) {
                // Armo tabla sin producto...
                _tablaProductos.push( { id: 0, 
                                        prod: '',
                                        ret: 0, 
                                        dev: 0, 
                                        ent: entrega,
                                        deb: debito } );
            } else {
                // Si ya hay productos en la tabla...
                _tablaProductos[0].ent = entrega; 
                _tablaProductos[0].deb = debito; 
            }
        }

        //console.log( "Entrega: " + _tablaProductos[0].ent  );

    	// Ingreso a tabla...
		if ( _tablaProductos.length === 0 ) {
   			_alerta('Debe ingresar producto y cantidades !!', '#selectProducto');
   			return null;
   		}

		// Deshabilito los botones
		$('#selectEmpleado').prop('disabled', true);
		$('button#btnBuscarCli').prop('disabled', true);
		$('button#btnIngresaProd').prop('disabled', true);
		$('select#selectProducto').prop('disabled', true);
		$('#btnConfirma').prop('disabled', true);
		$('#btnCancela').prop('disabled', true);
		$('#btnImprimir').prop('disabled', true);

		// Muestra spinner Guardando datos...
		$('div#enviando').css('display','initial');

		let idempl = $('#selectEmpleado').val();
		let idclie = $('input#idcliente').val();
        let iddom  = $('input#iddomicil').val();
        //console.log( "Productos: " + _tablaProductos );
		$.get( _pathGuardaVisita, { idemp: idempl, 
                                    idcli: idclie, 
                                    iddom: iddom, 
                                    prods: _tablaProductos } )
		  	.done(function( data ) {
				//console.log( "Data devuelto: " + data );
				// Id devuelto por AJAX
				let obj = $.parseJSON(data);
				_idVisitaParaImp = obj.id;
                //console.log( "Id para imp: " + _idVisitaParaImp );
	    		$('div#enviando').css('display','none');    // Spinner out !
	    		$('#alertGuardar').slideDown("slow").fadeOut(5000);   // Show cartel Hecho...
	    		$('#btnImprimir').prop('disabled', false);
				//$('#btnConfirma').prop('disabled', false);
				$('#btnCancela').prop('disabled', false);
			})
			.fail( function () {
				_alerta('Error al intentar salvar datos. Intente nuevamente.', '#btnConfirma');
				$('div#enviando').css('display','none'); 
				$('#btnConfirma').prop('disabled', false);
				$('#btnCancela').prop('disabled', false);
			});
    });

    // Boton imprimir
    $('#btnImprimir').click( function (event) {
    	// Pagina a imprimir
    	window.open(_pathImprimir + '?idvisita=' + _idVisitaParaImp, '_blank');
    });



});
