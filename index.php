<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $db->query("
    SELECT posts.id, posts.content, posts.created_at, users.username, users.profile_image, users.background_image
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC
");
$posts = $stmt->fetch_all(MYSQLI_ASSOC);

foreach ($posts as &$post) {
    $stmt = $db->prepare("
        SELECT comments.content, comments.created_at, users.username, users.profile_image 
        FROM comments 
        JOIN users ON comments.user_id = users.id 
        WHERE comments.post_id = ?
    ");
    $stmt->bind_param("i", $post['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $post['comments'] = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posterville</title>
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

    <div class="posts">
        <?php foreach ($posts as $post): ?>
            <div class="post" style="background-image: url('<?= htmlspecialchars($post['background_image']) ?>');">
                <div class="post-header <?= !empty($post['background_image']) ? 'white-text' : '' ?>">
                    <img src="<?= htmlspecialchars($post['profile_image']) ?>" alt="Profile Image" class="profile-image">
                    <a href="/@<?= htmlspecialchars($post['username']) ?>" class="<?= !empty($post['background_image']) ? 'white-text' : '' ?>">
                        <?= htmlspecialchars($post['username']) ?>
                    </a>
                    <small class="post-date <?= !empty($post['background_image']) ? 'white-text' : '' ?>">
                        <?= date('H:i', strtotime($post['created_at'])) ?>
                    </small>
                </div>
                <p class="<?= !empty($post['background_image']) ? 'white-text' : '' ?>">
                    <?= htmlspecialchars($post['content']) ?>
                </p>

                <button class="discussion-btn" data-post-id="<?= $post['id'] ?>">Обсуждение</button>
            </div>

            <div id="modal-<?= $post['id'] ?>" class="modal">
                <span class="closeModal" style="float:right;cursor:pointer;">&times;</span>
                <div class="modal-content">
                    <form class="comment-form" method="post" action="functions/create_comment.php">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <textarea name="content" placeholder="Оставьте комментарий..." required></textarea>
                        <button type="submit">Отправить</button>
                    </form>
                    <div class="comments">
                        <?php 
                        $reversed_comments = array_reverse($post['comments']);
                        foreach ($reversed_comments as $comment): ?>
                            <div class="comment">
                                <img src="<?= htmlspecialchars($comment['profile_image']) ?>" alt="Profile Image" class="profile-image">
                                <div class="comment-content">
                                    <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                                    <p><?= htmlspecialchars($comment['content']) ?></p>
                                </div>
                                <small class="comment-date"><?= date('H:i', strtotime($comment['created_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div> 
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="profileEditModal" class="modal">
        <div class="modal-content">
            <span id="closeProfileEditModal" class="closeModal" aria-label="Close Modal">&times;</span>
            <h2>Редактирование профиля</h2>
            <form id="profileEditForm" method="post" action="functions/profile.php" enctype="multipart/form-data">
                <label for="age">Возраст:</label>
                <input type="number" id="age" name="age" placeholder="Ваш возраст...">

                <label for="country">Страна:</label>
                <input type="text" id="country" name="country" placeholder="Ваша страна...">

                <label for="email">Почта:</label>
                <input type="email" id="email" name="email" placeholder="example@mail.com">

                <label for="personal_website">Персональный сайт:</label>
                <input type="url" id="personal_website" name="personal_website" placeholder="https://example.com">

                <label for="github_profile">GitHub профиль:</label>
                <input type="url" id="github_profile" name="github_profile" placeholder="https://github.com/username">

                <label for="bio">Информация о себе:</label>
                <textarea id="bio" name="bio" placeholder="Расскажите о себе..."></textarea>

                <button type="submit">Сохранить изменения</button>
            </form>
        </div>
    </div>

    <div id="imageUploadModal" class="modal">
        <div class="modal-content">
            <span id="closeImageUploadModal" class="closeModal" aria-label="Close Modal">&times;</span>
            <h2>Загрузка изображений</h2>

            <form id="imageUploadForm" method="post" action="functions/profile.php" enctype="multipart/form-data">
                <label for="profile_image">Изображение профиля:</label>
                <input type="file" id="profile_image" name="profile_image">

                <label for="background_image">Фоновое изображение:</label>
                <input type="file" id="background_image" name="background_image">

                <button type="submit">Загрузить изображения</button>
            </form>
        </div>
    </div>

    <button class="edit-profile-btn" id="editProfileBtn">✎</button>
    <button class="upload-image-btn" id="uploadImageBtn">🖼️</button>

    <div id="myModal" class="modal">
        <span id="closeModal" style="float:right;cursor:pointer;">&times;</span>
        <form id="postForm" method="post" action="functions/create_post.php">
            <textarea name="content" placeholder="Что у тебя на уме?" required></textarea>
            <button type="submit">Опубликовать</button>
        </form>
    </div>

    <button class="create-post-btn" id="createPostBtn">+</button>

    <script>
        document.querySelectorAll('.discussion-btn').forEach(button => {
            button.onclick = function() {
                const postId = this.getAttribute('data-post-id');
                document.getElementById(`modal-${postId}`).style.display = "block";
            }
        });

        document.querySelectorAll('.closeModal').forEach(closeButton => {
            closeButton.onclick = function() {
                this.parentElement.parentElement.style.display = "none";
            }
        });

        window.onclick = function(event) {
            document.querySelectorAll('.modal').forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            });
        }

        document.getElementById('createPostBtn').onclick = function() {
            document.getElementById('myModal').style.display = "block";
        }

        document.getElementById('closeModal').onclick = function() {
            document.getElementById('myModal').style.display = "none";
        }

        document.getElementById('editProfileBtn').onclick = function() {
            document.getElementById('profileEditModal').style.display = "block";
        }

        document.getElementById('closeProfileEditModal').onclick = function() {
            document.getElementById('profileEditModal').style.display = "none";
        }

        document.getElementById('uploadImageBtn').onclick = function() {
            document.getElementById('imageUploadModal').style.display = "block";
        }

        document.getElementById('closeImageUploadModal').onclick = function() {
            document.getElementById('imageUploadModal').style.display = "none";
        }
    </script>
</body>
</html>
