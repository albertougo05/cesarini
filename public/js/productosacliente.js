// Codigo jQuery
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

	// Set focus to select id="selectProducto"
	$("select#selectProducto").focus();

	$('input[type="text"]').keypress(function(event){
		if ((event.which > 46 && event.which < 58) || (event.which > 96 && event.which < 106)) {
    		//Si es tecla numérica activa el boton de guardar
    		$("button#btnAgregarProd").removeAttr('disabled');
		}
	});

	// Filtro para inputs que acepten solo numeros
	// https://stackoverflow.com/questions/995183/how-to-allow-only-numeric-0-9-in-html-inputbox-using-jquery
	$('input[type="text"]').keyup(function(e) {

	    if (/\D/g.test(this.value)) {
	    	// Filter non-digits from input value.
	    	this.value = this.value.replace(/\D/g, '');
	  	}
	});

	// Popup para modificar cantidad en tabla de cantidad de productos
	$('#tableCantProdClie tr').on('click', function() {
			var codProd  = $(this).find('td:first').html();
			var descProd = $(this).find('td:eq(2)').html();
			var cantidad = $(this).find('td:eq(3)').html();
			var idClie   = $('#idclie').val();
			var idDom    = $('#iddom').val();
			var id       = $('#id').val();
			var nomClie  = $('#nomclie').val();

	    $.confirm( {
	        title: '<strong>Modificar cantidad</strong>',
	        content: '' +
	        '<form action="" class="formModifCant">' +
	        '<div class="form-group">' +
	        '<label>'+ descProd +'</label>' +
	        '<input type="text" class="cant form-control form-control-sm col-sm-5 text-right float-right" value="' + cantidad +'" required />' +
	        '</div>' +
	        '</form>',
	        buttons: {
	            formSubmit: {
	                text: 'Confirma',
	                btnClass: 'btn-primary',
	                action: function () {
	                    let newCant = this.$content.find('.cant').val();
	                    let getStr  = "?id=" + id + "&idclie=" + idClie;
	                    getStr     += "&nomclie=" + nomClie + "&cant=" + newCant;
	                    getStr     += "&idprod=" + codProd + "&iddom=" + idDom;

	                    location.assign( _pathModCantProd + getStr );
	                }
	            },
	            cancel: {
	                text: 'Cancela',
	            },
	        } 
	    });
	});

	// Para boton volver de abajo
	$('#btnVolverProd').click(function(){
    	// Pasar todos los datos del form al path de buscarcliente para cargarlos a la session
    	let idGuia = $('input#idGuia').val();

    	location.assign( _pathGuiaReparto + "?idguia=" + idGuia );
    });

	// Al seleccionar producto, cambia el foco a input cantidad
	$("select#selectProducto").change(function (event) {
		$('input#cantProduct').focus();
	});


});
