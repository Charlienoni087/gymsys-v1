/* JAVASCRIPT DE FACTURACIÓN
   Archivo: js/facturacion.js */
 
const facturas   = window.facturas   || [];
const productos  = window.productos  || [];
const membresias = window.membresias || [];
 
/* formato de dinero */
function dinero(valor) {
    return 'C$ ' + Number(valor).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
 
function escapar(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}
 
/* ===================== BUSCADOR DE LA TABLA ===================== */
 
const buscador = document.getElementById('buscadorFacturas');

function normalizar(texto) {
    return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

/* el buscador solo existe en el historial */
if (buscador) {
    buscador.addEventListener('input', function () {
        const texto = normalizar(this.value.trim());
        document.querySelectorAll('#tablaFacturas tbody tr').forEach(function (fila) {
            fila.style.display = normalizar(fila.textContent).includes(texto) ? '' : 'none';
        });
    });
}

/* NUEVA FACTURA (solo existe en la página de facturación) */

if (document.getElementById('formNuevaFactura')) {

 
    const selectCliente    = document.getElementById('facturaCliente');
    const selectMembresia  = document.getElementById('facturaMembresia');
    const cuerpoItems      = document.getElementById('itemsFactura');
    const filaSinItems     = document.getElementById('filaSinItems');
    const totalFactura     = document.getElementById('totalFactura');
    const btnGuardar       = document.getElementById('btnGuardarFactura');
    const btnAgregarItem   = document.getElementById('btnAgregarItem');
    const radiosTipo       = document.querySelectorAll('input[name="tipo_factura"]');
    const seccionMembresia = document.getElementById('seccionMembresia');
    const seccionProductos = document.getElementById('seccionProductos');
 
    let contadorItems = 0;
 
    /* tipo de factura elegido: 'membresia' o 'productos' */
    function tipoActual() {
        const r = document.querySelector('input[name="tipo_factura"]:checked');
        return r ? r.value : 'membresia';
    }
 
    /* carga las membresías pendientes del cliente elegido */
    function cargarMembresias() {
        const idCliente = parseInt(selectCliente.value, 10);
 
        if (!idCliente) {
            selectMembresia.innerHTML = '<option value="">Seleccione primero un cliente</option>';
            selectMembresia.disabled = true;
            return;
        }
 
        const delCliente = membresias.filter(m => parseInt(m.id_cliente, 10) === idCliente);
 
        selectMembresia.innerHTML = '<option value="" data-costo="0">' +
            (delCliente.length === 0
                ? 'Este cliente no tiene membresías para facturar'
                : 'Seleccione una membresía') +
            '</option>';
 
        delCliente.forEach(function (m) {
            const op = document.createElement('option');
            op.value = m.id_membresia;
            op.dataset.costo = m.costo;
            op.textContent = 'Membresía ' + m.fecha_registro + ' → ' + m.fecha_vencimiento + ' — ' + dinero(m.costo);
            selectMembresia.appendChild(op);
        });
 
        /* si la factura es de productos el campo no se envía */
        selectMembresia.disabled = tipoActual() !== 'membresia';
    }
 
    /* muestra solo la sección que corresponde al tipo de factura */
    function aplicarTipo() {
        const esMembresia = tipoActual() === 'membresia';
 
        seccionMembresia.classList.toggle('d-none', !esMembresia);
        seccionProductos.classList.toggle('d-none', esMembresia);
        selectMembresia.required = esMembresia;
 
        /* al cambiar de tipo se descarta lo que se había armado del otro */
        cuerpoItems.querySelectorAll('tr:not(#filaSinItems)').forEach(f => f.remove());
        contadorItems = 0;
 
        cargarMembresias();
        recalcular();
    }
 
    radiosTipo.forEach(r => r.addEventListener('change', aplicarTipo));
 
    selectCliente.addEventListener('change', function () {
        cargarMembresias();
        recalcular();
    });
 
    selectMembresia.addEventListener('change', recalcular);
 
    /* agregar una fila de producto */
    btnAgregarItem.addEventListener('click', function () {
        if (productos.length === 0) {
            window.mostrarNotificacion({ tipo: 'error', mensaje: 'No hay productos registrados.' });
            return;
        }
 
        const i = contadorItems++;
        const fila = document.createElement('tr');
 
        let opciones = '<option value="">Seleccione...</option>';
        productos.forEach(function (p) {
            const sinStock = parseInt(p.stock_disponible, 10) <= 0;
            opciones += '<option value="' + p.id_producto + '" data-precio="' + p.precio_venta +
                        '" data-stock="' + p.stock_disponible + '"' + (sinStock ? ' disabled' : '') + '>' +
                        escapar(p.nombre_producto) + (sinStock ? ' (sin stock)' : '') + '</option>';
        });
 
        fila.innerHTML =
            '<td><select name="productos[' + i + '][id_producto]" class="form-select form-select-sm item-producto" required>' + opciones + '</select></td>' +
            '<td><input type="number" name="productos[' + i + '][cantidad]" class="form-control form-control-sm item-cantidad" min="1" value="1" required></td>' +
            '<td class="item-precio text-end">C$ 0.00</td>' +
            '<td class="item-subtotal text-end">C$ 0.00</td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger item-quitar"><i class="bi bi-trash"></i></button></td>';
 
        cuerpoItems.appendChild(fila);
        recalcular();
    });
 
    /* eventos dentro de la tabla de ítems */
    cuerpoItems.addEventListener('change', recalcular);
    cuerpoItems.addEventListener('input', recalcular);
 
    cuerpoItems.addEventListener('click', function (e) {
        const btn = e.target.closest('.item-quitar');
        if (btn) {
            btn.closest('tr').remove();
            recalcular();
        }
    });
 
    /* recalcula subtotales y total según el tipo de factura */
    function recalcular() {
        const esMembresia = tipoActual() === 'membresia';
        let total = 0;
        const filas = cuerpoItems.querySelectorAll('tr:not(#filaSinItems)');
 
        if (esMembresia) {
            const opMem = selectMembresia.options[selectMembresia.selectedIndex];
            total = parseFloat(opMem?.dataset.costo || 0);
        } else {
            filas.forEach(function (fila) {
                const select = fila.querySelector('.item-producto');
                const inputCant = fila.querySelector('.item-cantidad');
                const op = select.options[select.selectedIndex];
 
                const precio = parseFloat(op?.dataset.precio || 0);
                const stock  = parseInt(op?.dataset.stock || 0, 10);
                let cantidad = parseInt(inputCant.value, 10) || 0;
 
                /* no permitir más que el stock disponible */
                if (select.value && cantidad > stock) {
                    cantidad = stock;
                    inputCant.value = stock;
                }
 
                const subtotal = precio * cantidad;
                fila.querySelector('.item-precio').textContent = dinero(precio);
                fila.querySelector('.item-subtotal').textContent = dinero(subtotal);
                total += subtotal;
            });
        }
 
        filaSinItems.style.display = filas.length === 0 ? '' : 'none';
        totalFactura.textContent = dinero(total);
 
        /* se necesita cliente y algo que cobrar */
        btnGuardar.disabled = !(selectCliente.value && total > 0);
    }
 
    /* al cerrar el modal se limpia el formulario */
    document.getElementById('modalNuevaFactura').addEventListener('hidden.bs.modal', function () {
        document.getElementById('formNuevaFactura').reset();
        aplicarTipo();
    });
 
    /* estado inicial */
    aplicarTipo();

}

/* ===================== VER E IMPRIMIR FACTURA ===================== */
 
function mostrarFactura(id) {
    const f = facturas.find(x => parseInt(x.id_factura, 10) === id);
    if (!f) return false;
 
    const anulada = (f.estado || 'pagada') === 'anulada';
 
    document.getElementById('verNumero').textContent =
        String(f.id_factura).padStart(6, '0');
    document.getElementById('verFecha').textContent =
        new Date(f.fecha_factura.replace(' ', 'T')).toLocaleDateString('es-NI');
    document.getElementById('verCliente').textContent = f.nombre_cliente;
    document.getElementById('verCedula').textContent = f.cedula || '';
    document.getElementById('verMetodo').textContent =
        f.metodo_pago === 'efectivo' ? 'Efectivo' : 'Transferencia';
 
    const estado = document.getElementById('verEstado');
    estado.textContent = anulada ? 'Anulada' : 'Pagada';
    estado.className = 'badge ' + (anulada ? 'badge-anulada' : 'badge-pagada');
 
    let filas = '';
    (f.detalles || []).forEach(function (d) {
        filas += '<tr><td>' + escapar(d.descripcion) + '</td>' +
                 '<td class="text-center">' + parseInt(d.cantidad, 10) + '</td>' +
                 '<td class="text-end">' + dinero(d.precio_unitario) + '</td>' +
                 '<td class="text-end">' + dinero(d.subtotal) + '</td></tr>';
    });
    document.getElementById('verDetalle').innerHTML = filas;
    document.getElementById('verTotal').textContent = dinero(f.total);
 
    return true;
}
 
const tablaFacturas = document.getElementById('tablaFacturas');

if (tablaFacturas) tablaFacturas.addEventListener('click', function (e) {
    const btnVer = e.target.closest('.btn-ver-factura');
    const btnImp = e.target.closest('.btn-imprimir-factura');
 
    if (btnVer) {
        if (mostrarFactura(parseInt(btnVer.dataset.id, 10))) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerFactura')).show();
        }
    }
 
    if (btnImp) {
        if (mostrarFactura(parseInt(btnImp.dataset.id, 10))) {
            /* se abre el modal para que el área imprimible sea visible */
            const modal = document.getElementById('modalVerFactura');
            modal.addEventListener('shown.bs.modal', function imprimir() {
                modal.removeEventListener('shown.bs.modal', imprimir);
                window.print();
            });
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    }
});
 
document.getElementById('btnImprimirFactura').addEventListener('click', function () {
    window.print();
});
 
