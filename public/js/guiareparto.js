//
// Codigo javascript: settings
//


//
/** Funcionamiento boton UpScroll  **/
//
const _botonUp = document.getElementById("scrollUp");

_botonUp.addEventListener("click", function () {
    'use strict';
    document.body.scrollTop = 0; // For Safari
    document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
});
// When the user scrolls down 300px from the top of the document, show the button
window.onscroll = function() {
    'use strict';

    if (document.body.scrollTop > 280 || document.documentElement.scrollTop > 280) {
         _botonUp.style.display = "block";
    } else {
         _botonUp.style.display = "none";
    }
};
/** end **/

const horaSalida = document.getElementById('horaSalida');
const hmOptions = {
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
        maxLength: 2
        },
        mm: {
        mask: IMask.MaskedRange,
        from: 0,
        to: 59,
        maxLength: 2
        }
    }
};
const hm_mask = new IMask(horaSalida, hmOptions);
const horaRetorno = document.getElementById('horaRetorno');
const hm_mask2 = new IMask(horaRetorno, hmOptions);


//
// Envía TODA la Guia de Reparto (GR, clientes y productos)
//
const _enviarTodaGR = async function () {
    const formElem = document.getElementById("form-guia-reparto");
    // Data base de GR
    const data = new FormData(formElem);
    // Data de clientes (filtro borrados)
    const clientes = DATA_GR.clientes.filter(e => e.borrado === 0);

//console.log('Clientes', clientes);

    data.append('dataClientes', JSON.stringify( clientes ));
    // Data productos de clientes
    data.append('dataProductos', JSON.stringify( DATA_GR.productosClie ));

    // enviando...
    const response = await fetch(_pathPostGuiaReparto, {
        method: 'POST',
        body: data
    });

    const result = await response.json();

    // Después del envio de datos...
    $('div#enviando').css('display','none');
    GUIAREPARTO.mensajesGR('Datos Guia Reparto', 'Guardados con éxito !', true);

    console.log(result);
};


ACTIONS_BTNS.btnAgregaProd = function (elem) {
    // Accion para boton en lista de clientes, agregar producto/s btnVerProd
    const idcli  = elem.getAttribute('data-idcli');
    const nombre = elem.getAttribute('data-nomcli');
    const iddom  = elem.getAttribute('data-iddom');

    this.initModalProductos(idcli, nombre, iddom);
    $('#modalIngresoProds').modal('show');
}

ACTIONS_BTNS.initModalProductos = function (idcli, nomb, iddom) {
    $('#idCliEnModalProd').val(idcli);
    $('#idDomCliEnModalProd').val(iddom);
    $('#tituloModalAgregaProd').html(`<strong>Ver/Agregar productos a: ${nomb}</strong>`);
    document.getElementById('btnModalAgregarProd').disabled = true;     // Deshabilito boton de agregar
    MODALPRODS.dataModalProds = [];      // Vacio lista temporal de productos en modal
    $('#bodyTablaProds').empty();       // Vacio la tabla de productos en el modal
    // buscar indice del cliente y domicilio
    const buscar = idcli + '-' + iddom;
    const idx = DATA_GR.productosClie.map( e => e.idCliente + '-' + e.idDomicilio ).indexOf(buscar);

    if (idx >= 0) {     // Si tiene productos...
        this.llenoModalTablaProductos(idcli);
    }
}

ACTIONS_BTNS.llenoModalTablaProductos = function (id) {
    const productos = DATA_GR.productosClie.filter(prod => prod.idCliente == id);
    MODALPRODS.dataModalProds = productos;

    productos.forEach( prod => {
        MODALPRODS.nuevaLineaProd(prod);
    });
}

