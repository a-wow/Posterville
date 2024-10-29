<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $db->query("SELECT posts.id, posts.content, posts.created_at, users.username, users.profile_image, users.background_image FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
$posts = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS</title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="/">Постервиль</a></li>
            <li><a href="profile">Настройки</a></li>
            <li><a href="logout">Выйти</a></li>
        </ul>
    </nav>

    <div class="posts">
        <?php foreach ($posts as $post): ?>
            <div class="post" style="background-image: url('<?= htmlspecialchars($post['background_image']) ?>');">
                <div class="post-header <?= !empty($post['background_image']) ? 'white-text' : '' ?>">
                    <img src="<?= htmlspecialchars($post['profile_image']) ?>" alt="Profile Image" class="profile-image">
                <!--    <span class="<?= !empty($post['background_image']) ? 'white-text' : '' ?>"><?= htmlspecialchars($post['username']) ?></span> -->
                    <a href="/@<?= htmlspecialchars($post['username']) ?>" class="<?= !empty($post['background_image']) ? 'white-text' : '' ?>"><?= htmlspecialchars($post['username']) ?></a>
                    <small class="post-date <?= !empty($post['background_image']) ? 'white-text' : '' ?>"><?= date('H:i', strtotime($post['created_at'])) ?></small>
                </div>
                <p class="<?= !empty($post['background_image']) ? 'white-text' : '' ?>"><?= htmlspecialchars($post['content']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="myModal" class="modal">
            <span id="closeModal" style="float:right;cursor:pointer;">&times;</span>
            <form id="postForm" method="post" action="create_post.php">
                <textarea name="content" placeholder="Что у тебя на уме?" required></textarea>
                <button type="submit">Опубликовать</button>
            </form>
    </div>

    <button class="create-post-btn" id="createPostBtn">+</button>

    <script>
        document.getElementById('createPostBtn').onclick = function() {
            document.getElementById('myModal').style.display = "block";
        }

        document.getElementById('closeModal').onclick = function() {
            document.getElementById('myModal').style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('myModal')) {
                document.getElementById('myModal').style.display = "none";
            }
        }
    </script>
</body>
</html>
