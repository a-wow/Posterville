<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
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
    $stmt = $db->prepare("SELECT comments.content, comments.created_at, users.username, users.profile_image 
                           FROM comments 
                           JOIN users ON comments.user_id = users.id 
                           WHERE comments.post_id = ?");
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
                    <a href="/@<?= htmlspecialchars($post['username']) ?>" class="<?= !empty($post['background_image']) ? 'white-text' : '' ?>"><?= htmlspecialchars($post['username']) ?></a>
                    <small class="post-date <?= !empty($post['background_image']) ? 'white-text' : '' ?>"><?= date('H:i', strtotime($post['created_at'])) ?></small>
                </div>
                <p class="<?= !empty($post['background_image']) ? 'white-text' : '' ?>"><?= htmlspecialchars($post['content']) ?></p>

                <!-- Кнопка -->
                <button class="discussion-btn" data-post-id="<?= $post['id'] ?>">Обсуждение</button>
            </div>

            <!-- Модель комментариев -->
            <div id="modal-<?= $post['id'] ?>" class="modal">
                <span class="closeModal" style="float:right;cursor:pointer;">&times;</span>
                <div class="modal-content">
                    <form class="comment-form" method="post" action="create_comment.php">
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

    <div id="myModal" class="modal">
        <span id="closeModal" style="float:right;cursor:pointer;">&times;</span>
        <form id="postForm" method="post" action="create_post.php">
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
    </script>
</body>
</html>
