/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/JavaScript.js to edit this template
 */
document.addEventListener('DOMContentLoaded', () => {
  const botonesCarrito = document.querySelectorAll('.btn-carrito');

  botonesCarrito.forEach(boton => {
    boton.addEventListener('click', (e) => {
      e.preventDefault();
      const url = boton.getAttribute('href');
      // Aquí puedes agregar funcionalidad adicional, como agregar producto a sesión o localStorage
      // Luego redirigir a la página de detalle
      window.location.href = url;
    });
  });
});


