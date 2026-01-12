<footer class="footer-electromania">
  <div class="footer-container">
    <div class="footer-column contacto">
      <h4>CONTACTO</h4>
      <p>La Victoria <br>Lima / Perú</p>
      <p><span class="footer-icon">☎</span> Número de telefono: 923 932 945</p>
      <p>Horario de atención:<br>Dom: 24 horas <br>L-V: 5:00 am - 12:00 pm</p>
      <p><span class="footer-icon">✉</span>
        <a href="mailto:zhyrope@gmail.com">zhyrope@gmail.com</a>
      </p>
    </div>

    <div class="footer-column tipos-envios">
      <h4>TIPOS DE ENVÍOS</h4>
      <p><strong>Envíos a todo el Perú</strong></p>
      <p>Envios a todo Lima desde 10 soles</p>
      <p><a href="guiaCompra.php" class="guia-compra-link">Guía para comprar aquí</a></p>
    </div>

    <div class="footer-column extras">
      <div><span class="footer-icon">💳</span> Pagos seguros</div>
      <div><span class="footer-icon">💬</span> 24/7 soporte</div>
      <div><span class="footer-icon">🚗</span> Entregas a domicilio</div>
      <div><span class="footer-icon">🔄</span> Devolución 3 días</div>
    </div>

    <div class="footer-column footer-logo">
      <img src="images/logo.png" alt="Logo de la tienda" />
    </div>
  </div>

  <div class="footer-copy">
    Derechos reservados © 2025 | Creado por Grupo Zhyro
  </div>

  <style>
      .footer-logo img {
        width: 70px;
        height: auto;
        max-width: 100%;
        border-radius: 8px;
        filter: none;
        transition: transform 0.3s ease;
      }

      .footer-logo img:hover {
        transform: scale(1.05);
      }

      .footer-electromania {
        background: #003247;
        color: #dceaf5;
        margin-top: 40px;
        font-size: 15px;
        padding: 36px 20px 20px 20px;
        border-radius: 16px 16px 0 0;
        font-family: 'Garamond', serif;
        letter-spacing: 0.025em;
        box-shadow: 0 4px 10px rgba(0, 50, 71, 0.7);
      }

      .footer-electromania a {
        color: #a3c4dc;
        text-decoration: underline;
        font-weight: 700;
        transition: color 0.3s ease;
      }

      .footer-electromania a:hover {
        color: #6187a6;
        text-decoration: none;
      }

      .footer-column h4 {
        color: #8eb4d0;
        text-transform: uppercase;
        font-size: 17px;
        margin-bottom: 14px;
        letter-spacing: 1.7px;
        font-weight: 700;
      }

      .guia-compra-link {
        color: #a3c4dc;
        font-weight: 700;
        text-decoration: underline;
        cursor: pointer;
        transition: color 0.3s ease;
      }

      .guia-compra-link:hover {
        color: #6187a6;
        text-decoration: none;
      }

      .footer-copy {
        border-top: 1px solid #1f3e54;
        color: #a3c4dc;
        font-size: 13px;
        margin-top: 28px;
        padding: 18px 0 4px 0;
        font-style: italic;
        letter-spacing: 0.08em;
      }

      .footer-icon {
        color: #a3c4dc;
        font-size: 20px;
        margin-right: 8px;
        vertical-align: middle;
        transition: color 0.3s ease;
      }

      .footer-electromania div.footer-column.extras div:hover .footer-icon {
        color: #6187a6;
      }

      .footer-container {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
        gap: 20px;
      }

      .footer-column {
        flex: 1;
        min-width: 200px;
      }

      @media (min-width: 901px) {
        .footer-container {
          flex-direction: row;
          align-items: flex-start;
        }
      }

      @media (max-width: 900px) {
        .footer-container {
          flex-direction: column;
          gap: 25px;
          align-items: flex-start;
        }
        .footer-logo {
          justify-content: flex-start;
        }
      }
  </style>
</footer>
