<?php
/**
 * Controller für die Entwickler-Funktionen und Spielverwaltung
 */
class DeveloperController extends Controller
{
    /**
     * Zeigt das Entwickler-Dashboard an
     */
    public function index()
    {
        // Authentifizierung prüfen
        if (!Session::userIsLoggedIn()) {
            Redirect::to('login');
        }

        // Spiele des aktuellen Entwicklers abrufen
        $developer_id = Session::get('user_id');

        $this->View->render('developer/index', ['games' => DeveloperModel::getGamesByDeveloperId($developer_id)]);
    }

    /**
     * Verarbeitet das Hochladen eines neuen Spiels
     */
    public function upload()
    {
        // Authentifizierung prüfen
        if (!Session::userIsLoggedIn()) {
            Redirect::to('login');
        }

        // Prüfen, ob Daten per POST gesendet wurden
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Grundlegende Spieldaten erfassen
            $title = Request::post('title', '');
            $description = Request::post('description', '');
            $price = Request::post('price', 0);
            $genre = Request::post('genre', '');
            $category = Request::post('category', '');
            $release_date = Request::post('release_date', date('Y-m-d'));
            $discount = Request::post('discount', 0);
            $license_required = isset($_POST['license_required']) ? 1 : 0;

            // Systemanforderungen als Array erfassen
            $systemRequirements = isset($_POST['systemRequirements']) ? $_POST['systemRequirements'] : [];

            // Optional: Validierung der Systemanforderungen
            $requiredFields = [
                'min_os', 'min_processor', 'min_memory', 'min_graphics', 'min_directx', 'min_storage',
                'rec_os', 'rec_processor', 'rec_memory', 'rec_graphics', 'rec_directx', 'rec_storage'
            ];

            $isValid = true;

            // Prüfen, ob alle erforderlichen Felder vorhanden sind
            foreach ($requiredFields as $field) {
                if (empty($systemRequirements[$field])) {
                    $isValid = false;
                    Session::add('feedback_negative', 'Bitte alle Systemanforderungen angeben');
                    break;
                }
            }

            // Prüfen, ob eine EXE-Datei hochgeladen wurde
            if (!isset($_FILES['exe_file']) || $_FILES['exe_file']['error'] !== UPLOAD_ERR_OK) {
                $isValid = false;
                Session::add('feedback_negative', 'Bitte eine gültige EXE-Datei hochladen');
            }

            // Dateierweiterung der EXE-Datei prüfen
            if (isset($_FILES['exe_file'])) {
                $exe_ext = strtolower(pathinfo($_FILES['exe_file']['name'], PATHINFO_EXTENSION));
                if ($exe_ext !== 'exe') {
                    $isValid = false;
                    Session::add('feedback_negative', 'Die hochgeladene Datei muss eine .exe-Datei sein');
                }
            }

            if ($isValid) {
                // Systemanforderungen als JSON für die Datenbank konvertieren
                $systemRequirementsJson = json_encode($systemRequirements, JSON_UNESCAPED_UNICODE);

                // Bild und Video verarbeiten
                $image_path = $this->processUploadedFile('image', 'images/games/');
                $video_path = $this->processUploadedFile('video', 'videos/games/');

                // EXE-Datei verarbeiten
                $file_path = $this->processUploadedFile('exe_file', 'games_uploads/executables/');

                // Miniaturbilder (Screenshots) verarbeiten
                $thumbnails = [];
                for ($i = 1; $i <= 4; $i++) {
                    $thumbnail_path = $this->processUploadedFile('tImage' . $i, 'images/games/screenshots/');
                    if ($thumbnail_path) {
                        $thumbnails[] = $thumbnail_path;
                    }
                }

                // Thumbnails als JSON speichern
                $thumbnailsJson = json_encode($thumbnails, JSON_UNESCAPED_UNICODE);

                // Spiel in der Datenbank speichern
                $developer_id = Session::get('user_id');
                $game_id = DeveloperModel::addGame(
                    $developer_id,
                    $title,
                    $description,
                    $price,
                    $image_path,
                    $video_path,
                    $systemRequirementsJson,
                    $genre,
                    $category,
                    $release_date,
                    $discount,
                    $license_required,
                    $file_path,
                    $thumbnailsJson
                );

                if ($game_id) {
                    Session::add('feedback_positive', 'Spiel erfolgreich hochgeladen!');
                    Redirect::to('developer');
                } else {
                    Session::add('feedback_negative', 'Fehler beim Speichern des Spiels');
                }
            }
        }

