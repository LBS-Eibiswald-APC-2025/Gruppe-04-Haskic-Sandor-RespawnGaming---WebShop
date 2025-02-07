<?php

class Game
{
    public static function getAllGames()
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT * FROM games";
        $query = $database->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }
}
