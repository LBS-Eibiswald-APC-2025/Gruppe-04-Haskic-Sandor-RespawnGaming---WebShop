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
}
