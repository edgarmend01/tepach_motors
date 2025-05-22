<?php

/* poner esto en la base de datos
USE Produccion
GO

CREATE PROCEDURE ComprobarLogin
    @usuario VARCHAR(50),
    @contrasena VARCHAR(50)
AS
BEGIN
    SET NOCOUNT ON;

    -- Abrir la Master Key con su contraseña
    OPEN MASTER KEY DECRYPTION BY PASSWORD = 'Cur1p4p7s';

    -- Abrir la clave simétrica
    OPEN SYMMETRIC KEY LlaveSimetrica_curipapu
    DECRYPTION BY CERTIFICATE Certificado_Para_Cifrado;

    -- Comparar la contraseña desencriptada
    SELECT *
    FROM tbusr
    WHERE usr = @usuario
      AND CONVERT(VARCHAR(50), DecryptByKey(pwd_encrip)) = @contrasena;

    -- Cerrar claves (opcional pero recomendable)
    CLOSE SYMMETRIC KEY MiClaveSimetrica;
    CLOSE MASTER KEY;
END;
*/

include("conexion.php");

session_start(); // Siempre inicia la sesión al principio

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verificar si se enviaron los datos del formulario
    if (isset($_POST['usr']) && isset($_POST['pwd_encrip'])) {
        $Usuario = $_POST['usr'];
        $pwdU = $_POST['pwd_encrip'];

        $sql = "EXEC ComprobarLogin ?, ?";
        $params = array($Usuario, $pwdU);
        $result = sqlsrv_query($conectar, $sql, $params);
        
        if ($result === false) {
            die("Error en la consulta: " . print_r(sqlsrv_errors(), true));
        }
        
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        
        if ($row) {
            // Login exitoso
            $_SESSION['valid_user'] = true;
            $_SESSION['logged_user'] = $Usuario;
            header('Location: inventario.php');
            exit;
        } else {
            $mensaje = "❌ Credenciales inválidas";
        }
    } else {
        $mensaje = "❗ Faltan datos del formulario.";
    }
}
sqlsrv_close($conectar);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Formulario autos chidos</h2>

    <?php if (!empty($mensaje)) echo "<p style='color: red;'>$mensaje</p>"; ?>

    <form method="post" action="">
        Usuario: <input type="text" name="usr" required><br>
        Contraseña: <input type="password" name="pwd_encrip" required><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>


