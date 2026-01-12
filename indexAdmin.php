<?php
session_start();
$errorMessage = $_SESSION['errorMessage'] ?? '';
unset($_SESSION['errorMessage']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login Administrador</title>
    <link rel="stylesheet" type="text/css" href="css/indexAdmin.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Login de Administrador</h2>
            <form method="post" action="loginAdmin.php">
                Email:
                <input type="text" name="email" placeholder="User Name (Email)" required><br>
                Contraseña:
                <input type="password" name="password" placeholder="Password" required><br>
                <input type="submit" value="SIGN IN">
            </form>
            <p class="error-message">
                <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>
    </div>
</body>
</html>
