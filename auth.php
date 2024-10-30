<?php
session_start();
require 'config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: /");
            exit();
        } else {
            $message = "<div class='error-message'>Неверное имя пользователя или пароль</div>";
        }
    } elseif (isset($_POST['register'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $profileImage = 'uploads/user.jpg';

        $stmt = $db->prepare("INSERT INTO users (username, password, profile_image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $profileImage);
        $stmt->execute();

        header("Location: auth");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход и Регистрация</title>
    <link rel="stylesheet" href="styles/main.css">
    <script>
        function showForm(formId) {
            document.getElementById('loginForm').style.display = formId === 'login' ? 'block' : 'none';
            document.getElementById('registerForm').style.display = formId === 'register' ? 'block' : 'none';
        }
    </script>
</head>
<body onload="showForm('login')">
    <div class="login-container">
        <h2 id="formTitle">Войти в систему</h2>
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        
        <div id="loginForm" class="form">
            <form method="post">
                <input type="text" name="username" placeholder="Логин" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <input type="hidden" name="login" value="1">
                <button type="submit">Войти</button>
            </form>
            <a href="#" onclick="showForm('register');">Зарегистрироваться</a>
        </div>

        <div id="registerForm" class="form" style="display: none;">
            <form method="post">
                <input type="text" name="username" placeholder="Логин" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <input type="hidden" name="register" value="1">
                <button type="submit">Регистрация</button>
            </form>
            <a href="#" onclick="showForm('login');">У меня уже есть аккаунт</a>
        </div>
    </div>
</body>
</html>
