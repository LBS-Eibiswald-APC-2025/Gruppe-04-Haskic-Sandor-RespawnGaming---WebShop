<?php

class CommunityModel
{
    /**
     * Holt alle Community-Posts für ein bestimmtes Spiel
     */
    public static function getPostsByGame(int $game_id): ?array
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT community_posts.*, users.user_name 
                FROM community_posts 
                JOIN users ON community_posts.user_id = users.user_id
                WHERE game_id = :game_id 
                ORDER BY created_at DESC";
        $query = $database->prepare($sql);
        $query->execute([':game_id' => $game_id]);
        return $query->fetchAll();
    }

    /**
     * Fügt einen neuen Community-Post hinzu
     */
    public static function addPost(int $game_id, int $user_id, string $post_type, string $title, string $content): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO community_posts (game_id, user_id, post_type, title, content) 
                VALUES (:game_id, :user_id, :post_type, :title, :content)";
        $query = $database->prepare($sql);
        return $query->execute([
            ':game_id' => $game_id,
            ':user_id' => $user_id,
            ':post_type' => $post_type,
            ':title' => $title,
            ':content' => $content
        ]);
    }

    /**
     * Löscht einen Community-Post
     */
    public static function deletePost(int $post_id, int $user_id, string $user_role): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        // Admins dürfen alles löschen, normale Nutzer nur ihre eigenen Posts
        if ($user_role === 'Admin') {
            $sql = "DELETE FROM community_posts WHERE id = :post_id";
            $query = $database->prepare($sql);
            return $query->execute([':post_id' => $post_id]);
        } else {
            $sql = "DELETE FROM community_posts WHERE id = :post_id AND user_id = :user_id";
            $query = $database->prepare($sql);
            return $query->execute([':post_id' => $post_id, ':user_id' => $user_id]);
        }
    }
}
