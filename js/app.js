/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/JavaScript.js to edit this template
 */
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('cookieModal');
  const acceptBtn = document.getElementById('acceptBtn');
  const rejectBtn = document.getElementById('rejectBtn');

  // Revisar si ya se eligió antes
  const cookieConsent = localStorage.getItem('cookieConsent');
  if (cookieConsent === 'accepted' || cookieConsent === 'rejected') {
    modal.style.display = 'none'; // No mostrar modal
  }

  acceptBtn.addEventListener('click', () => {
    localStorage.setItem('cookieConsent', 'accepted');
    modal.style.display = 'none';
  });

  rejectBtn.addEventListener('click', () => {
    localStorage.setItem('cookieConsent', 'rejected');
    modal.style.display = 'none';
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const searchIcon = document.getElementById('searchIcon');
  const searchForm = document.getElementById('searchForm');

  searchIcon.addEventListener('click', () => {
    searchForm.classList.toggle('show'); // alterna clase que muestra el formulario
  });
});



