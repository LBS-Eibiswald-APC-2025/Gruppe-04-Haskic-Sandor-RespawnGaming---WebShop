<?php

class GamesModel
{
    /**
     * Holt alle Spiele als assoziative Arrays.
     * Passt zu deinen 15 Spalten in der Tabelle "games".
     */
    public static function getAllGames(?int $limit = 20): ?array
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT g.*,
            (SELECT COUNT(*) FROM game_ratings gr WHERE gr.game_id = g.id AND gr.is_positive = 1) as positive_ratings,
            (SELECT COUNT(*) FROM game_ratings gr WHERE gr.game_id = g.id AND gr.is_positive = 0) as negative_ratings
            FROM games g
            ORDER BY g.release_date DESC
            LIMIT " . $limit;

        $query = $database->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Sucht Spiele anhand eines Suchbegriffs.
     */
    public static function searchGames(mixed $search): ?array
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT
                    id,
                    title,
                    price,
                    developer_id,
                    license_required,
                    genre,
                    description,
                    release_date,
                    file_path,
                    created_at,
                    image,
                    tinyImageArray,
                    discount,
                    systemRequirements,
                    category,
                    game_url,
                    video_url
                FROM games
                WHERE title LIKE :search OR description LIKE :search
                ORDER BY release_date DESC";

        $query = $database->prepare($sql);
        $query->execute([':search' => '%' . $search . '%']);

        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function getGameById($game_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT * FROM games WHERE id = :game_id LIMIT 1";

        $query = $database->prepare($sql);
        $query->execute([':game_id' => $game_id]);

        $game = $query->fetch(PDO::FETCH_ASSOC); // oder wie du das Spiel abrufst

        if ($game) {
            // JSON-String in Array umwandeln
            if (isset($game['systemRequirements']) && is_string($game['systemRequirements'])) {
                $game['systemRequirements'] = json_decode($game['systemRequirements'], true);
            }

            // Fallback, falls JSON ungültig ist oder fehlt
            if (!isset($game['systemRequirements']) || !is_array($game['systemRequirements'])) {
                $game['systemRequirements'] = [
                    'min_os' => '',
                    'min_processor' => '',
                    'min_memory' => '',
                    'min_graphics' => '',
                    'min_directx' => '',
                    'min_storage' => '',
                    'rec_os' => '',
                    'rec_processor' => '',
                    'rec_memory' => '',
                    'rec_graphics' => '',
                    'rec_directx' => '',
                    'rec_storage' => ''
                ];
            }
        }

        return $game;
    }

    /**
     * Fügt ein neues Spiel hinzu.
     * Passe die Parameter an deine Form-Felder an (z. B. wenn du discount etc. erfassen willst).
     */
    public static function addGame(
        string $title,
        string $description,
        string $image,
        float  $price,
        string $genre,
        string $release_date,
        ?int   $developer_id,
        int    $license_required = 0,
        string $discount         = '',
        string $systemRequirements          = '',
        string $category         = '',
        string $video_url        = ''
    ): bool {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO games
                (id, title, price, developer_id, license_required, genre, rating, description, release_date, file_path,
                 created_at, image, tinyImageArray, discount, systemRequirements, category, video_url, game_url)
                VALUES
                (:id, :title, :price, :developer_id, :license_required, :genre, :rating, :description, :release_date,
                 :file_path, :created_at, :image, :tinyImageArray, :discount, :systemRequirements, :category, :video_url, :game_url, '', NOW())";

        $query = $database->prepare($sql);
        return $query->execute([
            ':title'           => $title,
            ':price'           => $price,
            ':developer_id'    => $developer_id,
            ':license_required'=> $license_required,
            ':genre'           => $genre,
            ':rating'          => 0,
            ':description'     => $description,
            ':release_date'    => $release_date,
            ':file_path'       => '',
            ':created_at'      => '',
            ':image'           => $image,
            ':tinyImageArray'  => '',
            ':discount'        => $discount,
            ':systemRequirements'         => $systemRequirements,
            ':category'        => $category,
            ':video_url'       => $video_url,
            ':game_url'        => ''
        ]);
    }

