/* Controles de los inputs (se usa en los 4 formularios).
   Cada input lleva data-regla="id | nombre | creador | tipado | uso | fecha" */
(function () {
    var L = "A-Za-zÁÉÍÓÚÜÑáéíóúüñ";
    var reglas = {
        id: {
            permitido: /[^0-9]/g,
            valido: /^[1-9][0-9]{0,8}$/,
            msg: "Solo números enteros positivos, sin ceros a la izquierda, sin espacios ni signos (máx. 9 dígitos)."
        },
        nombre: {
            permitido: /[^A-Za-z0-9+#.\-]/g,
            valido: /^[A-Za-z0-9+#.\-]{1,30}$/,
            msg: "Una sola palabra, sin espacios. Solo letras, números y los símbolos + # . - (máx. 30)."
        },
        creador: {
            permitido: new RegExp("[^" + L + ".',\\- ]", "g"),
            valido: new RegExp("^[" + L + "][" + L + ".',\\-]*( [" + L + "][" + L + ".',\\-]*)*$"),
            min: 3, max: 60, espacios: true,
            msg: "Solo letras (sin números), espacios simples y . , ' - (3 a 60 caracteres)."
        },
        tipado: {
            permitido: new RegExp("[^" + L + ",\\- ]", "g"),
            valido: new RegExp("^[" + L + "][" + L + ",\\-]*( [" + L + "][" + L + ",\\-]*)*$"),
            min: 3, max: 40, espacios: true,
            msg: "Solo letras, espacios simples, coma y guion (3 a 40 caracteres). Ej.: Estático, fuerte."
        },
        uso: {
            permitido: new RegExp("[^" + L + "0-9.,/()+#\\- ]", "g"),
            valido: new RegExp("^[" + L + "0-9.,/()+#\\-]+( [" + L + "0-9.,/()+#\\-]+)*$"),
            min: 3, max: 100, espacios: true,
            msg: "Solo letras, números, espacios simples y . , / ( ) + # - (3 a 100 caracteres)."
        }
    };

    function hoy() {
        var d = new Date();
        return d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0") + "-" + String(d.getDate()).padStart(2, "0");
    }

    function revisar(inp, r) {
        var v = inp.value;
        if (v === "" && !inp.required) {
            inp.setCustomValidity("");
        } else if (r.min && v.trim().length < r.min) {
            inp.setCustomValidity(r.msg);
        } else if (!r.valido.test(v)) {
            inp.setCustomValidity(r.msg);
        } else {
            inp.setCustomValidity("");
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll("input[data-regla]").forEach(function (inp) {
            var nombre = inp.getAttribute("data-regla");

            if (nombre === "fecha") {
                inp.min = "1940-01-01";
                inp.max = hoy();
                inp.addEventListener("input", function () {
                    inp.setCustomValidity(
                        inp.value && (inp.value < inp.min || inp.value > inp.max)
                            ? "La fecha debe estar entre 1940-01-01 y hoy." : ""
                    );
                });
                return;
            }

            var r = reglas[nombre];
            if (!r) return;
            inp.autocomplete = "off";
            if (r.max) inp.maxLength = r.max;
            if (nombre === "id") { inp.maxLength = 9; inp.inputMode = "numeric"; }
            if (nombre === "nombre") inp.maxLength = 30;

            // Bloquea el espacio en los campos que no lo permiten
            if (!r.espacios) {
                inp.addEventListener("keydown", function (e) {
                    if (e.key === " ") e.preventDefault();
                });
            }

            // Quita lo no permitido al escribir o pegar
            inp.addEventListener("input", function () {
                var v = inp.value.replace(r.permitido, "");
                if (!r.espacios) v = v.replace(/\s/g, "");
                else v = v.replace(/^ +/, "").replace(/ {2,}/g, " ");
                if (v !== inp.value) inp.value = v;
                revisar(inp, r);
            });

            inp.addEventListener("blur", function () {
                inp.value = inp.value.trim();
                revisar(inp, r);
            });

            revisar(inp, r);
        });

        document.querySelectorAll("form").forEach(function (f) {
            f.addEventListener("submit", function (e) {
                f.querySelectorAll("input[data-regla]").forEach(function (i) {
                    i.value = i.value.trim();
                    i.dispatchEvent(new Event("input"));
                });
                if (!f.checkValidity()) { e.preventDefault(); f.reportValidity(); }
            });
        });
    });
})();
