//
// Funciones JS para modal Agregar Mov Dispenser a visitas
//

// Variable global del modal...
var MOV_DISP = {

	listaCargada: false,          // Flag para cargar lista por única vez

	llenarInputsHidden: function (data) {
		// data = { id: 4, cliente: "CANALE Sandra Isabel", iddom: 4, direccion: "La Toma 456" }
		let estado = $('input:radio[name=radOpcMovim]:checked').val();

		$('#idclie2').val(data.id);
		$('#client2').val(data.cliente);
		$('#iddomi2').val(data.iddom);
		$('#domici2').val(data.direccion);
		$('#estado').val(estado);

		if (estado === 'Service') {
			// Buscar dispensers de cliente y llenar select
			this.buscaDispsCliente(data.id);
		}

	},

	confirmaMovDisp: function (iddis, idcli) {
		let modelo = $("#selDispenser option:selected").text();
		let idx    = modelo.indexOf(' - ');
		let obj = {};

		obj.idmov  = 0;
		obj.iddisp = iddis;
		obj.nroint = modelo.substring(0, idx);
		obj.modelo = modelo.substring( idx + 3);
		obj.idclie = idcli;
		obj.iddomi = $('#iddomi2').val();
		obj.client = $('#client2').val();
		obj.direcc = $('#domici2').val();
		obj.observ = $('#observac').val();
		//obj.estado = (this.opcMovimiento === 'Cliente') ? 'Cliente' : 'Stock';
		obj.estado = $('input:radio[name=radOpcMovim]:checked').val();

		// Asigno a array de dispensers...
		VISITAS.movimdisp.push( obj );
//console.log('Id cli: ' + idcli + ' NroInt: ' + obj.nroint + ' Modelo: ' + obj.modelo );
	},

	agregaLineaTablaMovDisp: function () {
		let idx = VISITAS.movimdisp.length - 1,
		    lin = "<tr>";

		lin += "<td>" + VISITAS.movimdisp.length + "</td>";
		lin += "<td>" + VISITAS.movimdisp[idx].nroint + "</td>";
		lin += "<td>" + VISITAS.movimdisp[idx].modelo + "</td>";
		lin += "<td>" + VISITAS.movimdisp[idx].client + "</td>";
		lin += "<td>" + VISITAS.movimdisp[idx].direcc + "</td>";
		lin += "<td>" + VISITAS.movimdisp[idx].estado + "</td>";
		lin += "<td></td></tr>";

		$("#tabDispenser tbody").append(lin);
	},

	// Llena select de dispensers en stock
	cargaListaDispenser: function () {

		$.get( VISITAS.pathDispEnStock, function( data ) {

			$.each(data, function(index, item) {
	            lin = "<option value='" + item.Id + "'>" + item.NroInterno + " - ";
	            lin += item.Modelo + "</option>";
	            $( "select#selDispenser" ).append( lin );
	        }); 
			// Cuando se cargó la lista por primera vez...
			this.listaCargada = true;

		}, "json");
	},

	inicioSelect: function (opc) {
		$("label#lblSelDispenser").text("Dispensers en " + opc);
		$("select#selDispenser").empty();
		$("select#selDispenser").append("<option value='0' selected>Seleccione dispenser...</option>");
		$("input#inputModBuscarCli2").val('');
	},

	// Al seleccionar opcion dispenser a Service
	opcionStock: function () {
		this.listaCargada = false;
		this.inicioSelect("Cliente");
		$("input#inputModBuscarCli2").focus();
	},

	// Opcion dispenser a Cliente
	opcionCliente: function () {
		this.inicioSelect("Service");

		if ( !this.listaCargada ) {
			MOV_DISP.cargaListaDispenser();			
		}

		$("select#selDispenser").focus();
	},

	// Buscar dispensers de cliente seleccionado
	buscaDispsCliente: function (idcli) {
		// Vacio el select, por si cambia de cliente...
		this.inicioSelect('Cliente');

		$.get( VISITAS.pathDispsDeClie, {id: idcli}, function( data ) {

			$.each(data, function(index, item) {
	            lin = "<option value='" + item.IdDispenser + "'>" + item.NroInterno + " - ";
	            lin += item.Modelo + "</option>";
	            $( "select#selDispenser" ).append( lin );
	        }); 

		}, "json");
	},



};   // Fin var MOV_DISP

// Al cargar el modal, busca via ajax los datos
$('#modalAgregaMovDisp').on('show.bs.modal', function (event) {

	if ( $('input:radio[name=radOpcMovim]:checked').val() === 'Service' ) {
		// Si la opcion es de cliente a Stock
		MOV_DISP.opcionStock();
	} else {
		MOV_DISP.opcionCliente();
	}
});

// Clic en opciones Movimiento
$('input:radio[name=radOpcMovim]').click( function (e) {
	let estado = $(this).val();

	if (estado === "Service") {
		MOV_DISP.opcionStock();
	}

	if (estado === "Cliente") {
		MOV_DISP.opcionCliente();
	}

});

// Boton Agrega movimiento dispenser a Visitas
$('#btnAgregaDisp').click(function (event) {
	// Vacio los inputs del form
	$('#inputModalBuscarCli').val('');
	$('#idclie2').val(0);
	$('#client2, #inputModBuscarCli2').val('');
	$('#iddomi2').val(0);
	$('#domici2').val('');
	$('#estado').val('');
	$('#observac').val('');
	$('#selDispenser').val(0);
	$('.typeahead2').typeahead('val', '');
	$('#modalAgregaMovDisp').modal('show');
});

// Initializing the typeahead with remote dataset without highlighting
$('.typeahead2').typeahead(
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

// Evento select del typeahead...
$('.typeahead2').bind('typeahead:select', function(ev, suggestion) {

    MOV_DISP.llenarInputsHidden(suggestion);

});

// Evento autocomplete del typeahead...
$('.typeahead2').bind('typeahead:autocomplete', function(ev, suggestion) {

    MOV_DISP.llenarInputsHidden(suggestion);

});

// Click al boton Confirma del modal
$('#btnConfirmMovDisp').click(function (event) {
	let idclie = $('#idclie2').val();
	let iddisp = $('#selDispenser').val();

console.log('Confirma. Id cli: ' + idclie + ' Id disp: ' + iddisp);

	if (idclie == 0 || iddisp == 0) {
		// Se cancela porque no selecciona dispenser o cliente
		$('#modalAgregaMovDisp').modal('hide');

		return false;
	}
	// Data a array de datos
	MOV_DISP.confirmaMovDisp(iddisp, idclie);
	// Desactivo boton de imprimir (porque debe guardar para poder imprimir !)
	$('button#btnImprimirVisita').attr('disabled', true);
	// Agrego línea a tabla de movimientos dispenser
	MOV_DISP.agregaLineaTablaMovDisp();
	// Sale de modal...
 	$('#modalAgregaMovDisp').modal('hide');
});

