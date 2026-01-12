/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/JavaScript.js to edit this template
 */
document.addEventListener('DOMContentLoaded', () => {
  const entregaRadios = document.querySelectorAll('input[name="entrega"]');
  const costoEnvioSpan = document.getElementById('costo-envio');
  const totalConEnvioSpan = document.getElementById('total-con-envio');

  let subtotal = parseFloat(window.subtotal) || 0;
  let impuestos = parseFloat(window.impuestos) || 0;
  let costoEnvioBase = parseFloat(window.precioEnvioProvincia) || 0;

  function actualizarCostos() {
    let costoEnvio = costoEnvioBase;
    costoEnvioSpan.textContent = `S/ ${costoEnvio.toFixed(2)}`;
    const total = subtotal + impuestos + costoEnvio;
    totalConEnvioSpan.textContent = `S/ ${total.toFixed(2)}`;
  }

  entregaRadios.forEach(radio => radio.addEventListener('change', actualizarCostos));
  actualizarCostos();
});
