<?php

use JetBrains\PhpStorm\NoReturn;

class GameController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // Zeigt alle Spiele an.
    public function index(): void
    {
        $games = GameModel::getAllGames();
        $this->View->render('games/index', ['games' => $games]);
    }

    // Suche nach Spielen.
    public function search(): void
    {
        $games = GameModel::searchGames($_POST['search']);
        $this->View->render('games/index', ['games' => $games]);
    }

    // Fügt ein neues Spiel hinzu.
    public function add(): void
    {
        session_start();
        if ($_SESSION['role'] !== 'Admin') {
            header('Location: /login?error=no_permission');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            GameModel::addGame(
                $_POST['title'],
                $_POST['description'],
                $_POST['image'],
                $_POST['price'],
                $_POST['genre'],
                $_POST['release_date'],
                $_POST['developer_id'],
                $_POST['license_required']
            );
            header('Location: /admin/games');
            exit();
        }
        $this->View->render('admin/add_game');
    }

    // Aktualisiert ein Spiel.
    public function update(int $game_id): void
    {
        session_start();
        if ($_SESSION['role'] !== 'Admin') {
            header('Location: /login?error=no_permission');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            GameModel::updateGame(
                $game_id,
                $_POST['title'],
                $_POST['description'],
                $_POST['image'],
                $_POST['price'],
                $_POST['genre'],
                $_POST['release_date'],
                $_POST['developer_id'],
                $_POST['license_required']
            );
            header('Location: /admin/games');
            exit();
        }
        $game = GameModel::getGameById($game_id);
        $this->View->render('admin/edit_game', ['games' => $game]);
    }

    // Löscht ein Spiel.
    #[NoReturn]
    public function delete(int $game_id): void
    {
        session_start();
        if ($_SESSION['role'] !== 'Admin') {
            header('Location: /login?error=no_permission');
            exit();
        }

        GameModel::deleteGame($game_id);
        header('Location: /admin/games');
        exit();
    }
}