ACTIONS_BTNS.btnOrdenCli = function (elem) {
    // Accion editar orden de visita
    const idGuia = document.getElementById('idGuia').value;
    const idCli  = elem.getAttribute('data-idcli');
    const idDom  = elem.getAttribute('data-iddom');
    const nomCli = elem.getAttribute('data-nomcli');
    const orden  = elem.getAttribute('data-orden');

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
                    const newOrden = this.$content.find('.newOrden').val();

                    if(!newOrden){
                        $.alert('Ingrese un número de orden !');
                        return false;
                    }
                    //Reordenar clientes
                    ACTIONS_BTNS.reordenarClientes(idCli, idDom, Number(newOrden)); //COMMONS.strToFloat(newOrden));
                }
            },
            cancelar: function () {
                //close
            },
        },
        onContentReady: function () {
            // bind to events
            const jc = this;
            this.$content.find('form').on('submit', function (e) {
            // if the user submits the form by pressing enter in the field.
                e.preventDefault();
                jc.$$btnConfirm.trigger('click'); // reference the button and click it
            });
        }
    });
};

ACTIONS_BTNS.btnBorrarCli = function (elem) {
    // Accion de Borrar cliente de la lista
    const idCli  = elem.getAttribute('data-idcli');
    const nomCli = elem.getAttribute('data-nomcli');
    const idDom  = elem.getAttribute('data-iddom');

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
                    ACTIONS_BTNS.borrarCliente(idCli, idDom);
                }
            },
            cancela: function () {
                // Nada sucede si cancela
            }
        }
    });
}

ACTIONS_BTNS.borrarCliente = function (idcli, iddom) {
    const buscar = idcli + '-' + iddom;
    // buscar indice el cliente
    const idx = DATA_GR.clientes.map( e => e.idCli + '-' + e.idDomicilio ).indexOf(buscar);
    // Asignar true al flag borrado
    DATA_GR.clientes[idx].borrado = 1;
    // Borrar lista de productos
    const temp = DATA_GR.productosClie.filter(e => e.idCliente != idcli && e.idDomicilio != iddom);
    DATA_GR.productosClie = temp;
    // Actualizar total de productos
    DATA_GR.totalProdsReparto = [];     // vacio la lista
    GUIAREPARTO.actualizoTablaTotalReparto();
    // Rearmar la pantalla
    this.reArmarPantallaClientes();
}

ACTIONS_BTNS.reordenarClientes = function (idcli, iddom, neword) {
    const buscar = idcli + '-' + iddom;
    // buscar indice el cliente
    const idx = DATA_GR.clientes.map( e => e.idCli + '-' + e.idDomicilio ).indexOf(buscar);
    // Asignar nuevo orden al cliente
    DATA_GR.clientes[idx].ordenVisita = parseFloat(neword);
    // Ordeno lista de clientes...
    DATA_GR.clientes.sort((a, b) => (a.ordenVisita > b.ordenVisita) ? 1 : -1)
    // Rearmar la pantalla..
    ACTIONS_BTNS.reArmarPantallaClientes();
}

ACTIONS_BTNS.reArmarPantallaClientes = function () {
    // Vaciar tbody
    const tbody = document.getElementById("tbodyTablaClientesGR");
    while (tbody.hasChildNodes()) {
        tbody.removeChild(tbody.lastChild);
    }
    // Loop por clientes para pintar
    DATA_GR.clientes.forEach(function (cli) {
        if ( cli.borrado == 0 ) {
            GUIAREPARTO.agregarCliente(cli);
        }
    });
}



MODALPRODS.agregarProducto = function (idprod, select) {
    const prodName = select.options[select.selectedIndex].text;
    const cant     = parseInt(document.getElementById("cantProduct").value);
    const producto = { idCliente: $('#idCliEnModalProd').val(),
                       idDomicilio: $('#idDomCliEnModalProd').val(),
                       idProducto: idprod,
                       producto: prodName,
                       cantidad: cant };
    this.nuevaLineaProd( producto );
    document.getElementById("cantProduct").value = '';
    document.getElementById("selectProducto").value = 0;
    this.dataModalProds.push( producto );
    $('#selectProducto').focus();
}

