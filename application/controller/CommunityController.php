<?php

class CommunityController extends Controller
{
    public function __construct($parameters = [])
    {
        parent::__construct($parameters);
    }

    public function index(): void
    {
        if (!LoginModel::isUserLoggedIn()) {
            Session::set('feedback_negative', 'Bitte logge dich zuerst ein einlogge.');
            Redirect::to('login/index');
        }

        $chatRooms = CommunityModel::getChatRooms();
        $this->View->render('community/index', ['chatRooms' => $chatRooms]);
    }

    public function chatRoom(): void
    {
        $messages = CommunityModel::getChatRoomMessages($this->parameters[0] ?? 0);
        $roomDetails = self::_getRoomDetails($this->parameters[0] ?? 0);

        $this->View->render('community/chat_room', ['messages' => $messages, 'roomDetails' => $roomDetails]);
    }

    public function sendMessage(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $this->_getCurrentUserId();
            $roomId = $_POST['room_id'] ?? null;
            $messageText = $_POST['message'] ?? '';

            if ($roomId && $messageText) {
                $messageId = CommunityModel::sendMessage($userId, $roomId, $messageText);

                if ($messageId) {
                    echo json_encode([
                        'status' => 'success',
                        'username' => $_SESSION['user_name'] ?? 'Unbekannt',
                        'avatar' => $_SESSION['avatar'] ?? '/avatars/default.jpg',
                        'isOwnMessage' => true,
                        'messageId' => $messageId // Neue Message-ID
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Nachrichtenversand fehlgeschlagen'
                    ]);
                }
            }
        }
        exit();
    }

    public function getNewMessages(): void
    {
        header('Content-Type: application/json');

        $roomId = $_GET['room_id'] ?? null;
        $lastMessageId = $_GET['last_message_id'] ?? 0;

        if ($roomId) {
            $database = DatabaseFactory::getFactory()->getConnection();
            $currentUserId = $this->_getCurrentUserId();

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
            AND m.id > :lastMessageId
            ORDER BY m.created_at ASC";

            $query = $database->prepare($sql);
            $query->bindValue(':roomId', $roomId, PDO::PARAM_INT);
            $query->bindValue(':lastMessageId', $lastMessageId, PDO::PARAM_INT);
            $query->bindValue(':currentUserId', $currentUserId, PDO::PARAM_INT);
            $query->execute();

            $messages = $query->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['messages' => $messages]);
        } else {
            echo json_encode(['messages' => []]);
        }
        exit();
    }

    private function _getCurrentUserId(): ?int
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }
        return (int) $userId;
    }

    private function _getRoomDetails(int $roomId): array
    {
        //get details from the database
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT id, name, description FROM chat_rooms WHERE id = :roomId";
        $query = $database->prepare($sql);
        $query->bindValue(':roomId', $roomId, PDO::PARAM_INT);
        $query->execute();
        $roomDetails = $query->fetch(PDO::FETCH_ASSOC);
        if (!$roomDetails) {
            Session::set('feedback_negative', 'Raum nicht gefunden.');
            Redirect::to('community/index');
        }
        return $roomDetails;
    }

    private static function _setFeedback(string $string, string $string1): void
    {
        if ($string1 === 'success') {
            Session::set('feedback', $string);
        } elseif ($string1 === 'error') {
            Session::set('feedback', $string);
        }
    }

    public function updateTypingStatus(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $roomId = $data['room_id'] ?? null;
            $isTyping = $data['is_typing'] ?? false;
            $userId = $_SESSION['user_id'] ?? null;

            if ($roomId && $userId) {
                $database = DatabaseFactory::getFactory()->getConnection();

                $sql = "INSERT INTO typing_status (user_id, room_id, is_typing) 
                    VALUES (:userId, :roomId, :isTyping)
                    ON DUPLICATE KEY UPDATE 
                    is_typing = :isTyping,
                    last_update = CURRENT_TIMESTAMP";

                $query = $database->prepare($sql);
                $query->execute([
                    ':userId' => $userId,
                    ':roomId' => $roomId,
                    ':isTyping' => $isTyping
                ]);

                echo json_encode(['success' => true]);
                exit();
            }
        }
        echo json_encode(['success' => false]);
        exit();
    }

    public function getTypingUsers(): void
    {
        header('Content-Type: application/json');

        $roomId = $_GET['room_id'] ?? null;
        if ($roomId) {
            $database = DatabaseFactory::getFactory()->getConnection();

            $sql = "SELECT u.user_name 
                FROM typing_status ts
                JOIN users u ON ts.user_id = u.user_id
                WHERE ts.room_id = :roomId 
                AND ts.is_typing = 1
                AND ts.last_update >= DATE_SUB(NOW(), INTERVAL 5 SECOND)";

            $query = $database->prepare($sql);
            $query->execute([':roomId' => $roomId]);

            $typingUsers = $query->fetchAll(PDO::FETCH_COLUMN);

            echo json_encode(['typing_users' => $typingUsers]);
            exit();
        }
        echo json_encode(['typing_users' => []]);
        exit();
    }
}
