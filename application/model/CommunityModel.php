<?php

class CommunityModel
{
    public static function getChatRooms(): array
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT id, name, description, game_type FROM chat_rooms ORDER BY game_type";
        $query = $database->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getChatRoomMessages(int $roomId, int $limit = 50): array
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $currentUserId = $_SESSION['user_id'] ?? 0;

        $sql = "SELECT 
            m.id,
            m.message_text, 
            m.created_at, 
            u.user_name, 
            u.avatar,
            m.user_id = :currentUserId as is_own_message
          FROM messages m
          JOIN users u ON m.user_id = u.user_id
          WHERE m.room_id = :roomId
          ORDER BY m.created_at DESC
          LIMIT :limit";

        $query = $database->prepare($sql);
        $query->bindValue(':roomId', $roomId, PDO::PARAM_INT);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':currentUserId', $currentUserId, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function sendMessage(int $userId, int $roomId, string $messageText): ?int
    {
        // Text sicher machen
        $messageText = htmlspecialchars($messageText, ENT_QUOTES, 'UTF-8');

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO messages (user_id, room_id, message_text, created_at) 
            VALUES (:userId, :roomId, :messageText, NOW())";

        $query = $database->prepare($sql);
        $query->bindValue(':userId', $userId, PDO::PARAM_INT);
        $query->bindValue(':roomId', $roomId, PDO::PARAM_INT);
        $query->bindValue(':messageText', $messageText);

        return $query->execute() ? (int)$database->lastInsertId() : null;
    }
}