    /**
     * Aktualisiert ein Spiel.
     */
    public static function updateGame(
        int    $game_id,
        string $title,
        string $description,
        string $image,
        float  $price,
        string $genre,
        string $release_date,
        ?int   $developer_id,
        int    $license_required,
        string $discount   = '',
        string $systemRequirements    = '',
        string $category   = '',
        string $video_url  = ''
    ): bool {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "UPDATE games
                SET
                    title            = :title,
                    description      = :description,
                    image           = :image,
                    price           = :price,
                    genre           = :genre,
                    release_date    = :release_date,
                    developer_id    = :developer_id,
                    license_required= :license_required,
                    discount        = :discount,
                    systemRequirements         = :systemRequirements,
                    category        = :category,
                    video_url       = :video_url
                WHERE id = :game_id";

        $query = $database->prepare($sql);
        return $query->execute([
            ':game_id'         => $game_id,
            ':title'           => $title,
            ':description'     => $description,
            ':image'           => $image,
            ':price'           => $price,
            ':genre'           => $genre,
            ':release_date'    => $release_date,
            ':developer_id'    => $developer_id,
            ':license_required'=> $license_required,
            ':discount'        => $discount,
            ':systemRequirements'         => $systemRequirements,
            ':category'        => $category,
            ':video_url'       => $video_url
        ]);
    }

    /**
     * Löscht ein Spiel.
     */
    public static function deleteGame(int $game_id): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "DELETE FROM games WHERE id = :game_id";
        $query = $database->prepare($sql);
        return $query->execute([':game_id' => $game_id]);
    }

    public static function getRandomGames($limit = 6, $excludeIds = []): false|array
    {
        try {
            $database = DatabaseFactory::getFactory()->getConnection();

            $sql = "SELECT * FROM games WHERE 1";

            // Ausschließen bestimmter Spiel-IDs, falls vorhanden
            if (!empty($excludeIds)) {
                $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
                $sql .= " AND id NOT IN ($placeholders)";
            }

            // Zufällige Sortierung und Limit
            $sql .= " ORDER BY RAND() LIMIT ?";

            $query = $database->prepare($sql);

            // Parameter setzen
            $paramIndex = 1;
            foreach ($excludeIds as $id) {
                $query->bindValue($paramIndex, $id, PDO::PARAM_INT);
                $paramIndex++;
            }

            $query->bindValue($paramIndex, $limit, PDO::PARAM_INT);
            $query->execute();

            // Ergebnisse zurückgeben
            return $query->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Fehlerbehandlung, z.B. Logging
            error_log('Datenbankfehler in GamesModel::getRandomGames: ' . $e->getMessage());
            return false;
        }
    }

    // In GamesModel.php

    /**
     * Speichert eine neue Bewertung
     */
    public static function rateGame(int $gameId, int $userId, bool $isPositive): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        // Prüfen ob Nutzer das Spiel bereits bewertet hat
        $sql = "SELECT id FROM game_ratings 
            WHERE game_id = :game_id AND user_id = :user_id";

        $query = $database->prepare($sql);
        $query->execute([
            ':game_id' => $gameId,
            ':user_id' => $userId
        ]);

        if ($query->fetch()) {
            // Update existierende Bewertung
            $sql = "UPDATE game_ratings 
                SET is_positive = :is_positive, updated_at = NOW() 
                WHERE game_id = :game_id AND user_id = :user_id";
        } else {
            // Neue Bewertung einfügen
            $sql = "INSERT INTO game_ratings 
                (game_id, user_id, is_positive, created_at) 
                VALUES (:game_id, :user_id, :is_positive, NOW())";
        }

        $query = $database->prepare($sql);
        return $query->execute([
            ':game_id' => $gameId,
            ':user_id' => $userId,
            ':is_positive' => (int)$isPositive // Boolean zu Integer konvertieren
        ]);
    }

    /**
     * Holt die Bewertungszahlen für ein Spiel
     */
    public static function getGameRatings(int $gameId): array
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT 
                    SUM(CASE WHEN is_positive = 1 THEN 1 ELSE 0 END) as positive,
                    SUM(CASE WHEN is_positive = 0 THEN 1 ELSE 0 END) as negative
                FROM game_ratings 
                WHERE game_id = :game_id";

        $query = $database->prepare($sql);
        $query->execute([':game_id' => $gameId]);

        $result = $query->fetch(PDO::FETCH_ASSOC);

        return [
            'positive' => (int)($result['positive'] ?? 0),
            'negative' => (int)($result['negative'] ?? 0)
        ];
    }
}
