/* Comportamiento del shell de la aplicación (reemplaza a adminlte.js): menú lateral,
   submenús y tarjetas plegables/removibles. Requiere jQuery (ya cargado en base.html.twig). */
(function ($) {
  'use strict';

  // Menú lateral en móvil: se abre con el botón de la barra superior y se cierra con el fondo o Esc.
  var $sidebar = $('#appSidebar'), $fondo = $('#appSidebarFondo');
  function abrirMenu() { $sidebar.removeClass('-translate-x-full'); $fondo.removeClass('hidden'); }
  function cerrarMenu() { $sidebar.addClass('-translate-x-full'); $fondo.addClass('hidden'); }
  $(document).on('click', '[data-sidebar-toggle]', function (e) { e.preventDefault(); $sidebar.hasClass('-translate-x-full') ? abrirMenu() : cerrarMenu(); });
  $fondo.on('click', cerrarMenu);
  $(document).on('keydown', function (e) { if (e.key === 'Escape') { cerrarMenu(); } });

  // Submenús: se abren/cierran al hacer clic; el que contiene la página activa arranca abierto.
  $('#appSidebar .nav-item.has-treeview').each(function () {
    if ($(this).find('.nav-treeview .nav-link.active').length) { $(this).addClass('menu-open'); }
  });
  $(document).on('click', '#appSidebar .has-treeview > .nav-link', function (e) {
    e.preventDefault();
    $(this).parent().toggleClass('menu-open');
  });

  // Tarjetas: data-card-widget="collapse" pliega/despliega el cuerpo; "remove" la oculta.
  $(document).on('click', '[data-card-widget="collapse"]', function (e) {
    e.preventDefault();
    var $card = $(this).closest('.card');
    $card.toggleClass('collapsed-card');
    $(this).find('i').toggleClass('fa-plus', $card.hasClass('collapsed-card')).toggleClass('fa-minus', !$card.hasClass('collapsed-card'));
  });
  $(document).on('click', '[data-card-widget="remove"]', function (e) {
    e.preventDefault();
    $(this).closest('.card').remove();
  });
})(jQuery);

/* Paneles laterales: [data-side-panel-open="<id>"] abre el panel con ese id; se cierra con [data-side-panel-close],
   clic en el fondo o Esc, y el foco vuelve al botón que lo abrió. */
(function ($) {
  'use strict';
  var abierto = null, disparador = null;
  function cerrar() {
    if (!abierto) { return; }
    abierto.hidden = true;
    abierto = null;
    if (disparador) { disparador.focus(); disparador = null; }
  }
  $(document).on('click', '[data-side-panel-open]', function () {
    var raiz = document.getElementById($(this).data('side-panel-open'));
    if (!raiz) { return; }
    if (raiz.parentNode !== document.body) { document.body.appendChild(raiz); } // ningún contenedor recorta el panel fijo
    disparador = this;
    abierto = raiz;
    raiz.hidden = false;
    var foco = raiz.querySelector('[data-side-panel-focus]') || raiz.querySelector('.side-panel');
    if (foco) { foco.focus(); }
  });
  $(document).on('click', '[data-side-panel-close]', cerrar);
  $(document).on('keydown', function (e) { if (e.key === 'Escape') { cerrar(); } });
})(jQuery);

/* Validación del correo del cliente: los campos con [data-validar-correo] se revisan al pulsar Guardar. Si el formato
   no es válido no se envía el formulario: el foco va al campo y el error se muestra debajo. Mismas reglas que en el
   servidor (App\Form\CorreoClienteConstraints). Se engancha en fase de captura para ir antes de la validación nativa
   del navegador y de los demás manejadores (que, p. ej., deshabilitan los botones al enviar). */
(function () {
  'use strict';
  var FORMATO = /^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$/;
  var MSG_VACIO = 'Debe ingresar el correo del cliente.';
  var MSG_FORMATO = 'El correo ingresado no tiene un formato válido (ej. nombre@dominio.cl).';

  function visible(el) { return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length); }

  function mostrarError(input, mensaje) {
    var caja = input._errorCorreo;
    if (!caja) {
      caja = document.createElement('div');
      caja.className = 'invalid-feedback d-block';
      caja.setAttribute('role', 'alert');
      var ancla = input.closest('.input-group') || input;
      ancla.parentNode.insertBefore(caja, ancla.nextSibling);
      input._errorCorreo = caja;
    }
    caja.textContent = mensaje;
    input.classList.add('is-invalid');
    input.setAttribute('aria-invalid', 'true');
  }

  function quitarError(input) {
    if (input._errorCorreo) { input._errorCorreo.remove(); input._errorCorreo = null; }
    input.classList.remove('is-invalid');
    input.removeAttribute('aria-invalid');
  }

  /** true si todos los correos del formulario son válidos; si no, deja el error y el foco en el primero inválido. */
  function validarFormulario(form) {
    var campos = form.querySelectorAll('input[data-validar-correo]');
    for (var i = 0; i < campos.length; i++) {
      var input = campos[i];
      if (input.disabled || !visible(input)) { continue; }
      var valor = input.value.trim();
      var mensaje = valor === '' ? MSG_VACIO : (FORMATO.test(valor) ? '' : MSG_FORMATO);
      if (mensaje) {
        mostrarError(input, mensaje);
        input.focus();
        return false;
      }
      quitarError(input);
    }
    return true;
  }

  // Clic en un botón de envío (también cuando se envía con Enter: el navegador simula ese clic).
  document.addEventListener('click', function (e) {
    var boton = e.target.closest && e.target.closest('button, input[type="submit"]');
    if (!boton || boton.type !== 'submit' || boton.disabled || !boton.form) { return; }
    if (!validarFormulario(boton.form)) { e.preventDefault(); e.stopImmediatePropagation(); }
  }, true);

  // Envíos sin botón (requestSubmit, formularios enviados por script de eventos).
  document.addEventListener('submit', function (e) {
    if (!validarFormulario(e.target)) { e.preventDefault(); e.stopImmediatePropagation(); }
  }, true);

  // El error desaparece en cuanto el usuario vuelve a escribir.
  document.addEventListener('input', function (e) {
    if (e.target.matches && e.target.matches('input[data-validar-correo]')) { quitarError(e.target); }
  });
})();