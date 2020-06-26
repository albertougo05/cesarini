// Codigo javascript:
//
var horaSalida = document.getElementById('horaSalida');
var hmOptions = {
    mask: 'hh:`mm',
    lazy: false,               // make placeholder always visible
    placeholderChar: '_',     // defaults to '_'

    //pattern: 'hh:`mm',  // Pattern mask with defined blocks, default is 'd{.}`m{.}`Y'
    // you can provide your own blocks definitions, default blocks for date mask are:
    blocks: {
        hh: {
        mask: IMask.MaskedRange,
        from: 0,
        to: 24,
        maxLength: 2,
        },
        mm: {
        mask: IMask.MaskedRange,
        from: 0,
        to: 59,
        maxLength: 2,
        }
    }
};
var hm_mask = new IMask(horaSalida, hmOptions); 

var horaRetorno = document.getElementById('horaRetorno');
var hm_mask2 = new IMask(horaRetorno, hmOptions); 


// Codigo jQuery:
// 
$(document).ready( function () {

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
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    // Filtro para inputs que acepten solo numeros
    // https://stackoverflow.com/questions/995183/how-to-allow-only-numeric-0-9-in-html-inputbox-using-jquery
    //$('input[type="text"]').keyup(function(e) {
    $('.numero').keyup(function(e) {
        if (/\D/g.test(this.value)) {
            // Filter non-digits from input value.
            this.value = this.value.replace(/\D/g, '');
        }
    });

    //Para boton salvar del nav bar
    $('#linkSalvar').click( function () {
        if ( $('#linkSalvar').attr('disabled') == undefined ) {
            $("#form-guia-reparto").submit();
        }
    });

    //Para boton Borrar del nav bar
    $('#linkBorrar').click( function () {
        if ( $('#linkBorrar').attr('disabled') == undefined ) {
            var idGuia = $('input#idGuia').val();

            $.confirm({
                type: 'red',
                typeAnimated: true,
                closeIcon: true,
                icon: 'fas fa-exclamation-triangle', 
                title: '<h5 class="text-danger"><strong>Confirmar !!</strong></h5>',
                content: '\n¿ Desea eliminar Guía de Reparto: <strong>' + idGuia + '</strong> ?\n',
                buttons: {
                    confirma: {
                        text: 'Confirma',
                        btnClass: 'btn-red',
                        action: function () {
                            location.assign(global_eliminarguia + "?idguia=" + idGuia);
                        }
                    },
                    cancela: function () {
                        // Nada sucede si cancela
                    }
                }
            });
        }
    });

    // Habilito el boton de guardar
    $("select#idActividad").change( function () {
        if ($("select#idActividad").val() != '0') {
            $("button#btnGuardarGR").removeAttr('disabled');
        } else {
            $("button#btnGuardarGR").prop("disabled", true);
        };
    });

    // Cuando se hace el submit del form...
    $('#form-guia-reparto').submit(function (event) {
        // Desactivo boton de guardar y agregar cliente...
        $("button#btnGuardarGR").prop("disabled", true);
        $('#btnAgregarClie').prop("disabled", true);
        // Spin de enviando visible
        $('div#enviando').css('display','initial');
    });

    //Para boton agregar cliente
    $('#btnAgregarClie').click(function(){
        // Pasar todos los datos del form al path de buscarcliente para cargarlos a la session
        let idGuia = $('#idGuia').val();
/*        let diaSemana = $('#diaSemana').val;
        let idEmpleado = $('#idEmpleado').val;
        let turno = $('#turno').val;
        let horaSalida = $('#horaSalida').val;
        let horaRetorno = $('#horaRetorno').val;
        let estado = $('#estado').val;*/
        
        location.assign(global_buscarcliente + "?idguia=" + idGuia);
    });

    // Si el div clase mensaje existe...
    if ($("div.mensaje").length > 0) {
        // Hace desaparecer el div con la línea del mensaje
        $('div.mensaje').delay(3000).fadeOut('slow');
    };

    // Accion editar orden de visita
    $('button#btnOrdenCli').click( function (event) {
        var idGuia = $('input#idGuia').val();
        var idCli  = $(this).data('idcli');
        let orden  = $(this).data('orden');
        let nomCli = $(this).data('nomcli');

        $.confirm({
            type: 'red',
            typeAnimated: true,
            title: '<strong>Editar orden de visita</strong>',
            content: '<p class="bg-warning text-center pt-2 pb-2">' + nomCli + '</p>' +
                '<form action="" class="formOrden">' +
                    '<div class="form-group">' +
                        '<label>Ingrese nuevo orden:</label>' +
                        '<input type="text" class="newOrden form-control cellRight numero" value="' + orden + '" required />' +
                    '</div>' +
                '</form>',
            buttons: {
                btnConfirm: {
                    text: 'Confirmar',
                    btnClass: 'btn-primary',
                    action: function () {
                        var newOrden = this.$content.find('.newOrden').val();

                        if(!newOrden){
                            $.alert('Ingrese un número de orden !');
                            return false;
                        }
                        //$.alert('El nuevo orden es: ' + newOrden);
                        location.assign(_pathReordenarVisitas + "?idguia=" + idGuia + "&idclie=" + idCli + '&orden=' + newOrden);
                    }
                },
                cancelar: function () {
                    //close
                },
            },
            onContentReady: function () {
                // bind to events
                var jc = this;
                this.$content.find('form').on('submit', function (e) {
                    // if the user submits the form by pressing enter in the field.
                    e.preventDefault();
                    jc.$$btnConfirm.trigger('click'); // reference the button and click it
                });
            }
        });

    });

    // Accion de Borrar cliente de la lista
    $('button#btnBorrarCli').click(function(){
        var idCli = $(this).data('idcli');
        var idDom = $(this).data('iddom');
        var nomCli = $(this).data('nomcli');

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
                        location.assign(global_borrarcliente + "?idclie=" + idCli + '&iddom=' + idDom );
                    }
                },
                cancela: function () {
                    // Nada sucede si cancela
                }
            }
        });
    });

    // Accion para boton en lista de clientes, agregar producto/s btnVerProd
    $('button#btnAgregaProd').click(function(){
        var idCli   = $(this).data('idcli');
        var nombCli = $(this).data('nomcli');
        var idDom   = $(this).data('iddom');

        location.assign(global_productosacliente + "?idclie=" + idCli + "&nomclie=" + nombCli + "&iddom=" + idDom);
    });

    // Validacion de campos de Guia de Repartos
    $('form#form-guia-reparto').submit( function (event) {
        var mensajesErr = '';

        if ($('select#diaSemana').val() == 0) {
            mensajesErr += '-> Seleccione día semana <br>';
        }
        if ($('select#idEmpleado').val() == 0) {
            mensajesErr +='-> Seleccione repartidor <br>';
        }
        if ($('select#turno').val() == 0) {
            mensajesErr += '-> Seleccione turno <br>';
        }
        if ($('input#horaSalida').val() === '__:__') {
            mensajesErr += '-> Seleccione hora salida <br>';
        }
        if ($('input#horaRetorno').val() === '__:__') {
            mensajesErr +='-> Seleccione hora retorno <br>';
        }
        // Si contiene errores ...
        if (mensajesErr.length > 0) {
            event.preventDefault();

            $.confirm({
                type: 'red',
                typeAnimated: true,
                columnClass: 'medium',
                title: 'La Guía contiene errores !!',
                content: '<br>' + mensajesErr,
                buttons: {
                    corregir: function () {
                        $('select#diaSemana').focus();
                    }
                }
            });
        }
    });
});
