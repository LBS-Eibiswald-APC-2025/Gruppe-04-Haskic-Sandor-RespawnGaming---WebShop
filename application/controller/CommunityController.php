<?php

use JetBrains\PhpStorm\NoReturn;

class CommunityController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Zeigt die Community-Startseite.
     */
    public function index(): void
    {
        //Hole alle Threads
        $threads = CommunityModel::getAllThreads();

        $this->View->render('community/index', [
            'threads' => $threads
        ]);
    }

    public function detail(?int $post_id = 0): void {
        $post = CommunityModel::getPostById($post_id);
        if (!$post) {
            Session::add('feedback_negative', 'Post nicht gefunden.');
            header('Location: /community/index');
            exit();
        }
        $this->View->render('community/detail', [
            'post' => $post
        ]);
    }

    /**
     * Zeigt alle Community-Posts für ein bestimmtes Spiel.
     */
    public function gamePosts(int $game_id): void
    {
        $posts = CommunityModel::getPostsByGame($game_id);
        $this->View->render('community/game_posts', [
            'posts'   => $posts,
            'game_id' => $game_id
        ]);
    }

    /**
     * Fügt einen neuen Community-Post hinzu (für Spiel-Posts).
     */
    public function addPost(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $game_id   = $_POST['game_id'];
            $user_id   = $_SESSION['user_id'];
            $post_type = $_POST['post_type'];
            $title     = $_POST['title'];
            $content   = $_POST['content'];

            if (CommunityModel::addPost($game_id, $user_id, $post_type, $title, $content)) {
                header("Location: /community/games/$game_id");
            } else {
                header("Location: /community/games/$game_id?error=failed");
            }
            exit();
        }
    }

    /**
     * Löscht einen Community-Post.
     */
    #[NoReturn] public function deletePost(int $post_id): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }

        $user_id  = $_SESSION['user_id'];
        $user_role = $_SESSION['role'] ?? null;

        if (CommunityModel::deletePost($post_id, $user_id, $user_role)) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=delete_failed");
        }
        exit();
    }

    /* ============================
       Neue Methoden für Threads
       ============================ */

    /**
     * Verarbeitet das Formular für ein neues Thema (Thread).
     */
    public function createThread(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Daten aus dem Formular
            $title    = trim($_POST['threadTitle']);
            $category = trim($_POST['threadCategory']);
            $content  = trim($_POST['threadContent']);
            $user_id  = $_SESSION['user_id'];
            // Falls Threads nicht spielgebunden sind, setzen wir game_id = 0
            $game_id  = 0;

            // Erstelle den Thread – hier wird in der Datenbank als post_type 'thread' gesetzt.
            if (CommunityModel::createThread($user_id, $title, $content, $category, $game_id)) {
                Session::add('feedback_positive', 'Thread wurde erstellt.');
                header('Location: /community/index');
            } else {
                Session::add('feedback_negative', 'Thread konnte nicht erstellt werden.');
                header('Location: /community/index');
            }
            exit();
        }
    }

    /**
     * Zeigt einen einzelnen Thread inklusive Antworten an.
     *
     * URL: /community/thread/{thread_id}
     */
    public function thread(int $thread_id): void
    {
        // Hole den Thread
        $thread = CommunityModel::getThreadById($thread_id);
        if (!$thread) {
            header('Location: /community/index?error=thread_not_found');
            exit();
        }

        // Erhöhe die View-Zahl
        CommunityModel::increaseThreadViews($thread_id);

        // Hole alle Antworten (Replies)
        $replies = CommunityModel::getRepliesByThreadId($thread_id);

        // Render die View 'community/thread' und übergebe Thread und Replies
        $this->View->render('community/thread', [
            'thread'  => $thread,
            'replies' => $replies
        ]);
    }

    /**
     * Verarbeitet das Formular zum Erstellen einer Antwort (Reply) in einem Thread.
     */
    public function createReply_action(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?error=not_logged_in');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $thread_id = (int)$_POST['thread_id'];
            $content   = trim($_POST['postContent']);
            $user_id   = $_SESSION['user_id'];

            if (CommunityModel::addReply($thread_id, $user_id, $content)) {
                header("Location: /community/thread/$thread_id");
            } else {
                header("Location: /community/thread/$thread_id?error=reply_failed");
            }
            exit();
        }
    }

    public function forum(): void
    {
        require APP . 'view/community/forum.php';
        $this->View->render('community/forum');
    }
}
