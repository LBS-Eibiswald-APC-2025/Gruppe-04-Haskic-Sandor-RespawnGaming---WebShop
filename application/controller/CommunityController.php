<?php

class CommunityController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // Zeigt alle Community-Posts für ein bestimmtes Spiel
    public function gamePosts(int $game_id): void
    {
        $posts = CommunityModel::getPostsByGame($game_id);
        $this->View->render('community/game_posts', ['posts' => $posts, 'game_id' => $game_id]);
    }

    // Fügt einen neuen Community-Post hinzu
    public function addPost(): void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $game_id = $_POST['game_id'];
            $user_id = $_SESSION['user_id'];
            $post_type = $_POST['post_type'];
            $title = $_POST['title'];
            $content = $_POST['content'];

            if (CommunityModel::addPost($game_id, $user_id, $post_type, $title, $content)) {
                header("Location: /community/game/$game_id");
                exit();
            } else {
                header("Location: /community/game/$game_id?error=failed");
                exit();
            }
        }
    }

    // Löscht einen Community-Post
    public function deletePost(int $post_id): void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['role'];

        if (CommunityModel::deletePost($post_id, $user_id, $user_role)) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=delete_failed");
            exit();
        }
    }
}
