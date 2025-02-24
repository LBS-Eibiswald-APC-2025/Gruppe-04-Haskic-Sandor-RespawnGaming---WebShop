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
        $sql = "SELECT id, title, price, release_date, description, image FROM games LIMIT 10";
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

        $sql = "SELECT id, title, description, image FROM games WHERE id = :game_id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':game_id' => $game_id]);

        return $query->fetch();
    }

    /**
     * Fügen Sie ein neues Spiel hinzu
     * @param string $title
     * @param string $description
     * @param string $image
     * @return bool
     */
    public static function addGame(string $title, string $description, string $image): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO games (title, description, image) VALUES (:title, :description, :image)";
        $query = $database->prepare($sql);
        $parameters = [
            ':title' => $title,
            ':description' => $description,
            ':image' => $image
        ];

        return $query->execute($parameters);
    }

    /**
     * Löschen Sie ein Spiel anhand der ID
     * @param int $game_id
     * @return bool
     */
    public static function deleteGame(int $game_id): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "DELETE FROM games WHERE id = :game_id";
        $query = $database->prepare($sql);
        $parameters = [':game_id' => $game_id];

        return $query->execute($parameters);
    }

    /**
     * Suchen Sie nach Spielen anhand des Titels
     * @param string $search
     * @return array|null
     */
    public static function searchGames(string $search): ?array
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT id, title, price, release_date, description, image FROM games WHERE title LIKE :search";
        $query = $database->prepare($sql);
        $query->execute([':search' => '%' . $search . '%']);

        return $query->fetchAll();
    }
}
