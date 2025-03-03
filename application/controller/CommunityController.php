<?php

use JetBrains\PhpStorm\NoReturn;

class CommunityController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Diese Methode zeigt die Community-Startseite
     * unter application/view/community/index.php an.
     * Damit funktioniert der Aufruf /community oder /community/index
     * ohne 404-Fehler.
     */
    public function index(): void
    {
        // Lädt deine View-Datei community/index.php
        // (Dort hast du z. B. dein "Willkommen im Community-Bereich"-HTML)
        $this->View->render('community/index');
    }

    /**
     * Zeigt alle Community-Posts für ein bestimmtes Spiel (z.B. /community/gamePosts/15)
     */
    public function gamePosts(int $game_id): void
    {
        $posts = CommunityModel::getPostsByGame($game_id);
        // Rendert die View community/game_posts.php mit den Posts
        $this->View->render('community/game_posts', [
            'posts' => $posts,
            'game_id' => $game_id
        ]);
    }

    /**
     * Fügt einen neuen Community-Post hinzu (wird i. d. R. per POST angesprochen)
     */
    public function addPost(): void
    {
        // Session nur einmal global starten (wenn noch nicht geschehen)
        session_start();

        // Prüfen, ob eingeloggt
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }

        // Nur bei POST-Anfragen
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Daten aus dem POST-Formular
            $game_id   = $_POST['game_id'];
            $user_id   = $_SESSION['user_id'];
            $post_type = $_POST['post_type'];
            $title     = $_POST['title'];
            $content   = $_POST['content'];

            // Model-Aufruf
            if (CommunityModel::addPost($game_id, $user_id, $post_type, $title, $content)) {
                // Erfolgreich hinzugefügt → zurück zur Spiele-Seite /community/games/<ID>
                header("Location: /community/games/$game_id");
            } else {
                // Fehlerfall
                header("Location: /community/games/$game_id?error=failed");
            }
            exit();
        }
    }

    /**
     * Löscht einen Community-Post (nur für den Besitzer/Moderator/Admin)
     */
    #[NoReturn] public function deletePost(int $post_id): void
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }

        $user_id  = $_SESSION['user_id'];
        $user_role = $_SESSION['role'] ?? null;  // Falls du 'role' so speicherst

        // Model-Aufruf zum Löschen
        if (CommunityModel::deletePost($post_id, $user_id, $user_role)) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=delete_failed");
            exit();
        }
    }
}
