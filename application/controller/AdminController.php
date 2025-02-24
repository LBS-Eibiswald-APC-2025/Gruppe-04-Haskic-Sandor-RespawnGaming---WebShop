<?php

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Nur Admins dürfen hier rein
        Auth::checkAdminAuthentication();
    }

    public function index(): void
    {
        if (!Auth::checkAdminAuthentication()) {
            Redirect::to('user');
        }

        $this->View->render('admin/index', ['users' => UserModel::getPublicProfilesOfAllUsers()]);
    }

    public function actionAccountSettings(): void
    {
        AdminModel::setAccountSuspensionAndDeletionStatus(
            Request::post('deactivate'),
            Request::post('user_id')
        );

        Redirect::to("admin");
    }

    public function changeUserRole(): void
    {
        if (AdminModel::changeUserRole(Request::post('user_id'),Request::post('user_account_type'))) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_TYPE_CHANGE_SUCCESSFUL'));
            Redirect::to("admin");
        } else {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_TYPE_CHANGE_FAILED'));
            Redirect::to("admin");
        }
    }
}