MODALPRODS.nuevaLineaProd = (prod) => {
    let linea = '<tr><td></td>';

    linea += `<td class="text-center">${prod.idProducto}</td>`;
    linea += `<td>${prod.producto}</td>`;
    linea += `<td class="text-right">`;
    linea += `<input name="cant_${prod.idProducto}" id="cant_${prod.idProducto}" `;
    linea += `class="form-control form-control-sm numero text-right celInputCant" `;
    linea += `type="text" value="${prod.cantidad}">`;
    linea += `</td>`;
    linea += `<td></td></tr>`;
    $('#bodyTablaProds').append(linea);
}



GUIAREPARTO.actualizoTablaTotalReparto = function () {
    const uniqIdsProd = [...new Set( DATA_GR.productosClie.map(x => x.idProducto) )];
    const uniqNameProd = [...new Set( DATA_GR.productosClie.map(x => x.producto) )];
    let suma = 0,
        idx = 0,
        obj = {};

    uniqIdsProd.forEach(function(ele, ind) {

        for (const elem of DATA_GR.productosClie) {
            if (elem.idProducto == ele ) {     //uniqIdsProd[0] ) {
                suma += parseInt(elem.cantidad);
            }
        }
        // Encuentro el indice del producto
        idx = DATA_GR.totalProdsReparto.map(function(e) {
                return e.idProduc;
              }).indexOf(ele);

        if (idx === -1) {
            obj.idProduc = ele;
            obj.producto = uniqNameProd[ind];
            obj.cantidad = suma;
            DATA_GR.totalProdsReparto.push(obj)
        } else {
            DATA_GR.totalProdsReparto[idx].cantidad = suma
        }
        obj = {};
        suma = 0;
    });
    // Ordeno la lista por id de producto
    DATA_GR.totalProdsReparto.sort((a, b) => (Number(a.idProduc) > Number(b.idProduc)) ? 1 : -1);
    // Armo tabla total productos
    this.reArmarTablaTotalProductos();
}

GUIAREPARTO.reArmarTablaTotalProductos = function (idguia) {
    const tbody = document.getElementById('tbodyTablaTotalReparto');
    while (tbody.hasChildNodes()) {
        tbody.removeChild(tbody.lastChild);
    }
    let linea = '';

    DATA_GR.totalProdsReparto.forEach( function(elem) {
        linea += `<tr>`;
        linea += `<td class="text-center">${elem.idProduc}</td>`;
        linea += `<td>${elem.producto}</td>`;
        linea += `<td class="text-center">${elem.cantidad}</td>`;
        linea += `</tr>`;

        tbody.insertAdjacentHTML('beforeend', linea);
        linea = '';
    });
}

GUIAREPARTO.winBuscarCliente = function (idguia) {       // Crea ventana para buscar Cliente
    const left = (screen.width/2)-500,    // (width/2) - Para centrar
          top  = (screen.height/2)-350;   //  (height/2) - Idem
    const paramsWin = "location=no,menubar=no,scrollbars=yes,resizable=no,titlebar=no,toolbar=no,top=" + top + ",left=" + left + ",width=900,height=700";
    const paramsUrl = "?idguia=" + idguia;

    if ( ! this.winBuscar ) {

        this.objWinBuscar = window.open(_pathBuscarCliente + paramsUrl, '_blank', paramsWin);    // Abrir la ventana para buscar Cliente
        this.objWinBuscar._clientes = DATA_GR.clientes;     // Paso la lista de clientes a la ventana de buscar
        this.objWinBuscar._cliente = {};        // Objeto vacio para ser llenado por el cliente seleccionado
        this.objWinBuscar._clienteSel = false;      // Flag para saber que está seleccionado un cliente

        this.winBuscar = true;      // Variable para saber que está abierta la ventana de búsqueda

        // Antes de que se dierre la ventana buscar...
        this.objWinBuscar.addEventListener("beforeunload", function (e) {

            if ( this._clienteSel  ) {
                // Agrego cliente a la lista de clientes
                DATA_GR.clientes.push(this._cliente);
                // Ordeno lista de clientes...
                DATA_GR.clientes.sort((a, b) => (Number(a.ordenVisita) > Number(b.ordenVisita)) ? 1 : -1);
                ACTIONS_BTNS.reArmarPantallaClientes(this._cliente);
                //window.scroll(0, 1000);
                document.getElementById('divTotalReparto').scrollIntoView({ behavior: 'smooth' });
            }
            GUIAREPARTO.winBuscar = false;
        });
    }
    return null;
}

