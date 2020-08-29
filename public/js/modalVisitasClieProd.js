//
// Funciones JS para modal Agregar Cliente a Visitas
//

$('#selProd').change(function (e) {
	const idpro = $(this).val();
	const idcli = $('#idclie').val();

	if (idcli > 0 && idpro > 0) {
		MODCLIPRO.stockEnvases(idpro, idcli);
		console.log('select producto...');
	}
});

// Click al boton Agrega cliente de pantalla Visitas
$('#btnAgregarCli').click(function (event) {
	// Vacio los inputs del form
	$('#inputModalBuscarCli').val('');
	$('#idclie').val(0);
	$('#client, #inputModBuscarCli').val('');
	$('#iddomi').val(0);
	$('#domici').val('');
	$('#selProd').val(0);
	$('#ordenCli').val( VISITAS.ultimOrden + 1);
	$('.typeahead').typeahead('val', '');
	$('#modalAgregarCliente').modal('show');
	// Reset valor stock envases
	MODCLIPRO.stock = 0;
});

// Evento select del typeahead...
$('.typeahead').bind('typeahead:select', function(ev, suggestion) {

     MODCLIPRO.llenarInputsHidden(suggestion);
});

// Evento autocomplete del typeahead...
$('.typeahead').bind('typeahead:autocomplete', function(ev, suggestion) {
    //console.log('Autocomplete Selection: ', suggestion);

    MODCLIPRO.llenarInputsHidden(suggestion);
});

// Click al boton Confirma del modal
$('#btnConfirmAgrCli').click(function (event) {
	const idclie = $('#idclie').val();
	const client = $('#client').val();
	const iddomi = $('#iddomi').val();
	const orden  = $('#ordenCli').val();
	const idprod = $('#selProd').val();
	const produc = $("#selProd option:selected").text();
	const precio = $("#selProd option:selected").attr('data-pre');
	const stockEnv = MODCLIPRO.stock;

	//console.log('Precio: ' + precio);

	if (idclie == 0 || idprod == 0 || orden == 0) {
		$('#modalAgregarCliente').modal('hide');

		return false;
	}

	// Chequear si el cliente-domicilio y producto existen ya en la lista
	const idxCli = VISITAS.prodsDeClies.findIndex(x => x.idpro == idprod && x.idcli == idclie && x.iddom == iddomi );
	//console.log('Prod: ' + idprod + ' Cli: ' + idclie + ' Dom: ' + iddomi)

	if ( idxCli >= 0 ) {
		//console.log('El cliente-domicilio y producto ya existe en la lista !');
		VISITAS._alerta('El cliente y el producto ya existen en la lista !!', $('#inputModBuscarCli'));

		return false;
	}

	// Buscar el indice del producto
	const idx = VISITAS.productos.findIndex(x => x.id == idprod);

	// Si el producto no existe en la lista de productos...
	if (idx === -1) {
		// Agrego el producto a lista de prod
		const obj = { id: idprod, retira: 0, devuel: 0 };
		VISITAS.productos.push( obj );
		// Agrego la linea de producto
		MODCLIPRO.armaLineaProductos(idprod, produc); 
	}

	// Busco el stock de envases
	if ( stockEnv === 0 ) { 
		MODCLIPRO.stockEnvases(idprod, idclie) 
	};
	// Busco saldo, SOLO si cliente-domicilio NO existen en la lista
	const existCli = VISITAS.prodsDeClies.findIndex(x => x.idcli === idclie && x.iddom === iddomi );
	//console.log('Existe id cli + id dom ? ' + existCli );
	if ( existCli === -1 ) {
		$.get( VISITAS.pathSaldoActual, { id: idclie } )
			.done(function( data ) {
				const dato = $.parseJSON(data);
			   	const saldo = parseFloat(dato.saldo);
			   	const abono = parseFloat(dato.abono);
				// Agrego cliente a la lista de clientes con productos
				MODCLIPRO.agregoClienteVarGlobal(idclie, client, idprod, orden, stockEnv, saldo, abono, produc, precio);
				// Crear linea e insertar a tabla clientes
				MODCLIPRO.lineaTablaClientes(idclie, orden, produc, idprod, stockEnv, saldo);
				//console.log('Saldo: ' + saldo);
		});

	} else {
		// Agrego cliente a la lista de clientes con productos
		MODCLIPRO.agregoClienteVarGlobal(idclie, client, idprod, orden, stockEnv, 0, VISITAS.prodsDeClies[existCli].abono, produc, precio);
		// Crear linea e insertar a tabla clientes
		MODCLIPRO.lineaTablaClientes(idclie, orden, produc, idprod, stockEnv, 0);
	}

	// Incrementar el ultimo orden
	VISITAS.ultimOrden = parseInt(orden);
	// Confirmo que hay clientes en la grilla
	VISITAS.hayClientes = 1;
	// Desactivo boton de imprimir (porque debe guardar para poder imprimir !)
	$('button#btnImprimirVisita').attr('disabled', true);
	// Activo boton de Guardar y cancelar (por si es el primer cliente)
	$('#btnGuardarVisita').attr('disabled', false);
	// Ocultar el modal
	$('#modalAgregarCliente').modal('hide');
});

