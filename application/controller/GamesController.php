<?php

use JetBrains\PhpStorm\NoReturn;

class GamesController extends Controller
{
    public function __construct($parameters = [])
    {
        parent::__construct($parameters);
    }

    // Zeigt alle Spiele an.
    public function index(): void
    {
        $games = GamesModel::getAllGames();
        $this->View->render('games/index', ['games' => $games]);
    }

    // Detailansicht eines Spiels
    public function detail(): void
    {
        $game_id = $this->parameters[0] ?? null;

        if (empty($game_id)) {
            Session::add('feedback_negative', 'Game not found');
            Redirect::to('games/index');
            exit;
        }

        $game = GamesModel::getGameById((int)$game_id);
        if (!$game) {
            Session::add('feedback_negative', 'Game not found');
            Redirect::to('games/index');
            exit;
        }

        $this->View->render('games/detail', ['game' => $game]);
    }

    // Suche nach Spielen
    public function search(): void
    {
        $searchTerm = $_POST['search'] ?? '';
        $games = GamesModel::searchGames($searchTerm);
        $this->View->render('games/index', ['games' => $games]);
    }

    // Fügt ein neues Spiel hinzu (Admin)
    public function add(): void
    {
        session_start();
        if (($_SESSION['role'] ?? '') !== 'Admin') {
            header('Location: /login?error=no_permission');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Falls du discount, snippet, category, video_url etc. abfragen willst:
            // $_POST['discount'] ?? '', $_POST['snippet'] ?? '', ...
            GamesModel::addGame(
                $_POST['title'],
                $_POST['description'],
                $_POST['image'],
                (float)$_POST['price'],
                $_POST['genre'],
                $_POST['release_date'],
                isset($_POST['developer_id']) ? (int)$_POST['developer_id'] : null,
                isset($_POST['license_required']) ? (int)$_POST['license_required'] : 0,
                $_POST['discount'] ?? '',
                $_POST['snippet'] ?? '',
                $_POST['category'] ?? '',
                $_POST['video_url'] ?? ''
            );
            header('Location: /admin/games');
            exit();
        }

        $this->View->render('admin/add_game');
    }

    // Aktualisiert ein Spiel (Admin)
    public function update(int $game_id): void
    {
        session_start();
        if (($_SESSION['role'] ?? '') !== 'Admin') {
            header('Location: /login?error=no_permission');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            GamesModel::updateGame(
                $game_id,
                $_POST['title'],
                $_POST['description'],
                $_POST['image'],
                (float)$_POST['price'],
                $_POST['genre'],
                $_POST['release_date'],
                isset($_POST['developer_id']) ? (int)$_POST['developer_id'] : null,
                isset($_POST['license_required']) ? (int)$_POST['license_required'] : 0,
                $_POST['discount'] ?? '',
                $_POST['snippet'] ?? '',
                $_POST['category'] ?? '',
                $_POST['video_url'] ?? ''
            );
            header('Location: /admin/games');
            exit();
        }

        $game = GamesModel::getGameById($game_id);
        $this->View->render('admin/edit_game', ['games' => $game]);
    }

    // Löscht ein Spiel (Admin)
    #[NoReturn]
    public function delete(int $game_id): void
    {
        session_start();
        if (($_SESSION['role'] ?? '') !== 'Admin') {
            header('Location: /login?error=no_permission');
            exit();
        }

        GamesModel::deleteGame($game_id);
        header('Location: /admin/games');
        exit();
    }
}
