/* =====================================================================
   js/validaciones.js  -  Tienda "Estilo Andino"
   1. Validación del formulario en el navegador
   2. Contador de caracteres de la descripción
   3. Filtro instantáneo del catálogo
   4. Confirmación antes de eliminar
   5. Cierre automático de los avisos
   ===================================================================== */
'use strict';

document.addEventListener('DOMContentLoaded', function () {

    /* ---------------------------------------------------------------
       1. VALIDACIÓN DEL FORMULARIO
       Nota: la validación real está en PHP (validar() del controlador);
       esta solo evita viajes innecesarios al servidor.
    --------------------------------------------------------------- */
    const formulario = document.getElementById('formProducto');

    if (formulario) {
        const reglas = {
            codigo: {
                prueba: (v) => /^[A-Za-z]{3}-\d{3}$/.test(v.trim()),
                mensaje: 'Use el formato ROP-011 (tres letras, guion, tres dígitos).'
            },
            nombre: {
                prueba: (v) => v.trim().length >= 3,
                mensaje: 'El nombre debe tener al menos 3 caracteres.'
            },
            precio: {
                prueba: (v) => v !== '' && !isNaN(parseFloat(v)) && parseFloat(v) > 0,
                mensaje: 'Ingrese un precio mayor que 0.'
            },
            stock: {
                prueba: (v) => v === '' || (Number.isInteger(Number(v)) && Number(v) >= 0),
                mensaje: 'El stock debe ser un número entero no negativo.'
            },
            id_categoria: {
                prueba: (v) => v !== '',
                mensaje: 'Seleccione una categoría.'
            }
        };

        /** Pinta u oculta el mensaje de error de un campo. */
        function marcar(campo, texto) {
            let aviso = campo.parentElement.querySelector('.error-js');

            if (texto === null) {
                campo.removeAttribute('aria-invalid');
                if (aviso) { aviso.remove(); }
                return true;
            }

            campo.setAttribute('aria-invalid', 'true');
            if (!aviso) {
                aviso = document.createElement('small');
                aviso.className = 'error error-js';
                campo.parentElement.appendChild(aviso);
            }
            aviso.textContent = texto;
            return false;
        }

        function validarCampo(nombre) {
            const campo = formulario.elements[nombre];
            const regla = reglas[nombre];
            if (!campo || !regla) { return true; }
            return regla.prueba(campo.value)
                ? marcar(campo, null)
                : marcar(campo, regla.mensaje);
        }

        // Valida mientras el usuario escribe / sale del campo
        Object.keys(reglas).forEach(function (nombre) {
            const campo = formulario.elements[nombre];
            if (!campo) { return; }
            campo.addEventListener('blur', () => validarCampo(nombre));
            campo.addEventListener('input', function () {
                if (campo.getAttribute('aria-invalid') === 'true') { validarCampo(nombre); }
            });
        });

        // El código siempre en mayúsculas
        const codigo = formulario.elements['codigo'];
        if (codigo) {
            codigo.addEventListener('input', function () {
                const posicion = codigo.selectionStart;
                codigo.value = codigo.value.toUpperCase();
                codigo.setSelectionRange(posicion, posicion);
            });
        }

        formulario.addEventListener('submit', function (evento) {
            let valido = true;
            Object.keys(reglas).forEach(function (nombre) {
                if (!validarCampo(nombre)) { valido = false; }
            });

            if (!valido) {
                evento.preventDefault();
                const primerError = formulario.querySelector('[aria-invalid="true"]');
                if (primerError) { primerError.focus(); }
            }
        });

        /* ------- 2. Contador de caracteres de la descripción ------- */
        const descripcion = formulario.elements['descripcion'];
        const contadorTexto = document.getElementById('contadorTexto');
        if (descripcion && contadorTexto) {
            const refrescar = () => { contadorTexto.textContent = String(descripcion.value.length); };
            descripcion.addEventListener('input', refrescar);
            refrescar();
        }
    }

    /* ---------------------------------------------------------------
       3. FILTRO INSTANTÁNEO DEL CATÁLOGO
    --------------------------------------------------------------- */
    const filtro   = document.getElementById('filtroVivo');
    const rejilla  = document.getElementById('rejilla');
    const contador = document.getElementById('contador');

    if (filtro && rejilla) {
        const tarjetas = Array.prototype.slice.call(rejilla.querySelectorAll('.tarjeta'));

        filtro.addEventListener('input', function () {
            const texto = filtro.value.trim().toLowerCase();
            let visibles = 0;

            tarjetas.forEach(function (tarjeta) {
                const coincide = tarjeta.dataset.nombre.indexOf(texto) !== -1;
                tarjeta.hidden = !coincide;
                if (coincide) { visibles++; }
            });

            if (contador) {
                contador.hidden = texto === '';
                contador.textContent = visibles + ' de ' + tarjetas.length + ' prendas coinciden con "' + filtro.value + '".';
            }
        });
    }

    /* ---------------------------------------------------------------
       4. CONFIRMACIÓN ANTES DE ELIMINAR (DELETE)
    --------------------------------------------------------------- */
    document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            if (!window.confirm(form.dataset.confirmar)) {
                evento.preventDefault();
            }
        });
    });

    /* ---------------------------------------------------------------
       5. AVISOS: cierre manual y automático
    --------------------------------------------------------------- */
    document.querySelectorAll('.aviso').forEach(function (aviso) {
        const cerrar = aviso.querySelector('.aviso__cerrar');
        if (cerrar) {
            cerrar.addEventListener('click', () => aviso.remove());
        }
        if (aviso.hasAttribute('data-autocerrar')) {
            window.setTimeout(function () {
                aviso.style.opacity = '0';
                window.setTimeout(() => aviso.remove(), 400);
            }, 5000);
        }
    });
});
