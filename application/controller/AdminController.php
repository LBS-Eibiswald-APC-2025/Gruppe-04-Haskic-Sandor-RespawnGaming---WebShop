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
}
