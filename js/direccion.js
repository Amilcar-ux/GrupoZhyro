/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/JavaScript.js to edit this template
 */
$(document).ready(function() {
  $('#provincia').on('change', function() {
    const provinciaId = $(this).val();
    if (provinciaId) {
      $.ajax({
        url: 'cargarDistritos.php',
        method: 'GET',
        data: { provinciaId: provinciaId },
        dataType: 'json',
        success: function(data) {
          const $distrito = $('#distrito');
          $distrito.empty();
          $distrito.append('<option value="">Seleccione un distrito</option>');
          data.forEach(function(distrito) {
            $distrito.append(`<option value="${distrito.id}">${distrito.nombre}</option>`);
          });
        },
        error: function() {
          alert('Error al cargar los distritos');
        }
      });
    } else {
      $('#distrito').empty().append('<option value="">Seleccione un distrito</option>');
    }
  });
});


