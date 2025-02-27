<?php

class GameModel
{
    /**
     * Holen Sie sich alle Spiele aus der Datenbank
     * @return array|null
     */
    public static function getAllGames(): ?array
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT id, title, price, genre, release_date, description, image FROM games ORDER BY release_date DESC LIMIT 20";
        $query = $database->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    /**
     * Holen Sie sich ein einzelnes Spiel anhand der ID
     * @param int $game_id
     * @return object|null
     */
    public static function getGameById(int $game_id): ?object
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT id, title, price, genre, release_date, description, image, developer_id, license_required 
                FROM games WHERE id = :game_id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':game_id' => $game_id]);
        return $query->fetch();
    }

    /**
     * Fügen Sie ein neues Spiel hinzu
     */
    public static function addGame(string $title, string $description, string $image, float $price, string $genre, string $release_date, ?int $developer_id, int $license_required = 0): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO games (title, description, image, price, genre, release_date, developer_id, license_required) 
                VALUES (:title, :description, :image, :price, :genre, :release_date, :developer_id, :license_required)";
        $query = $database->prepare($sql);
        return $query->execute([
            ':title' => $title,
            ':description' => $description,
            ':image' => $image,
            ':price' => $price,
            ':genre' => $genre,
            ':release_date' => $release_date,
            ':developer_id' => $developer_id,
            ':license_required' => $license_required
        ]);
    }

    /**
     * Aktualisieren Sie ein Spiel
     */
    public static function updateGame(int $game_id, string $title, string $description, string $image, float $price, string $genre, string $release_date, ?int $developer_id, int $license_required): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "UPDATE games 
                SET title = :title, description = :description, image = :image, price = :price, genre = :genre, 
                    release_date = :release_date, developer_id = :developer_id, license_required = :license_required 
                WHERE id = :game_id";
        $query = $database->prepare($sql);
        return $query->execute([
            ':game_id' => $game_id,
            ':title' => $title,
            ':description' => $description,
            ':image' => $image,
            ':price' => $price,
            ':genre' => $genre,
            ':release_date' => $release_date,
            ':developer_id' => $developer_id,
            ':license_required' => $license_required
        ]);
    }

    /**
     * Löschen Sie ein Spiel
     */
    public static function deleteGame(int $game_id): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "DELETE FROM games WHERE id = :game_id";
        $query = $database->prepare($sql);
        return $query->execute([':game_id' => $game_id]);
    }

    public static function searchGames(mixed $search): ?array
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT id, title, price, genre, release_date, description, image 
            FROM games 
            WHERE title LIKE :search OR description LIKE :search
            ORDER BY release_date DESC";

        $query = $database->prepare($sql);
        $query->execute([':search' => '%' . $search . '%']);

        return $query->fetchAll();
    }

}
