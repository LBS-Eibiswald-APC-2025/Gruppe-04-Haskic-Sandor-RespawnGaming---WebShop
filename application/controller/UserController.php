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
        // need this line! Otherwise not-logged in users could do actions.
        Auth::checkAuthentication();
    }

    /**
     * Show user's PRIVATE profile
     */
    public function index(): void
    {
        $this->View->render('user/index');
    }

   public function user_edit(): void
    {
        $this->View->render('user/user_edit');
    }

    public function saveChanges(): void
    {
        UserModel::saveUserEdit($_POST);
        Redirect::to('user/index');
    }
}
