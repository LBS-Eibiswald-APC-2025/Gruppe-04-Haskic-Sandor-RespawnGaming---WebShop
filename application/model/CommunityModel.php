<?php

class CommunityModel
{
    /**
     * Holt alle Community-Posts für ein bestimmtes Spiel.
     *
     * @param int $game_id
     * @return array|null
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
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fügt einen neuen Community-Post hinzu.
     *
     * @param int $game_id
     * @param int $user_id
     * @param string $post_type
     * @param string $title
     * @param string $content
     * @return bool
     */
    public static function addPost(int $game_id, int $user_id, string $post_type, string $title, string $content): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO community_posts (game_id, user_id, post_type, title, content) 
                VALUES (:game_id, :user_id, :post_type, :title, :content)";
        $query = $database->prepare($sql);
        return $query->execute([
            ':game_id'   => $game_id,
            ':user_id'   => $user_id,
            ':post_type' => $post_type,
            ':title'     => $title,
            ':content'   => $content
        ]);
    }

    /**
     * Löscht einen Community-Post.
     *
     * @param int $post_id
     * @param int $user_id
     * @param string $user_role
     * @return bool
     */
    public static function deletePost(int $post_id, int $user_id, string $user_role): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        if ($user_role === 'Admin') {
            $sql = "DELETE FROM community_posts WHERE id = :post_id";
            $query = $database->prepare($sql);
            return $query->execute([':post_id' => $post_id]);
        } else {
            $sql = "DELETE FROM community_posts WHERE id = :post_id AND user_id = :user_id";
            $query = $database->prepare($sql);
            return $query->execute([
                ':post_id' => $post_id,
                ':user_id' => $user_id
            ]);
        }
    }

    /* ---------------------------------------------------------
       Zusätzliche Methoden für ein Forum-/Thread-System
       (Keine Änderung an den vorhandenen Methoden)
       --------------------------------------------------------- */

    /**
     * Holt alle Threads (post_type = 'thread') in absteigender Reihenfolge.
     *
     * @return array
     */
    public static function getAllThreads(): array
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT cp.*, u.user_name 
                FROM community_posts cp
                JOIN users u ON cp.user_id = u.user_id
                WHERE cp.post_type = 'thread'
                ORDER BY cp.created_at DESC";
        $query = $database->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Erstellt einen neuen Thread (post_type = 'thread').
     *
     * @param int $user_id
     * @param string $title
     * @param string $content
     * @param string $category  (Optional, falls in deiner Struktur benötigt)
     * @param int $game_id  (0, wenn global)
     * @return bool
     */
    public static function createThread(int $user_id, string $title, string $content, string $category = '', int $game_id = 0): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO community_posts (game_id, user_id, post_type, title, content)
                VALUES (:game_id, :user_id, 'thread', :title, :content)";
        $query = $database->prepare($sql);
        return $query->execute([
            ':game_id' => $game_id,
            ':user_id' => $user_id,
            ':title'   => $title,
            ':content' => $content
        ]);
    }

    /**
     * Holt einen einzelnen Thread anhand seiner ID (post_type = 'thread').
     *
     * @param int $thread_id
     * @return object|false
     */
    public static function getThreadById(int $thread_id): object|false
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT cp.*, u.user_name 
                FROM community_posts cp
                JOIN users u ON cp.user_id = u.user_id
                WHERE cp.id = :thread_id
                  AND cp.post_type = 'thread'
                LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':thread_id' => $thread_id]);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Erhöht die Anzahl der Views eines Threads.
     *
     * @param int $thread_id
     * @return bool
     */
    public static function increaseThreadViews(int $thread_id): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "UPDATE community_posts SET views = views + 1 WHERE id = :thread_id AND post_type = 'thread'";
        $query = $database->prepare($sql);
        return $query->execute([':thread_id' => $thread_id]);
    }

    /**
     * Holt alle Antworten zu einem Thread (post_type = 'reply').
     *
     * @param int $thread_id
     * @return array
     */
    public static function getRepliesByThreadId(int $thread_id): array
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "SELECT cp.*, u.user_name
                FROM community_posts cp
                JOIN users u ON cp.user_id = u.user_id
                WHERE cp.post_type = 'reply'
                  AND cp.parent_id = :thread_id
                ORDER BY cp.created_at ASC";
        $query = $database->prepare($sql);
        $query->execute([':thread_id' => $thread_id]);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fügt eine Antwort zu einem Thread hinzu (post_type = 'reply').
     *
     * @param int $thread_id
     * @param int $user_id
     * @param string $content
     * @return bool
     */
    public static function addReply(int $thread_id, int $user_id, string $content): bool
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql = "INSERT INTO community_posts (game_id, user_id, post_type, title, content, parent_id)
                VALUES (0, :user_id, 'reply', '', :content, :parent_id)";
        $query = $database->prepare($sql);
        return $query->execute([
            ':user_id'   => $user_id,
            ':content'   => $content,
            ':parent_id' => $thread_id
        ]);
    }
}
