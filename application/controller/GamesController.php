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

    public function detail(): void {
        $game_id = $this->parameters[0];

        if (empty($game_id)) {
            Session::add('feedback_negative', 'Game not found');
            Redirect::to('games/index');
            exit;
        }

        $game = GamesModel::getGameById($game_id);
        $this->View->render('games/detail', ['game' => $game]);
    }

    // Suche nach Spielen.
    public function search(): void
    {
        $games = GamesModel::searchGames($_POST['search']);
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
            GamesModel::addGame(
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
            GamesModel::updateGame(
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
        $game = GamesModel::getGameById($game_id);
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

        GamesModel::deleteGame($game_id);
        header('Location: /admin/games');
        exit();
    }

    public function getGameById($game_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        // Beispiel: Hol dir alle Daten zum Spiel, inkl. Video-URL, Publisher, Preis usw.
        $sql = "SELECT id, title, price, cover_image, trailer_url, description, developer, publisher,
                       release_date, screenshots, is_free
                FROM games
                WHERE id = :id
                LIMIT 1";

        $query = $database->prepare($sql);
        $query->execute([':id' => $game_id]);
        return $query->fetch(); // gibt stdClass-Objekt zurück oder false
    }
}
