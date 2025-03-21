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
                    snippet,
                    category,
                    game_url,
                    video_url
                FROM games
                ORDER BY release_date DESC
                LIMIT " . $limit;

        $query = $database->prepare($sql);
        $query->execute();

        // Mehrere Datensätze => fetchAll(), als assoziatives Array
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result ?: null;
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
                    snippet,
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

    /**
     * Holt ein einzelnes Spiel anhand der ID (als assoziatives Array).
     */
    public static function getGameById(int $game_id): ?array
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
                    snippet,
                    category,
                    video_url,
                    game_url
                FROM games
                WHERE id = :game_id
                LIMIT 1";

        $query = $database->prepare($sql);
        $query->execute([':game_id' => $game_id]);

        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
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
        string $snippet          = '',
        string $category         = '',
        string $video_url        = ''
    ): bool {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO games
                (id, title, price, developer_id, license_required, genre, rating, description, release_date, file_path,
                 created_at, image, tinyImageArray, discount, snippet, category, video_url, game_url)
                VALUES
                (:id, :title, :price, :developer_id, :license_required, :genre, :rating, :description, :release_date,
                 :file_path, :created_at, :image, :tinyImageArray, :discount, :snippet, :category, :video_url, :game_url, '', NOW())";

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
            ':snippet'         => $snippet,
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
        string $snippet    = '',
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
                    snippet         = :snippet,
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
            ':snippet'         => $snippet,
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
}
