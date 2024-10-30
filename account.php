<?php
session_start();
require 'config/db.php';

//if (!isset($_SESSION['user_id'])) {
//    header("Location: auth");
//    exit;
//}

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
    <title>Профиль <?= htmlspecialchars($requested_username) ?></title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <nav>
        <ul>
            <li>
                <a href="/"><img src="uploads/logo/logo.png" alt="Постервиль" style="height: 40px; width: auto;" /><a>
            </li>
            <li style="position: absolute; top: 10px; right: 10px;">
                <button id="logoutBtn" class="logout-button" onclick="window.location.href='logout'">&times;</button>
            </li>
        </ul>
    </nav>

    <div class="account">
        <h1><?= htmlspecialchars($requested_username) ?></h1>
        <?php if ($current_image): ?>
            <img src="<?= htmlspecialchars($current_image) ?>" alt="Изображение профиля" class="account-profile-image">
        <?php else: ?>
            <p>У этого пользователя нет изображения профиля.</p>
        <?php endif; ?>
        <div class="information">
            <h2>Информация:</h2>
            <p>Возраст:</strong> <?= htmlspecialchars($age) ?></p>
            <p><strong>Страна:</strong> <?= htmlspecialchars($country) ?></p>
            <p><strong>E-mail:</strong> <?= htmlspecialchars($email) ?></p>
            <p><strong>Личный сайт:</strong> <a href="<?= htmlspecialchars($personal_website) ?>" target="_blank"><?= htmlspecialchars($personal_website) ?></a></p>
            <p><strong>GitHub:</strong> <a href="<?= htmlspecialchars($github_profile) ?>" target="_blank"><?= htmlspecialchars($github_profile) ?></a></p>
            <p><strong>О себе:</strong> <?= nl2br(htmlspecialchars($bio)) ?></p>
        </div>
    </div>
</body>
</html>
