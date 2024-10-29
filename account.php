<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit;
}

$username = null;
$age = null;
$country = null;
$email = null;
$personal_website = null;
$github_profile = null;
$bio = null;
$current_image = null;

if (isset($_GET['username'])) {
    $requested_username = $_GET['username'];

    $stmt = $db->prepare("SELECT profile_image, age, country, email, personal_website, github_profile, bio FROM users WHERE username = ?");
    $stmt->bind_param("s", $requested_username);
    $stmt->execute();
    $stmt->bind_result($current_image, $age, $country, $email, $personal_website, $github_profile, $bio);
    $stmt->fetch();
    $stmt->close();

    if ($age === null) {
        header("Location: /");
        exit;
    }
} else {
    header("Location: /");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Аккаунт <?= htmlspecialchars($requested_username) ?></title>
    <link rel="stylesheet" href="styles/account.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="/">Постервиль</a></li>
            <li><a href="profile">Настройки</a></li>
            <li><a href="logout">Выйти</a></li>
        </ul>
    </nav>

     <div class="form-container">
        <h1><?= htmlspecialchars($requested_username) ?></h1>
        <?php if ($current_image): ?>
            <img src="<?= htmlspecialchars($current_image) ?>" alt="Изображение профиля" class="profile-image">
        <?php else: ?>
            <p>У этого пользователя нет изображения профиля.</p>
        <?php endif; ?>
        <h2>Информация</h2>
        <p><strong>Возраст:</strong> <?= htmlspecialchars($age) ?></p>
        <p><strong>Страна:</strong> <?= htmlspecialchars($country) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Личный сайт:</strong> <a href="<?= htmlspecialchars($personal_website) ?>" target="_blank"><?= htmlspecialchars($personal_website) ?></a></p>
        <p><strong>GitHub:</strong> <a href="<?= htmlspecialchars($github_profile) ?>" target="_blank"><?= htmlspecialchars($github_profile) ?></a></p>
        <p><strong>О себе:</strong> <?= nl2br(htmlspecialchars($bio)) ?></p>
    </div>
</body>
</html>
