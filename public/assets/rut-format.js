/**
 * Da formato de RUT chileno (12.345.678-K) mientras se escribe, sin dependencias.
 * Uso: <input data-rut-format> (se aplica solo al cargar la página).
 *
 * Sólo cambia lo que se ve: el servidor siempre compara el RUT sin puntos ni guion
 * (ver LoginAuthenticator::normalizarRut), así que ambos formatos son válidos.
 */
(function () {
    function formatear(valor) {
        var limpio = String(valor).replace(/[^0-9kK]/g, '').toUpperCase();
        // La K sólo puede ser el dígito verificador (último carácter).
        limpio = limpio.replace(/K(?=.)/g, '').slice(0, 9);
        if (limpio.length <= 1) {
            return limpio;
        }
        var cuerpo = limpio.slice(0, -1);
        var dv = limpio.slice(-1);
        return cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + dv;
    }

    function aplicar(input) {
        input.addEventListener('input', function () {
            var antes = input.value;
            var despues = formatear(antes);
            if (antes !== despues) {
                input.value = despues;
            }
        });
        if (input.value) {
            input.value = formatear(input.value);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-rut-format]').forEach(aplicar);
    });
})();
