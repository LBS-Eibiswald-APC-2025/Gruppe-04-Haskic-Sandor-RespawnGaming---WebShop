<?php
/**
 * Model für die Spielverwaltung und Entwickler-Funktionen
 */
class DeveloperModel
{
    /**
     * Fügt ein neues Spiel zur Datenbank hinzu
     *
     * @param int $developer_id ID des Entwicklers
     * @param string $title Spieltitel
     * @param string $description Spielbeschreibung
     * @param float $price Spielpreis
     * @param string $image_path Pfad zum Spielbild
     * @param string|null $video_path Pfad zum Spielvideo (optional)
     * @param string|null $systemRequirements JSON-String mit Systemanforderungen
     * @param string|null $genre Genre des Spiels
     * @param string|null $category Kategorie des Spiels
     * @param string|null $release_date Erscheinungsdatum
     * @param int $discount Rabatt in Prozent
     * @param int $license_required Gibt an, ob eine Lizenz erforderlich ist
     * @param string|null $file_path Pfad zur EXE-Datei
     * @param string|null $tinyImageArray JSON-String mit Pfaden zu Screenshots
     * @return int|bool ID des neuen Spiels oder false bei Fehler
     */
    public static function addGame(
        int    $developer_id,
        string $title,
        string $description,
        float  $price,
        string $image_path,
        string $video_path = null,
        string $systemRequirements = null,
        string $genre = null,
        string $category = null,
        string $release_date = null,
        int    $discount = 0,
        int    $license_required = 0,
        string $file_path = null,
        string $tinyImageArray = null
    ): bool|int
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        // Generiere den game_url-Wert (z.B. durch Slugify des Titels)
        $game_url = self::generateGameUrl($title);

        $category_mapping = [
            'action' => 1,
            'adventure' => 2,
            'strategy' => 3,
            'simulation' => 4,
            'rpg' => 5,
            'sports' => 6,
            'puzzle' => 7,
            'arcade' => 8,
            'horror' => 9,
            'shooter' => 10
        ];

        if (array_key_exists($category, $category_mapping)) {
            $category = $category_mapping[$category];
        } else {
            $category = null; // oder eine Standard-ID setzen
        }

