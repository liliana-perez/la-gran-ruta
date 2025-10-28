
    // Mejor manejo del modal: espera a DOMContentLoaded, bloquea scroll y gestiona foco.
    document.addEventListener('DOMContentLoaded', function() {
      const openBtn = document.getElementById('open-form-btn');
      const closeBtn = document.getElementById('close-form-btn');
      const cancelBtn = document.getElementById('cancel-form-btn');
      const panel = document.getElementById('panel-form');
      const backdrop = document.getElementById('modal-backdrop');
      const panelTitle = document.getElementById('panel-title');
      const form = document.getElementById('form-nomina');
      const formAction = document.getElementById('form-action');
      const empleadoId = document.getElementById('empleado-id');

      if (!panel || !backdrop || !openBtn) return;

      function openPanel() {
        panel.classList.add('open');
        backdrop.classList.add('open');
        panel.setAttribute('aria-hidden', 'false');
        backdrop.setAttribute('aria-hidden', 'false');
        document.body.classList.add('no-scroll');
        // focus primer campo para accesibilidad (esperar al final del frame)
        requestAnimationFrame(() => {
          const first = panel.querySelector('input, select, button');
          if (first) first.focus();
        });
      }

      function closePanel() {
        panel.classList.remove('open');
        backdrop.classList.remove('open');
        panel.setAttribute('aria-hidden', 'true');
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('no-scroll');
        openBtn.focus();
        // reset form to create mode
        form.reset();
        formAction.value = 'create';
        empleadoId.value = '';
        panelTitle.textContent = 'Registrar nuevo empleado';
      }

      openBtn.addEventListener('click', function() {
        // preparar formulario para crear
        formAction.value = 'create';
        empleadoId.value = '';
        panelTitle.textContent = 'Registrar nuevo empleado';
        openPanel();
      });
      closeBtn && closeBtn.addEventListener('click', closePanel);
      cancelBtn && cancelBtn.addEventListener('click', closePanel);
      backdrop.addEventListener('click', closePanel);

      // Edit buttons: abrir panel y rellenar datos
      document.querySelectorAll('.op-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
          const id = btn.getAttribute('data-id');
          const nombre = btn.getAttribute('data-nombre');
          const cargo = btn.getAttribute('data-cargo');
          const salario = btn.getAttribute('data-salario');
          const estado = btn.getAttribute('data-estado');

          // set values in the panel
          document.getElementById('nombre').value = nombre;
          document.getElementById('cargo').value = cargo;
          document.getElementById('salario').value = salario;
          document.getElementById('estado').value = estado;
          formAction.value = 'update';
          empleadoId.value = id;
          panelTitle.textContent = 'Editar empleado #' + id;
          openPanel();
        });
      });

      // Delete forms: confirm before submit
      document.querySelectorAll('.delete-form').forEach(function(f) {
        f.addEventListener('submit', function(e) {
          // show confirm dialog
          if (!confirm('¿Confirma que desea eliminar este empleado? Esta acción no se puede deshacer.')) {
            return false;
          }
          // if confirmed, submit normally
          f.submit();
          return true;
        });
      });

      // Esc para cerrar
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && panel.classList.contains('open')) {
          closePanel();
        }
      });

      // Evitar submit accidental al presionar Enter si el panel está cerrado
      document.addEventListener('submit', function(e) {
        // si el formulario está dentro del panel permitimos submit, si no lo está y panel abierto, cerrar
        // (no es estrictamente necesario, queda como protección)
      }, true);
    });
  