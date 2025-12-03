document.addEventListener('DOMContentLoaded', () => {
    const openFormBtn = document.getElementById('open-form-btn');
    const closeFormBtn = document.getElementById('close-form-btn');
    const cancelFormBtn = document.getElementById('cancel-form-btn');
    const panelForm = document.getElementById('panel-form');
    const modalBackdrop = document.getElementById('modal-backdrop');
    const formInventario = document.getElementById('form-inventario');
    const tablaInventario = document.getElementById('tabla-inventario');
    const panelTitle = document.getElementById('panel-title');

    // Inputs
    const editIdInput = document.getElementById('editId');
    const codigoInput = document.getElementById('codigo');
    const nombreInput = document.getElementById('nombre');
    const cantidadInput = document.getElementById('cantidad');
    const precioInput = document.getElementById('precio');

    // Cargar productos al inicio
    loadProducts();

    // --- Event Listeners ---

    openFormBtn.addEventListener('click', () => {
        resetForm();
        openPanel();
    });

    closeFormBtn.addEventListener('click', closePanel);
    cancelFormBtn.addEventListener('click', closePanel);
    modalBackdrop.addEventListener('click', closePanel);

    formInventario.addEventListener('submit', (e) => {
        e.preventDefault();
        saveProduct();
    });

    // Delegación de eventos para botones editar/eliminar en la tabla
    tablaInventario.addEventListener('click', (e) => {
        const target = e.target;
        // Botón Editar
        if (target.classList.contains('btn-edit')) {
            const row = target.closest('tr');
            const product = JSON.parse(row.dataset.product);
            fillForm(product);
            openPanel();
        }
        // Botón Eliminar
        if (target.classList.contains('btn-delete')) {
            const id = target.dataset.id;
            if (confirm('¿Estás seguro de eliminar este producto?')) {
                deleteProduct(id);
            }
        }
    });

    // --- Funciones ---

    function openPanel() {
        panelForm.classList.add('open');
        modalBackdrop.classList.add('visible');
        panelForm.setAttribute('aria-hidden', 'false');
        modalBackdrop.setAttribute('aria-hidden', 'false');
    }

    function closePanel() {
        panelForm.classList.remove('open');
        modalBackdrop.classList.remove('visible');
        panelForm.setAttribute('aria-hidden', 'true');
        modalBackdrop.setAttribute('aria-hidden', 'true');
    }

    function resetForm() {
        formInventario.reset();
        editIdInput.value = '';
        panelTitle.textContent = 'Registrar nuevo producto';
    }

    function fillForm(product) {
        editIdInput.value = product.id;
        codigoInput.value = product.codigo;
        nombreInput.value = product.nombre;
        cantidadInput.value = product.cantidad;
        precioInput.value = product.precio;
        panelTitle.textContent = 'Editar producto';
    }

    async function loadProducts() {
        try {
            const res = await fetch('inventario.php?load=1');
            if (!res.ok) throw new Error('Error al cargar productos');
            const products = await res.json();
            renderTable(products);
        } catch (err) {
            console.error(err);
            alert('Error al cargar el inventario.');
        }
    }

    function renderTable(products) {
        tablaInventario.innerHTML = '';
        if (products.length === 0) {
            tablaInventario.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay productos registrados.</td></tr>';
            return;
        }

        products.forEach(p => {
            const tr = document.createElement('tr');
            tr.dataset.product = JSON.stringify(p);
            tr.innerHTML = `
        <td data-label="ID">${p.id}</td>
        <td data-label="Código">${escapeHtml(p.codigo)}</td>
        <td data-label="Nombre">${escapeHtml(p.nombre)}</td>
        <td data-label="Cantidad">${p.cantidad}</td>
        <td data-label="Precio">$${p.precio}</td>
        <td data-label="Operaciones">
          <button class="menu-button btn-edit" style="padding: 6px 12px; font-size: 0.85rem; background: #1976d2; margin-right: 4px;">Editar</button>
          <button class="menu-button btn-delete" data-id="${p.id}" style="padding: 6px 12px; font-size: 0.85rem; background-color: #e74c3c;">Eliminar</button>
        </td>
      `;
            tablaInventario.appendChild(tr);
        });
    }

    async function saveProduct() {
        const id = editIdInput.value;
        const action = id ? 'update' : 'create';

        const formData = new FormData();
        formData.append('ajax', '1');
        formData.append('action', action);
        if (id) formData.append('id', id);
        formData.append('codigo', codigoInput.value);
        formData.append('nombre', nombreInput.value);
        formData.append('cantidad', cantidadInput.value);
        formData.append('precio', precioInput.value);

        try {
            const res = await fetch('inventario.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                closePanel();
                loadProducts();
            } else {
                alert('Error: ' + (data.error || 'Desconocido'));
            }
        } catch (err) {
            console.error(err);
            alert('Error de conexión al guardar.');
        }
    }

    async function deleteProduct(id) {
        const formData = new FormData();
        formData.append('ajax', '1');
        formData.append('action', 'delete');
        formData.append('id', id);

        try {
            const res = await fetch('inventario.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                loadProducts();
            } else {
                alert('Error: ' + (data.error || 'Desconocido'));
            }
        } catch (err) {
            console.error(err);
            alert('Error de conexión al eliminar.');
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
