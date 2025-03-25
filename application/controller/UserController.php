<?php

/**
 * UserController
 * Controls everything that is user-related
 */
class UserController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class.
     */
    public function __construct()
    {
        parent::__construct();

        // VERY IMPORTANT: All controllers/areas that should only be usable by logged-in users
        // need this line! Otherwise, not-logged in users could do actions.
        Auth::checkAuthentication();
    }

    /**
     * Show user's PRIVATE profile
     */
    public function index(): void
    {
        error_reporting(0);
        $this->View->render('user/index');
    }

   public function user_edit(): void
    {
        error_reporting(0);
        $this->View->render('user/user_edit');
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function saveChanges(): void
    {
        error_reporting(0);
        UserModel::saveUserEdit($_POST);
        Redirect::to('user/index');
    }

    public function avatar_upload(): void
    {
        error_reporting(0);
        header('Content-Type: application/json');

        if (!isset($_FILES['avatar'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Keine Datei gefunden']);
            return;
        }

        $result = UserModel::uploadAvatar($_FILES['avatar']);

        if ($result['success']) {
            echo json_encode([
                'message' => 'Avatar erfolgreich aktualisiert',
                'path' => $result['path'] // Neue URL zurückgeben
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['message' => $result['message']]);
        }
    }

    public function banner_upload(): void
    {
        error_reporting(0);
        header('Content-Type: application/json');

        if (!isset($_FILES['banner'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Keine Datei gefunden']);
            return;
        }

        $result = UserModel::uploadBanner($_FILES['banner']);

        if ($result['success']) {
            echo json_encode([
                'message' => 'Banner erfolgreich aktualisiert',
                'path' => $result['path'] // Neue URL zurückgeben
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['message' => $result['message']]);
        }
    }
}
