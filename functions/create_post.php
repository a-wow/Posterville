<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    preg_match_all('/@(\w+)/', $content, $mentions);

    $stmt = $db->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $content);
    $stmt->execute();
    $post_id = $stmt->insert_id;

    foreach ($mentions[1] as $mentioned_username) {
        $mention_stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $mention_stmt->bind_param("s", $mentioned_username);
        $mention_stmt->execute();
        $mention_result = $mention_stmt->get_result();
        $mentioned_user = $mention_result->fetch_assoc();

        if ($mentioned_user) {
            $insert_mention_stmt = $db->prepare("INSERT INTO mentions (post_id, mentioned_user_id) VALUES (?, ?)");
            $insert_mention_stmt->bind_param("ii", $post_id, $mentioned_user['id']);
            $insert_mention_stmt->execute();
        }
    }

    header("Location: /");
}
?>