        $sql = "INSERT INTO games (
                developer_id, 
                title, 
                description, 
                price, 
                image, 
                video_url, 
                systemRequirements, 
                genre,
                category,
                release_date,
                discount,
                license_required,
                file_path,
                tinyImageArray,
                game_url,
                created_at
            ) 
            VALUES (
                :developer_id, 
                :title, 
                :description, 
                :price, 
                :image_path, 
                :video_path, 
                :system_requirements,
                :genre,
                :category,
                :release_date,
                :discount,
                :license_required,
                :file_path,
                :tiny_image_array,
                :game_url,
                NOW()
            )";

        $query = $database->prepare($sql);

        $query->execute([
            ':developer_id' => $developer_id,
            ':title' => $title,
            ':description' => $description,
            ':price' => $price,
            ':image_path' => $image_path,
            ':video_path' => $video_path,
            ':system_requirements' => $systemRequirements,
            ':genre' => $genre,
            ':category' => $category,
            ':release_date' => $release_date,
            ':discount' => $discount,
            ':license_required' => $license_required,
            ':file_path' => $file_path,
            ':tiny_image_array' => $tinyImageArray,
            ':game_url' => $game_url
        ]);

        if ($query->rowCount() === 1) {
            return $database->lastInsertId();
        }

        return false;
    }

    /**
     * Generiert eine URL-freundliche Version des Spieltitels
     *
     * @param string $title Der Spieltitel
     * @return string URL-freundliche Version
     */
    private static function generateGameUrl($title): string
    {
        // Sonderzeichen entfernen und durch Bindestriche ersetzen
        $url = preg_replace('/[^a-zA-Z0-9]/', '-', $title);
        // Mehrfache Bindestriche durch einen einzelnen ersetzen
        $url = preg_replace('/-+/', '-', $url);
        // Zu Kleinbuchstaben konvertieren
        $url = strtolower(trim($url, '-'));

        // Wenn die URL leer ist, verwende einen Standardwert
        if (empty($url)) {
            $url = 'game-' . time();
        }

        return $url;
    }

    /**
     * Ruft ein Spiel anhand seiner ID ab
     *
     * @param int $game_id ID des Spiels
     * @return object|bool Spielobjekt oder false, wenn nicht gefunden
     */
    public static function getGameById($game_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT * FROM games WHERE id = :game_id LIMIT 1";

        $query = $database->prepare($sql);
        $query->execute([':game_id' => $game_id]);

        $game = $query->fetch(PDO::FETCH_OBJ);

        if ($game) {
            // Systemanforderungen von JSON in ein Array umwandeln
            if ($game->systemRequirements) {
                $game->systemRequirements = json_decode($game->systemRequirements, true);
            } else {
                $game->systemRequirements = [];
            }

            // Screenshots von JSON in ein Array umwandeln
            if ($game->tinyImageArray) {
                $game->screenshots = json_decode($game->tinyImageArray, true);
            } else {
                $game->screenshots = [];
            }

            return $game;
        }

        return false;
    }

    /**
     * Ruft alle Spiele eines Entwicklers ab
     *
     * @param int $developer_id ID des Entwicklers
     * @return array Liste von Spielobjekten
     */
    public static function getGamesByDeveloperId($developer_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT * FROM games WHERE developer_id = :developer_id ORDER BY created_at DESC";

        $query = $database->prepare($sql);
        $query->execute([':developer_id' => $developer_id]);

        $games = $query->fetchAll(PDO::FETCH_OBJ);

        return $games;
    }

    /**
     * Aktualisiert ein bestehendes Spiel
     *
     * @param int $game_id ID des Spiels
     * @param array $data Zu aktualisierende Daten
     * @return bool Erfolg oder Misserfolg
     */
    public static function updateGame($game_id, $data)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        // Basisfelder, die aktualisiert werden können
        $allowed_fields = [
            'title', 'description', 'price', 'image', 'video_url',
            'genre', 'category', 'release_date', 'discount',
            'license_required', 'file_path'
        ];

        $set_parts = [];
        $params = [':game_id' => $game_id];

        // Reguläre Felder aktualisieren
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $set_parts[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        // Systemanforderungen aktualisieren, wenn vorhanden
        if (isset($data['systemRequirements'])) {
            $set_parts[] = "systemRequirements = :system_requirements";
            $params[':system_requirements'] = json_encode($data['systemRequirements'], JSON_UNESCAPED_UNICODE);
        }

        // Screenshots aktualisieren, wenn vorhanden
        if (isset($data['screenshots'])) {
            $set_parts[] = "tinyImageArray = :tiny_image_array";
            $params[':tiny_image_array'] = json_encode($data['screenshots'], JSON_UNESCAPED_UNICODE);
        }

        // SQL-Abfrage nur ausführen, wenn es etwas zu aktualisieren gibt
        if (!empty($set_parts)) {
            $sql = "UPDATE games SET " . implode(', ', $set_parts) . " WHERE id = :game_id";

            $query = $database->prepare($sql);
            return $query->execute($params);
        }

        return false;
    }

    /**
     * Löscht ein Spiel aus der Datenbank
     *
     * @param int $game_id ID des zu löschenden Spiels
     * @return bool Erfolg oder Misserfolg
     */
    public static function deleteGame($game_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "DELETE FROM games WHERE id = :game_id";

        $query = $database->prepare($sql);
        return $query->execute([':game_id' => $game_id]);
    }

    /**
     * Ruft die Anzahl der Downloads eines Spiels ab
     *
     * @param int $game_id ID des Spiels
     * @return int Anzahl der Downloads
     */
    public static function getGameDownloads($game_id)
    {
        // In einer echten Anwendung würde hier eine Datenbankabfrage erfolgen
        // Für dieses Beispiel simulieren wir zufällige Daten
        return mt_rand(10, 5000);
    }

    /**
     * Ruft den Umsatz eines Spiels ab
     *
     * @param int $game_id ID des Spiels
     * @return float Umsatz in Euro
     */
    public static function getGameRevenue($game_id)
    {
        // In einer echten Anwendung würde hier eine Datenbankabfrage erfolgen
        // Für dieses Beispiel simulieren wir zufällige Daten
        return round(mt_rand(100, 50000) / 10, 2);
    }

    /**
     * Ruft die durchschnittliche Bewertung eines Spiels ab
     *
     * @param int $game_id ID des Spiels
     * @return float Durchschnittliche Bewertung (1-5)
     */
    public static function getGameRating($game_id)
    {
        // In einer echten Anwendung würde hier eine Datenbankabfrage erfolgen
        // Für dieses Beispiel simulieren wir zufällige Daten
        return round(mt_rand(30, 50) / 10, 1);
    }
}