// Variable global del modal Cliente-Productos
var MODCLIPRO = {

	stock: 0,
	stockReady: false,

	llenarInputsHidden: function (data) {
		// { id: 4, cliente: "CANALE Sandra Isabel", iddom: 4, direccion: "La Toma 456" }
		$('#idclie').val(data.id);
		$('#client').val(data.cliente);
		$('#iddomi').val(data.iddom);
		$('#domici').val(data.direccion);
	},

	armaLineaProductos: function (id, prod) {
		let lin = "<tr>";
		lin += "<td class='text-center'>" + id + "</td>";
		lin += "<td class='font-weight-bold'>" + prod + "</td>";

		lin += "<td><input onkeyup='VISITAS._onKeyUpRetira(this);' type='text' ";
		lin += "class='form-control form-control-sm cellRight numero inputCantsProds' ";
		lin += "name='reti-" + id + "'> ";
		lin += "<input type='hidden' name='prodRet-" + id + "' value='0'></td>";

		lin += "<td><input type='text' "; 
		lin += "class='form-control form-control-sm cellRight numero inputCantsProds' ";
		lin += "name='devu-" + id + "' disabled='true'>";
		lin += "<input type='hidden' name='prodDev-" + id + "' value='0'></td>";

		lin += "<td><input type='text' ";
		lin += "class='form-control form-control-sm cellRight numero inputCantsProds' ";
		lin += "name='dejado-" + id + "' disabled='true'></td>";

		lin += "<td><input type='text' ";
		lin += "class='form-control form-control-sm cellRight numero inputCantsProds' ";
		lin += "name='recu-" + id + "' disabled='true'>";
		lin += "<input type='hidden' name='prodRecu-" + id + "' value='0'></td>";

		lin += "</tr>";

		$("table#divProductos tbody").append(lin);
	},

	lineaTablaClientes: function (idclie, orden, produc, idprod, stock, saldo) {
		let iddomi = $('#iddomi').val(),
		    client = $('#client').val(),
			domici = $('#domici').val(),
			linea  = '<tr><td>' + idclie + '<input type="hidden" name="idreg_';

		// <input type="hidden" name="idreg_{{ datcli.idclie }}x{{ datcli.iddomic }}_{{ datcli.idprod }}o{{ datcli.orden }}" value="{{ datcli.idreg }}">
		linea += idclie + 'x' + iddomi + '_' + idprod + 'o' + orden + '" value="0">';
		linea += '</td>';

		linea += '<td class="celClieDom">' + client + ' <small>(' + domici + ')</small></td>';
		linea += '<td class="cellRight celOrden">' + orden + '</td>';

		linea += '<td class="celProducto">' + produc + '</td>';

		// Toma el stock de la variable MODCLIPRO y no del parametro stock !!
		linea += '<td class="cellRight">' + MODCLIPRO.stock + '</td>';
		linea += '<input type="hidden" name="stock_' + idclie + 'x' + iddomi + '_' + idprod + 'o' + orden + '" value="' + MODCLIPRO.stock + '">';

		if ( VISITAS.accion == 'Nueva') {
			// inserto columna de sugerido
			linea += '<td></td>';
		}
		linea += '<td><input onkeyup="VISITAS._onkeyupDeja(this);" onfocusout="DEBITOS.onFocusOut(this);" name="deja_' + idclie + 'x' + iddomi + '_' + idprod + 'o' + orden + '" ';
		linea += 'class="form-control form-control-sm cellRight numero celInputCant" type="text" value=""></td>';

		linea += '<td><input onkeyup="VISITAS._onKeyUpEnv(this);" name="ret_' + idclie + 'x' + iddomi + '_' + idprod + 'o' + orden + '" ';
		linea += 'class="form-control form-control-sm cellRight numero celInputCant" type="text" value=""></td>';

		linea += '<td><input onfocusout="VISITAS._onFocusOut(this, ' + '\'sald\');\"' + ' name=\"saldo_' + idclie + 'x' + iddomi + '_' + idprod + 'o' + orden + '\" ';
		linea += 'class="form-control form-control-sm cellRight celInputSaldo nroFloat" type="text" value=' + saldo + '></td>';

		linea += '<td><input onfocusout="VISITAS._onFocusOut(this, ' + '\'entr\');\"' + ' name=\"entr_' + idclie + 'x' + iddomi + '_' + idprod + 'o' + orden + '\" ';
		linea += 'class="form-control form-control-sm cellRight celInputEntr nroFloat" type="text" value=0></td>';

		linea += '<td><input onfocusout="VISITAS._onFocusOut(this, ' + '\'debi\');\"' + ' name=\"debi_' + idclie + 'x' + iddomi + '_' + idprod + 'o' + orden + '\" ';
		linea += 'class="form-control form-control-sm cellRight celInputEntr nroFloat" type="text" value=0></td>';

		// linea += '<td class="text-center pl-0 pr-0">';
		// linea += '<button type="button" id="btnElimCli" data-idcli="{{ datcli.idclie }}" ';
		// linea += 'data-nomcli="{{ datcli.cliente }}" data-iddom="{{ datcli.iddomic }}" ';
		// linea += 'data-idprod="{{ datcli.idprod }}" class="btn btn-danger btn-sm" ';
		// linea += 'data-toggle="tooltip" data-placement="right" title="Borrar">';
		// linea += '<i class="fa fa-trash" aria-hidden="true"></i></button></td>';

		// Agrega linea de input al DOM
		$("#tabClientes tbody").append(linea);
		// InputMask
		VISITAS.inputmaskImportes();
	},

	agregoClienteVarGlobal: function (idcli, client, idpro, orden, stock, saldo, abono, prod, precio) {
		let obj = {
			idreg: 0,
			idpro: idpro,
			produ: prod,
			precio: precio,
			idcli: idcli,
			iddom: $('#iddomi').val(),
			clien: client,
			orden: orden,
			stock: MODCLIPRO.stock,
			suger: 0,
			deja:  0,
			recu:  0,
			saldo: saldo,
			entr:  0,
			debit: 0,
			abono: abono,
		};

		VISITAS.prodsDeClies.push( obj );

		return null;
	},

	stockEnvases: function ( idProd, idClie ) {
		$.get( VISITAS.pathStockEnvases, { idpro: idProd, idcli: idClie } )
			.done(function( data ) {
				const dato = $.parseJSON(data);
			   	MODCLIPRO.stock = dato.stock;
		});

		return MODCLIPRO.stock;

//		const url = VISITAS.pathStockEnvases + '?idpro=' + idProd + '&idcli=' + idClie;
//		const promesa = fetch( url )
//		fetch( url ).then( res => res.json() )
//					.then( res => { 
//							MODCLIPRO.stock = res.stock;
//							console.log('Then: ' + res.stock);
//					});

//		Promise.all( [ promesa ] )
//			.then( value => { 
//				MODCLIPRO.stock = value.stock;
//				console.log('then: ' + value.stock);
//			});

//console.log('stock: ' + MODCLIPRO.stock);

//		return MODCLIPRO.stock;
	},


}