GUIAREPARTO.agregarCliente = function (cliente) {
    const tbody = document.getElementById("tbodyTablaClientesGR");
    // { idCli: "353", apellidoNom: "ACA Nadal Soledad ", domicilio: "Ruta E 53 KM 40", idDomicilio: "358", ordenVisita: "35" }
    let lineaTr = `<tr>`;
        lineaTr += `<td id="tablaId">${cliente.idCli}</td>`;
        lineaTr += `<td>${cliente.apellidoNom}</td>`;
        lineaTr += `<td class="text-center">${cliente.ordenVisita}</td>`;
        lineaTr += `<td>${cliente.domicilio}</td>`;
        lineaTr += `<td>${cliente.localidad}</td>`;
        lineaTr += `<td>${cliente.celular}</td>`;
        lineaTr += this.botonesAccionesTabClies(0, cliente);
        lineaTr += this.botonesAccionesTabClies(1, cliente);
        lineaTr += this.botonesAccionesTabClies(2, cliente);
        lineaTr += `</tr>`;

    tbody.insertAdjacentHTML('beforeend', lineaTr);
    $('[data-toggle="tooltip"]').tooltip();     // Activa los tooltip
}

GUIAREPARTO.botonesAccionesTabClies = function (id, cliente) {
    const data = this.dataBotonesAcciones;
    let lin = '<td class="text-center pl-0 pr-0">';
    lin += `<button type="button" id="${data[id].id}" onclick="ACTIONS_BTNS.${data[id].id}(this);" data-idcli="${cliente.idCli}" `;
    lin += `data-nomcli="${cliente.apellidoNom}" data-iddom="${cliente.idDomicilio}" data-orden="${cliente.ordenVisita}" `;
    lin += `class="${data[id].class}" data-toggle="tooltip" data-placement="${data[id].tooltip}" `;
    lin += `title="${data[id].title}"><i class="${data[id].icon}"></i></button>`;
    lin += '</td>';

    return lin;
}

GUIAREPARTO.dataBotonesAcciones = [
    {id: 'btnAgregaProd', tooltip: "left",  title: "Agregar/Ver productos", class: "btn btn-success btn-sm", icon: "fas fa-plus-square" },
    {id: 'btnOrdenCli',   tooltip: "top",   title: "Editar orden", class: "btn btn-warning btn-sm ml-1 mr-1", icon: "fas fa-list-ol" },
    {id: 'btnBorrarCli',  tooltip: "right", title: "Borrar", class: "btn btn-danger btn-sm", icon: "fas fa-trash-alt" }
];

GUIAREPARTO.mensajesGR = function (titulo, mens, autoremove = false) {
    let options = {
        type: 'red',
        typeAnimated: true,
        columnClass: 'medium',
        title: titulo,
        content: '<br>' + mens
    }

    if (autoremove) {
        options.autoClose = 'okBtn|5000';
        options.columnClass = 'small';
        options.buttons = {okBtn: {text: 'Listo ', btnClass: 'btn-success'}};

    } else {
        options.buttons = {corregir: { text: 'Corregir', btnClass: 'btn-danger'}};
    }

    $.confirm(options);
}

    // Validacion de campos de Guia de Repartos
GUIAREPARTO.validarGR = function () {
    let mensajesErr = '',
        retorno = true;

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
        GUIAREPARTO.mensajesGR('La Guía contiene errores !!', mensajesErr);
        $('select#diaSemana').focus();
        retorno = false;
    }

    return retorno;
};



