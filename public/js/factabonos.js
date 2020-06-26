//
// Set toastr options
// 
toastr.options = {
  "closeButton": true,
  "debug": false,
  "newestOnTop": false,
  "progressBar": false,
  "positionClass": "toast-top-right",
  "preventDuplicates": false,
  "onclick": null,
  "showDuration": "300",
  "hideDuration": "1000",
  "timeOut": "5000",
  "extendedTimeOut": "1000",
  "showEasing": "swing",
  "hideEasing": "linear",
  "showMethod": "fadeIn",
  "hideMethod": "fadeOut"
}

//
// Variable global
//
FACTABONO.clientesCargados = false;

FACTABONO.inputmaskImportes = function () {
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

// Convierte string '1250.25' A '$ 1.250,25'
FACTABONO.strToCurrency = function (num) {
  num = num.replace('.', '');
  num = num.replace(',', '.');
  num = parseFloat(num);

  return (
    '$ ' + num
      .toFixed(2) // always two decimal digits
      .replace('.', ',') // replace decimal point character with ,
      .replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
  )
}

// Click en boton confirma...
FACTABONO.clickConfirm = function (elem) {
	// Name tiene en id del cliente
	const idcli   = elem.name;
	const idelem  = elem.id;
	const importe = $('#input_' + idcli).val();
	const boton  = document.querySelector('#btnVerProd_' + idcli);
	const firma  = boton.dataset.name;	    

	const data = { idcli:   idcli,
				         firma:   firma,
				         periodo: this.periodoFac,
				         desde:   this.fechaDesde,
				         hasta:   this.fechaHasta,
				         importe: importe };

//console.log('Id CLIENTE: ' + idcli + ' - Firma: ' + firma + ' - Periodo: ' + this.periodoFac + ' - Importe: ' + importe);
//console.log(data);

  $.ajax(     // Generar comprobante con valor abono
  {   
    url : FACTABONO.confirmComprob,
    type: "GET",
    data: data,
    //dataType: 'json',
    success: function(data, textStatus, jqXHR) 
    { // data: returning of data from the server
		  //let dataObj = $.parseJSON(data);
		  //console.log(dataObj.status);
	    toastr["success"]("Creado con éxito !!", "Abono: " + firma);	// Alert (debe ir en ajax success !!)
	    FACTABONO.acionesConfirma(idelem, idcli);	// Acciones que modifican la pantalla				
    },
    error: function(jqXHR, textStatus, errorThrown) 
    { // if fails
      console.log('Status: ' + textStatus);
      alert('ERROR al salvar datos del comprobante !!');
    }
  });

	return null;
}

// Click en boton Ver productos...
FACTABONO.clickProds = function (elem) {
	const idcli  = elem.name;	// Name tiene en id del cliente
	const boton  = document.querySelector('#btnVerProd_' + idcli);
	const nombre = boton.dataset.name;

	$('#tituloModal').html("<strong>" + idcli + " - " + nombre + "</strong>");    //Cambio nombre del cliente en modal
	$('#listaProds').empty();          // Vacio el <ul>
	//console.log('Id: ' + idcli + ' - Nombre: ' + nombre);

	pedidoAjax = this.buscarProdsViaAjax(idcli);	// Traer productos por ajax

	pedidoAjax.done(function( data ) {
		//console.log('Data: ' + data );
		$('#listaProds').append(data);	   // Agregar lista de productos al modal
		$('#modalCant').modal('show');    // Mostrar los productos dejado por las visitas
	});
}

// Modificaciones al DOM, despues de boton confirma...
FACTABONO.acionesConfirma = function (idElem, idCli) {
	let htmlElem = document.getElementById(idElem).parentElement.previousSibling.previousSibling;
	const valInput = $('#input_' + idCli).val();

//console.log('Id Elemento: ' + idElem);
//console.log('Val input: ' + valInput);

	curValInput = this.strToCurrency(valInput);
//console.log('Currency: ' + curValInput);
	$('#input_' + idCli).parent().html(curValInput);	// Cambiar input por importe ingresado...

//console.log('html elem index: ' + htmlElem.cellIndex);
//console.log('Index row: ' + htmlElem.parentNode.rowIndex);

	htmlElem.setAttribute("class", "text-center"); 	// Cartel de SI 
	htmlElem.innerHTML = "<h5><span class='badge badge-success'> SI </span></h5>";
	$('[data-toggle="tooltip"]').tooltip('hide');	// Esconder tooltip
  $('#' + idElem).hide();    // Eliminar botón de confirmar
}

FACTABONO.buscarProdsViaAjax = function (id) {
	const request = $.ajax({
	  url: this.productosClie,
	  method: "GET",
	  data: { id: id, desde: this.fechaDesde, hasta: this.fechaHasta },
	  dataType: "html"
	});

	request.fail(function( jqXHR, textStatus ) {
	  console.log( "Fallo al buscar productos: " + textStatus );
	});

	return request;
}

FACTABONO.clickDetallado = function (elem) {
	let paramString = '?';
	paramString += 'desde='  + this.fechaDesde;
	paramString += '&hasta=' + this.fechaHasta;
	paramString += '&idcli=' + elem.name;

	// Abre pestaña nueva para resumen detallado
	window.open( this.resumenDetallado + paramString, '_blank');
	return null;
}

FACTABONO.poblarTablaClientes = function (idguia) {
  let url = '';
	const param = { idguia: idguia, 
		              periodo: this.periodoFac,
                  desde:   this.fechaDesde,
                  hasta:   this.fechaHasta };

//console.log('poblar...');

  if (idguia === 0) {
    url = this.pathClientesAbono;
  } else {
    url = this.clientesGuiaRep;
  }

  if ( !this.clientesCargados ) {

    $.ajax(   // Buscar datos por ajax con id guia de rep
    { 
      url : url,
      type: "GET",
      data: param,
      //dataType: 'json',
      success: function(data, textStatus, jqXHR) 
      { // data: returning of data from the server
    	  let dataObj = $.parseJSON(data);
  		  //console.log(dataObj);
  	    FACTABONO.dibujarLineasTabla(dataObj);
      },
      error: function(jqXHR, textStatus, errorThrown) 
      { // if fails
        console.log('Error status: ' + textStatus);
        alert('ERROR al buscar clientes !!');
      }
    });

    this.clientesCargados = true;

  } // end if

  return null;
}

FACTABONO.dibujarLineasTabla = function (data) {
	let linea = ``, 
	    costoAbono = '', 
	    importeFact = '';

	data.forEach(o => {
      linea += `<tr><td>${o.ApellidoNombre} - (${o.Id }) <small>${o.Direccion}, ${o.Localidad}</small></td>`;
      linea += `<td>${o.Dispensers}</td>`;
      linea += `<td class="cellRight">${o.cantBx12}</td>`;
      linea += `<td class="cellRight">${o.cantBx20}</td>`;
      costoAbono = this.strToCurrency(o.CostoAbono.toString());
      linea += `<td class="cellRight">${costoAbono}</td>`;

      if (o.PeriodoFact == 'si') {
      	importeFact = this.strToCurrency(o.ImporteFact.toString());
      	linea += `<td class="cellRight">${importeFact}</td>`;
      	linea += `<td class="text-center"><h5><span class="badge badge-success"> SI </span></h5></td>`;
      	linea += `<td></td>`;

      } else {
      	linea += `<td class="cellRight"><input id="input_${o.Id}" class="form-control form-control-sm cellRight nroFloat celInputAbono" type="text" value="${o.CostoAbono}"></td>`;
      	linea += `<td></td>`;
      	linea += `<td style="display: none;"></td><td class="text-center pl-0 pr-0">`;
      	linea += `<button type="button" id="btnConf_${o.Id}" onclick="FACTABONO.clickConfirm(this);" name="${o.Id}" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Confima abono"><i class="fas fa-edit"></i></button>`;
		    linea += `</td>`;
      }

      linea += `<td class="text-center pl-0 pr-0">`;
      linea += `<button type="button" id="btnVerProd_${o.Id}" onclick="FACTABONO.clickProds(this);" name="${o.Id}" data-name="${o.ApellidoNombre}" class="btn btn-warning btn-sm ml-1 mr-1" data-toggle="tooltip" data-placement="top" title="Ver prod. entregados"><i class="fas fa-layer-group"></i></button>`;
      linea += `</td>`;
      linea += `<td class="text-center pl-0 pr-0">`;
      linea += `<button type="button" id="btnVerDetal_${o.Id}" onclick="FACTABONO.clickDetallado(this);" name="${o.Id}" class="btn btn-info btn-sm ml-1 mr-1" data-toggle="tooltip" data-placement="top" title="Ver resumen detallado"><i class="fas fa-list-ul"></i></button>`;
      linea += `</td>`;

      $('#tabBody').append(linea);
      linea = ``;
	});
	// Mascara para input importes
	this.inputmaskImportes();
	$('#divSpinner').hide();

  return null;
}

FACTABONO.cambiarFechaPeriodo = function (fecha) {
  const request = $.ajax({
    url: this.pathFechaPeriodo,
    method: "GET",
    data: { fecha: fecha },
    dataType: "json"
  });

  // Actualiza los campos por cambio en fecha de facturacion
  request.done( function(data) {
    //console.log('Periodo: ' + data.periodo );
    $('#h4Periodo').text( data.periodo );
    FACTABONO.periodoFac = data.periodo;
    $('#fechaDesde').val(data.desde);
    FACTABONO.fechaDesde = data.desde;
    $('#fechaHasta').val(data.hasta);
    FACTABONO.fechaHasta = data.hasta;
    FACTABONO.recargarTablaClientes( Number( $('#selGuiaReparto').val() ) );
  });

  return null;
}

FACTABONO.recargarTablaClientes = function (id) {
  // ReCarga la tabla de clientes
  $('#tabClientes tbody').empty();
  $('#divSpinner').show();
  this.clientesCargados = false;
  this.poblarTablaClientes(id);
}




//
// Codigo jQuery:
// 
//$(document).ready( function () {   <<--- Deprecated !!
$(function () {

  // Carga inicial de datos
  FACTABONO.poblarTablaClientes(0);

  // Set focus en fecha
  $('#fechaActual').focus();

  // Al cambiar la fecha de facturación...
  $('#fechaActual').change( function(event) {
    // Evitar dos llamadas
    event.stopImmediatePropagation();
    FACTABONO.cambiarFechaPeriodo( $(this).val() );
  });

  // Boton de ir arriba
  $('#scrollUp').click( function () {
    $('body, html').animate({
      scrollTop: '0px'
    }, 300);
  });

  // Para chequear scroll para boton ir arriba
  $(window).scroll( function () {
    if( $(this).scrollTop() > 0 ) {
      $('#scrollUp').show();
    } else {
      $('#scrollUp').hide();
    }
  });

  // Habilito los tooltip
  //$(function () {
    $('[data-toggle="tooltip"]').tooltip()
  //});

	// InputMask para ingreso de importes 
	FACTABONO.inputmaskImportes();

	// Seleciona Guía de Reparto
	$('#selGuiaReparto').change(function(event) {
    event.preventDefault();
    event.stopImmediatePropagation();
    // Si es 0 recargo la página
    //if ($(this).val() === '0') {
    // 	window.location.assign(location.href);
    //}
    FACTABONO.recargarTablaClientes( Number($(this).val()) )    
	});


});