        // Bei Fehler oder direktem Aufruf zurück zum Formular
        Redirect::to('developer');
    }

    /**
     * Bearbeitet ein vorhandenes Spiel
     *
     * @param int $game_id Die ID des zu bearbeitenden Spiels
     */
    public function edit($game_id)
    {
        // Authentifizierung prüfen
        if (!Session::userIsLoggedIn()) {
            Redirect::to('login');
        }

        // Prüfen, ob das Spiel dem Entwickler gehört
        $developer_id = Session::get('user_id');
        $game = DeveloperModel::getGameById($game_id);

        if (!$game || $game->developer_id != $developer_id) {
            Session::add('feedback_negative', 'Zugriff verweigert oder Spiel nicht gefunden');
            Redirect::to('developer');
        }

        // Formulardaten verarbeiten, wenn POST-Anfrage
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // TODO: Implementiere die Update-Logik ähnlich wie bei upload()
            // ...

            Session::add('feedback_positive', 'Spiel erfolgreich aktualisiert!');
            Redirect::to('developer');
        }

        // Formular mit den aktuellen Daten rendern
        $this->View->render('developer/edit', ['game' => $game]);
    }

    /**
     * Löscht ein Spiel
     *
     * @param int $game_id Die ID des zu löschenden Spiels
     */
    public function delete($game_id)
    {
        // Authentifizierung prüfen
        if (!Session::userIsLoggedIn()) {
            Redirect::to('login');
        }

        // Prüfen, ob das Spiel dem Entwickler gehört
        $developer_id = Session::get('user_id');
        $game = DeveloperModel::getGameById($game_id);

        if (!$game || $game->developer_id != $developer_id) {
            Session::add('feedback_negative', 'Zugriff verweigert oder Spiel nicht gefunden');
            Redirect::to('developer');
        }

        // Spiel aus der Datenbank löschen
        if (DeveloperModel::deleteGame($game_id)) {
            // Dateien löschen
            $this->deleteGameFiles($game);

            Session::add('feedback_positive', 'Spiel erfolgreich gelöscht!');
        } else {
            Session::add('feedback_negative', 'Fehler beim Löschen des Spiels');
        }

        Redirect::to('developer');
    }

    /**
     * Hilfsfunktion zum Löschen der Spieldateien
     *
     * @param object $game Das zu löschende Spielobjekt
     */
    private function deleteGameFiles($game)
    {
        // Bild löschen
        if (!empty($game->image)) {
            $image_path = Config::get('PATH_PUBLIC') . $game->image;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Video löschen
        if (!empty($game->video_url)) {
            $video_path = Config::get('PATH_PUBLIC') . $game->video_url;
            if (file_exists($video_path)) {
                unlink($video_path);
            }
        }

        // EXE-Datei löschen
        if (!empty($game->file_path)) {
            $file_path = Config::get('PATH_PUBLIC') . $game->file_path;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Screenshots löschen
        if (!empty($game->tinyImageArray)) {
            $thumbnails = json_decode($game->tinyImageArray, true);
            foreach ($thumbnails as $thumbnail) {
                $thumbnail_path = Config::get('PATH_PUBLIC') . $thumbnail;
                if (file_exists($thumbnail_path)) {
                    unlink($thumbnail_path);
                }
            }
        }
    }

    /**
     * Hilfsfunktion für die Verarbeitung hochgeladener Dateien
     */
    private function processUploadedFile($file_key, $target_dir): ?string
    {
        // Prüfen, ob eine Datei hochgeladen wurde
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
            $temp_name = $_FILES[$file_key]['tmp_name'];
            $name = $_FILES[$file_key]['name'];

            // Eindeutigen Dateinamen generieren
            $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $new_filename = uniqid('', true) . '.' . $file_extension;

            // Pfad direkt definieren, da PATH_PUBLIC nicht vorhanden ist
            $base_path = $_SERVER['DOCUMENT_ROOT'];

            // Zielverzeichnis erstellen, falls nicht vorhanden
            if (!file_exists($base_path . '/' . $target_dir)) {
                mkdir($base_path . '/' . $target_dir, 0777, true);
            }

            $target_path = $target_dir . $new_filename;

            // Datei verschieben
            if (move_uploaded_file($temp_name, $base_path . '/' . $target_path)) {
                return $target_path;
            }
        }

        return null;
    }

    /**
     * Zeigt Statistiken für ein bestimmtes Spiel an
     *
     * @param int $game_id Die ID des Spiels
     */
    public function stats($game_id)
    {
        // Authentifizierung prüfen
        if (!Session::userIsLoggedIn()) {
            Redirect::to('login');
        }

        // Prüfen, ob das Spiel dem Entwickler gehört
        $developer_id = Session::get('user_id');
        $game = DeveloperModel::getGameById($game_id);

        if (!$game || $game->developer_id != $developer_id) {
            Session::add('feedback_negative', 'Zugriff verweigert oder Spiel nicht gefunden');
            Redirect::to('developer');
        }

        // TODO: Hier Statistikdaten sammeln
        $stats = [
            'downloads' => DeveloperModel::getGameDownloads($game_id),
            'revenue' => DeveloperModel::getGameRevenue($game_id),
            'rating' => DeveloperModel::getGameRating($game_id),
        ];

        $this->View->render('developer/stats', [
            'game' => $game,
            'stats' => $stats
        ]);
    }
}