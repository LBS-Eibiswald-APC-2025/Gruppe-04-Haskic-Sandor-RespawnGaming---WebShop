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

        // Bewertungen abrufen
        $ratings = GamesModel::getGameRatings((int)$game_id);
        $game['positive_ratings'] = $ratings['positive'];
        $game['negative_ratings'] = $ratings['negative'];

        $this->View->render('games/detail', ['game' => $game]);
    }

    // Suche nach Spielen
    public function search(): void
    {
        $searchTerm = $_POST['search'] ?? '';
        $games = GamesModel::searchGames($searchTerm);
        $this->View->render('games/index', ['method' => 'search', 'games' => $games]);
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

    public function getRandomGames()
    {
        // Prüfen, ob es eine AJAX-Anfrage ist
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            // Falls kein AJAX, zur Startseite umleiten
            header('Location: ' . Config::get('URL'));
            exit;
        }

        // Antwort-Header setzen
        header('Content-Type: application/json');

        try {
            // Auszuschließende Spiel-IDs aus der Anfrage holen
            $excludeIds = [];
            if (isset($_POST['exclude_ids'])) {
                $excludeIds = json_decode($_POST['exclude_ids'], true) ?: [];
            }

            // Zufällige Spiele aus dem Model holen (maximal 4)
            $randomGames = GamesModel::getRandomGames(6, $excludeIds);

            if (!$randomGames) {
                echo json_encode(['success' => false, 'message' => 'Keine Spiele gefunden']);
                return;
            }

            // Erfolgreiche Antwort zurückgeben
            echo json_encode([
                'success' => true,
                'games' => $randomGames
            ]);

        } catch (Exception $e) {
            // Bei Fehlern eine Fehlermeldung zurückgeben
            echo json_encode([
                'success' => false,
                'message' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()
            ]);
        }

        // Beenden, um zusätzliche Ausgabe zu vermeiden
        exit;
    }

    public function rate(): void
    {
        // Content-Type Header setzen
        header('Content-Type: application/json');

        // Prüfen ob Nutzer eingeloggt ist
        if (!Session::userIsLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Bitte melden Sie sich an']);
            exit;
        }

        // Prüfen ob es ein AJAX POST Request ist
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage']);
            exit;
        }

        // JSON-Daten aus dem Request-Body lesen
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['game_id']) || !isset($data['rating'])) {
            echo json_encode(['success' => false, 'message' => 'Fehlende Parameter']);
            exit;
        }

        $gameId = (int)$data['game_id'];
        // Wichtig: Korrekter String-Vergleich für positive/negative
        $isPositive = $data['rating'] === 'positive';
        $userId = Session::get('user_id');

        try {
            // Bewertung speichern
            $result = GamesModel::rateGame($gameId, $userId, $isPositive);

            if ($result) {
                // Neue Bewertungszahlen holen
                $ratings = GamesModel::getGameRatings($gameId);

                $total = $ratings['positive'] + $ratings['negative'];
                $positivePercent = $total > 0 ?
                    round(($ratings['positive'] / $total) * 100) : 0;
                $negativePercent = 100 - $positivePercent;

                echo json_encode([
                    'success' => true,
                    'message' => 'Bewertung gespeichert',
                    'positive_percent' => $positivePercent,
                    'negative_percent' => $negativePercent,
                    'total_ratings' => $total
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Bewertung konnte nicht gespeichert werden'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}