//
// Codigo jQuery:
//
//$(document).ready( function () {
$(function () {

    $('.numero').keyup(function(e) {
        if (/\D/g.test(this.value)) {
            // Filter non-digits from input value.
            this.value = this.value.replace(/\D/g, '');
        }
    });

    // Habilito los tooltip
    $('[data-toggle="tooltip"]').tooltip();

    //Para boton salvar del nav bar
    $('#linkSalvar').click( function () {
        if ( $('#linkSalvar').attr('disabled') == undefined ) {
            $("#form-guia-reparto").submit();
        }
    });

    // Habilito el boton de guardar
    $("select#idActividad").change( function () {
        const elemLi = document.querySelector('#liSalvar');
        const aLink = document.querySelector('#linkSalvar');

        if ($("select#idActividad").val() != '0') {
            $("button#btnGuardarGR").removeAttr('disabled');
            elemLi.classList.remove('disabled');
            elemLi.classList.add('active');
            aLink.classList.remove('disabled');
        } else {
            $("button#btnGuardarGR").prop("disabled", true);
            elemLi.classList.add('disabled');
            aLink.classList.add('disabled');
        };
    });

    // Cuando se hace el submit del form...
    $('#form-guia-reparto').submit(async function (event) {
        event.preventDefault();

        if ( GUIAREPARTO.validarGR() ) {
            // Desactivo boton de guardar y agregar cliente...
            $("button#btnGuardarGR").prop("disabled", true);
            const elemLi = document.querySelector('#liSalvar');
            const aLink  = document.querySelector('#linkSalvar');
            elemLi.classList.add('disabled');
            elemLi.classList.remove('active');
            aLink.classList.add('disabled');
            $('#btnAgregarClie').prop("disabled", true);
            // Spin de enviando visible
            $('div#enviando').css('display','initial');

            _enviarTodaGR();
        }
    });

    //Para boton agregar cliente
    $('#btnAgregarClie').click(function(){
        // Pasar todos los datos del form al path de buscarcliente para cargarlos a la session
        let idGuia = $('#idGuia').val();

        GUIAREPARTO.winBuscarCliente(idGuia);
        //location.assign(global_buscarcliente + "?idguia=" + idGuia);
    });

    // Si el div clase mensaje existe...
    if ($("div.mensaje").length > 0) {
        // Hace desaparecer el div con la línea del mensaje
        $('div.mensaje').delay(3000).fadeOut('slow');
    };

// MODAL Ingreso productos.
    $('input#cantProduct').keypress(function(event){
        if ((event.which > 46 && event.which < 58) || (event.which > 96 && event.which < 106)) {
            //Si es tecla numérica activa el boton de guardar
            if ( $("select#selectProducto").val() != 0 ) {
                $("button#btnModalAgregarProd").removeAttr('disabled');
            }
        }
    });

    // Al seleccionar producto, cambia el foco a input cantidad
    $("select#selectProducto").change(function (event) {
        $('input#cantProduct').focus();
    });

    // Click boton agregar producto a cliente en MODAL
    $('#btnModalAgregarProd').click(function (e) {
        const sel = document.getElementById("selectProducto");
        const idprod = sel.options[sel.selectedIndex].value;

        if (idprod > 0) {
            MODALPRODS.agregarProducto(idprod, sel);
        }
    });

    // Click boton confirma en MODAL agregar productos al cliente
    $('#btnConfirmAgregaProd').click(function (e) {
        const cantProdIngresados = MODALPRODS.dataModalProds.length;

        if (cantProdIngresados > 0) {
            MODALPRODS.dataModalProds.forEach(function (e) {
                let idx = DATA_GR.productosClie.findIndex( (p) => p.idCliente == e.idCliente && p.idDomicilio == e.idDomicilio && p.idProducto == e.idProducto);

                if (idx === -1) {       // NO encontró el indice
                    DATA_GR.productosClie.push(e);
                } else {
                    e.cantidad = $(`input#cant_${e.idProducto}`).val();
                    DATA_GR.productosClie[idx].cantidad =  e.cantidad;
                }
            });
        }
        $('#modalIngresoProds').modal('hide');
        GUIAREPARTO.actualizoTablaTotalReparto();
    });

    // Evento antes de cerrar la ventana
    $(window).on("beforeunload", function(e) {

        if ( GUIAREPARTO.winBuscar) {     // Si está abierta la ventana de buscar firma..
            GUIAREPARTO.objWinBuscar.close();    // ... la cierra
        }
    });


});
