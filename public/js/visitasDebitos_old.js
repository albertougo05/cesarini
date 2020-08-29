//
// Realiza débitos por carga de cantidades entregadas en visitas
// (Sifones a todos y Bidones a clientes sin abono)
//
// http://localhost:8080/repartos/visitas/conidvisita?idvisita=539


const DEBITOS = {

	debitoComun: function (dat, prod) {
		var registroModificado = false;		// Sirve para si borra o modifica

		if (dat.cantid === 0 ) {
			//console.log('Cantidad 0 y no debitado.');
			return null;		// Si es 0 y no está debitado
		}

		const data = this.armarData(dat);
		// Si la cantidad es 0 y estaba debitado !!!
		if (dat.cantid === 0 && dat.debitado === 1 ) {

			this.cantZeroAndDebitado(dat);
			data.delete = 1;
			registroModificado = true;

		} else if (dat.debitado === 1) {

			this.producYaDebitado(dat);
			data.modifica = 1;
			registroModificado = true;

		} else {

			VISITAS.prodsDeClies[dat.idx].deja = dat.cantid;
			VISITAS.prodsDeClies[dat.idx].debitado = 1;
		}

//console.log(data);

		this.debitoPorAjax(data, dat, registroModificado);

	},

    debitoPorAjax: function (datos, dat, regModif) {
		$.ajax(     // Generar comprobante con valor abono
		{
			url : VISITAS.pathDebitoByAjax,
			type: "GET",
			data: datos,
			success: function(data, textStatus, jqXHR) 
			{		// data: returning of data from the server
				let dataObj = $.parseJSON(data);
				//console.log(dataObj.status);
				if ( !regModif ) {
					DEBITOS.cartelConfirm(dat);	
				}
			},
			error: function(jqXHR, textStatus, errorThrown) 
			{	// if fails
				//console.log('Status: ' + textStatus);
				$.alert({
					type: 'red',
	        		typeAnimated: true,
					title: 'Alerta de Error !',
					content: 'NO se salvaron los datos !!',
				});
			}
		});

		return null;
	},

	// Cuando pierde el foco el input deja...
	onFocusOut: function (elem) {
		const obj = this.setVariables(elem);

			// Si los productos son soda...
			if ( obj.idprod === 15 || obj.idprod === 18 ) {

				//this.debitoComun(obj, 'soda');		// Debito por soda

			} else {		// Si el cliente NO TIENE ABONO
				
				if ( VISITAS.prodsDeClies[obj.idx].abono === 0 ) {		// Buscar si el cliente tiene abono ...

					//this.debitoComun(obj, 'bidon');			// Si NO TIENE ABONO: debitar el bidón...
				}
			}

		return null;
	},

	setVariables: function (elem) {
		const nombre = elem.name;
		const idprod = VISITAS._getIdProd(nombre);
		const idclie = VISITAS._getIdClie(nombre);
		const iddom  = VISITAS._getIdDomi(nombre);
		const idx     = VISITAS.prodsDeClies.findIndex( x => x.idpro == idprod && x.idcli == idclie && x.iddom == iddom );
		//const produ    = VISITAS.prodsDeClies[idx].produ;
		//const debitado  = VISITAS.prodsDeClies[idx].debitado;
		const obj = { nombre: VISITAS.prodsDeClies[idx].clien,
					  cantid: parseInt(elem.value) || 0,
					  idprod: idprod,
					  produ:  VISITAS.prodsDeClies[idx].produ,
					  precio: VISITAS.prodsDeClies[idx].precio,
					  idclie: idclie,
					  idx:    idx,
					};
		return obj;
	},

	cartelConfirm: function (dat) {
	 	$.confirm({
	 		title: '<h5 class="text-danger"><strong>Débito generado !!</strong></h5>',
			content: '<strong>' + dat.nombre + '</strong>: <br>Cantidad: ' + dat.cantid + '<br>Producto: ' + dat.produ + '<br>',
	        type: 'red',
	        typeAnimated: true,
	        closeIcon: true,
	        icon: 'fas fa-exclamation-triangle',
	        autoClose: 'confirm|8000',
			buttons: {
				confirm: {
            		text: 'Ok',
            		btnClass: 'btn-blue',
				}
			}
		});
	},

	armarData: function (dat) {
		const concept = dat.cantid + ' ' + dat.produ + ' (' + dat.idprod + ')' + ' - Visita: ' + dat.idvisi;
		const data =  { fecha:    dat.fecha,
						idvisita: dat.idvisi,
						tipcomp:  'ND',
						tipo:	  'B',
						sucursal: 1,
						idclie:   dat.idclie,
						cliente:  dat.nombre,
						concept:  concept,
						cantid:   dat.cantid,
						idprod:   dat.idprod,
						importe:  0,
						modifica: 0,
						delete:   0 };
		return data;
	},

	cantZeroAndDebitado: function (dat) {
		$.alert({
			type: 'red',
	        typeAnimated: true,
			title: 'Aviso !!',
			content: 'Se elimina débito generado !!',
		});
		VISITAS.prodsDeClies[dat.idx].deja = dat.cantid;
		VISITAS.prodsDeClies[dat.idx].debitado = 0;
	},

	producYaDebitado: function (dat) {
		$.alert({
			type: 'red',
        	typeAnimated: true,
			title: 'Aviso !!',
			content: 'Modificó débito ya generado !!',
		});
		VISITAS.prodsDeClies[dat.idx].deja = dat.cantid;
		VISITAS.prodsDeClies[dat.idx].debitado = 1;
	},

};

