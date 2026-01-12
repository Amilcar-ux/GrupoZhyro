/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/JavaScript.js to edit this template
 */
 /* global idProducto */

function mostrarGuiaTallas() {
    alert("Guía de tallas:\nS = Pequeña\nM = Mediana\nL = Grande\nXL = Extra Grande");
  }


function agregarFavorito(idProducto) {
  if (!idProducto) {
    alert("ID de producto inválido");
    return;
  }
  fetch('agregarFavorito', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `id=${encodeURIComponent(idProducto)}`
  })
  .then(response => {
    if (response.ok) alert('Producto agregado a favoritos');
    else alert('Error al agregar favorito');
  })
  .catch(error => {
    console.error('Error en fetch:', error);
    alert('Error en la petición');
  });
}


