<?php
session_start();
require '../config/db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $db->prepare("SELECT age, country, email, personal_website, github_profile, bio FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_age, $current_country, $current_email, $current_personal_website, $current_github_profile, $current_bio);
    $stmt->fetch();
    $stmt->close();

    $age = !empty($_POST['age']) ? $_POST['age'] : $current_age;
    $country = !empty($_POST['country']) ? $_POST['country'] : $current_country;
    $email = !empty($_POST['email']) ? $_POST['email'] : $current_email;
    $personal_website = !empty($_POST['personal_website']) ? $_POST['personal_website'] : $current_personal_website;
    $github_profile = !empty($_POST['github_profile']) ? $_POST['github_profile'] : $current_github_profile;
    $bio = !empty($_POST['bio']) ? $_POST['bio'] : $current_bio;

    $stmt = $db->prepare("UPDATE users SET age = ?, country = ?, email = ?, personal_website = ?, github_profile = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("isssssi", $age, $country, $email, $personal_website, $github_profile, $bio, $user_id);
    $stmt->execute();

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);

        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $stmt = $db->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $user_id);
            $stmt->execute();
        }
    }

    if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] == 0) {
        $background_target_dir = "../uploads/backgrounds/";
        $background_target_file = $background_target_dir . basename($_FILES["background_image"]["name"]);

        if (move_uploaded_file($_FILES["background_image"]["tmp_name"], $background_target_file)) {
            $stmt = $db->prepare("UPDATE users SET background_image = ? WHERE id = ?");
            $stmt->bind_param("si", $background_target_file, $user_id);
            $stmt->execute();
        }
    }

    header("Location: /");
    exit();
}
