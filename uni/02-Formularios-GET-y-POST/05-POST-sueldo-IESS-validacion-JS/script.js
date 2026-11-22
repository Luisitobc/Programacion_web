const formulario = document.getElementById("formulario");

formulario.addEventListener("submit", function(event) {

    const nombre = document.getElementById("nombre").value.trim();
    const sueldo = parseFloat(document.getElementById("sueldo").value);
    const horasExtras = parseInt(
        document.getElementById("horasExtras").value
    );
    const valorHora = parseFloat(
        document.getElementById("valorHora").value
    );
    const tipo = document.getElementById("tipo").value;

    if (nombre === "") {
        alert("Ingrese el nombre del empleado.");
        event.preventDefault();
        return;
    }

    if (isNaN(sueldo) || sueldo <= 0) {
        alert("Ingrese un sueldo básico válido.");
        event.preventDefault();
        return;
    }

    if (isNaN(horasExtras) || horasExtras < 0) {
        alert("Ingrese una cantidad válida de horas extras.");
        event.preventDefault();
        return;
    }

    if (isNaN(valorHora) || valorHora < 0) {
        alert("Ingrese un valor válido para la hora extra.");
        event.preventDefault();
        return;
    }

    if (tipo === "") {
        alert("Seleccione el tipo de empleado.");
        event.preventDefault();
        return;
    }

});