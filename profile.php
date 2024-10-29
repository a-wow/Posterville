<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit;
}

$user_id = $_SESSION['user_id'];
$current_image = null;
$current_background_image = null;
$username = null;
$age = null;
$country = null;
$email = null;
$personal_website = null;
$github_profile = null;
$bio = null;

$stmt = $db->prepare("SELECT profile_image, background_image, username, age, country, email, personal_website, github_profile, bio FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($current_image, $current_background_image, $username, $age, $country, $email, $personal_website, $github_profile, $bio);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);

    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
        $stmt = $db->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->bind_param("si", $target_file, $user_id);
        $stmt->execute();
        $current_image = $target_file;
    }

    header("Location: profile");
    exit;
}

if (isset($_FILES['background_image'])) {
    $background_target_dir = "uploads/backgrounds/";
    $background_target_file = $background_target_dir . basename($_FILES["background_image"]["name"]);

    if (move_uploaded_file($_FILES["background_image"]["tmp_name"], $background_target_file)) {
        $stmt = $db->prepare("UPDATE users SET background_image = ? WHERE id = ?");
        $stmt->bind_param("si", $background_target_file, $user_id);
        $stmt->execute();
        $current_background_image = $background_target_file;
    }

    header("Location: profile");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_info'])) {
    $age = $_POST['age'];
    $country = $_POST['country'];
    $email = $_POST['email'];
    $personal_website = $_POST['personal_website'];
    $github_profile = $_POST['github_profile'];
    $bio = $_POST['bio'];

    $stmt = $db->prepare("UPDATE users SET age = ?, country = ?, email = ?, personal_website = ?, github_profile = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("isssssi", $age, $country, $email, $personal_website, $github_profile, $bio, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: profile");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Профиль</title>
    <link rel="stylesheet" href="styles/profile.css">
    <link rel="stylesheet" href="styles/profile2.css">
    <script>
        function toggleInfoContainer() {
            const modal = document.getElementById('infoModal');
            modal.style.display = (modal.style.display === 'none' || modal.style.display === '') ? 'block' : 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('infoModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function closeModal() {
            document.getElementById('infoModal').style.display = 'none';
        }
    </script>
</head>
<body>
    <nav>
        <ul>
            <li><a href="/">Постервиль</a></li>
            <li><a href="profile">Настройки</a></li>
            <li><a href="logout">Выйти</a></li>
        </ul>
    </nav>

    <h1><?= htmlspecialchars($username) ?></h1>

    <div class="form-container">
        <?php if ($current_image): ?>
            <img src="<?= htmlspecialchars($current_image) ?>" alt="Изображение профиля" class="profile-image">
            <p><strong>Ваш аватар:</strong></p>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="profile_image" required>
                <button type="submit">Изменить аватар</button>
            </form>
        <?php else: ?>
            <p>У вас еще нет изображения профиля.</p>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="profile_image" required>
                <button type="submit">Загрузить</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="form-container">
        <h2>Изменить фон</h2>
        <?php if ($current_background_image): ?>
            <img src="<?= htmlspecialchars($current_background_image) ?>" alt="Фон" style="width:100%; height:auto;">
            <p><strong>Ваш фон:</strong></p>
        <?php else: ?>
            <p>У вас еще нет фонового изображения.</p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="background_image" required>
            <button type="submit">Загрузить фон</button>
        </form>
    </div>

    <div id="infoModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <form method="post">
            <div class="form-group">
                <label for="age">Возраст:</label>
                <input type="number" name="age" required>
            </div>
            <div class="form-group">
                <label for="country">Страна:</label>
                <input type="text" name="country" required>
            </div>
            <div class="form-group">
                <label for="email">Почта:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="personal_website">Ссылка на личный сайт:</label>
                <input type="url" name="personal_website">
            </div>
            <div class="form-group">
                <label for="github_profile">Ссылка на GitHub:</label>
                <input type="url" name="github_profile">
            </div>
            <div class="form-group">
                <label for="bio">О себе:</label>
                <textarea name="bio" rows="4"></textarea>
            </div>
            <button type="submit" name="update_info">Сохранить</button>
        </form>
    </div>

    <button class="floating-button" onclick="toggleInfoContainer()">+</button>

</body>
</html>
