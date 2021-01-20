//
// Realiza débitos por carga de cantidades entregadas en visitas
// (Sifones y agua destilada a todos y Bidones a clientes sin abono)
//
// http://localhost:8080/repartos/visitas/conidvisita?idvisita=539


const DEBITOS = {

	debitoComun: function (dat, prod) {
		// Input débito: name="debi_{{ datcli.idclie }}x{{ datcli.iddomic }}_{{ datcli.idprod }}o{{ datcli.orden }}"
		const nameInput = `debi_${ dat.idclie }x${ dat.iddom }_${ dat.idprod }o${ dat.orden }`;
		const valDebito = this.strToFloat( $('input[name=' + nameInput + ']').val() );

		if (dat.cantid === 0 && valDebito === 0 ) {
			//console.log('Cantidad 0');
			return null;		// Si es 0 y no está debitado
		}

		const debito = (dat.cantid * dat.precio); 
		$('input[name=' + nameInput).val(debito);
        //console.log('Input debito: ' + valDebito + ' - Debito por ' + prod + ': ' + debito);
        //console.log(dat, prod);
	},

	// Cuando pierde el foco el input deja...
	onFocusOut: function (elem) {
		const codsProdsDebitar = [1, 3, 5, 6, 15, 18];	// Códigos productos a debitar
		const obj = this.setVariables(elem);

			// Si los productos son soda y agua destilada...
			if (codsProdsDebitar.includes(obj.idprod)) {

				this.debitoComun(obj, 'soda');		// Debito por soda o agua dest

			} else {		// Si el cliente NO TIENE ABONO
				
				if ( VISITAS.prodsDeClies[obj.idx].abono === 0 ) {		// Buscar si el cliente tiene abono ...

					this.debitoComun(obj, 'bidon');			// Si NO TIENE ABONO: debitar el bidón...
				}
			}

		return null;
	},

	setVariables: function (elem) {
		const nombre = elem.name;
		const idprod = VISITAS._getIdProd(nombre);
		const idclie = VISITAS._getIdClie(nombre);
		const iddom  = VISITAS._getIdDomi(nombre);
		const idx    = VISITAS.prodsDeClies.findIndex( x => x.idpro == idprod && x.idcli == idclie && x.iddom == iddom );
		const obj = { cantid: parseInt(elem.value) || 0,
					  idprod: idprod,
					  precio: parseFloat(VISITAS.prodsDeClies[idx].precio),
					  idclie: idclie,
					  iddom:  iddom,
					  orden:  VISITAS.prodsDeClies[idx].orden,
					  idx:    idx,
					};
		return obj;
	},

	// Formatea string a float
	strToFloat: function (str) {
	    let num = str.replace('.', '');
	    let flo = 0;
	    num = str.replace(',', '.');
	    flo = parseFloat(num);

	    return flo.toFixed(2);
	},


};

