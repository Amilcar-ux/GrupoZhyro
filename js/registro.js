/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/JavaScript.js to edit this template
 */
document.addEventListener('DOMContentLoaded', () => {
  const togglePassword = document.getElementById('togglePassword');
  const password = document.getElementById('password');
  const confirmPassword = document.getElementById('confirm_password');
  const form = document.getElementById('formRegistro');

  togglePassword.addEventListener('click', () => {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    togglePassword.textContent = type === 'password' ? 'Mostrar' : 'Ocultar';
  });

  form.addEventListener('submit', (e) => {
    if (password.value !== confirmPassword.value) {
      e.preventDefault();
      alert('Las contraseñas no coinciden.');
      confirmPassword.focus();
    }
  });
});



