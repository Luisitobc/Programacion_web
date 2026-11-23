// Validaciones del lado del cliente (el servidor también valida).

function validarProducto(f) {
  if (f.nombre.value.trim() === '') {
    alert('El nombre es obligatorio.');
    return false;
  }
  if (parseFloat(f.precio.value) > 0 === false) {
    alert('El precio debe ser mayor que 0.');
    return false;
  }
  if (f.stock && (f.stock.value === '' || parseInt(f.stock.value, 10) < 0)) {
    alert('El stock no puede ser negativo.');
    return false;
  }
  if (f.stock_minimo.value === '' || parseInt(f.stock_minimo.value, 10) < 0) {
    alert('El stock mínimo no puede ser negativo.');
    return false;
  }
  return true;
}

function validarCantidad(f) {
  if (!(parseInt(f.cantidad.value, 10) > 0)) {
    alert('Ingresa una cantidad mayor que 0.');
    return false;
  }
  return true;
}
