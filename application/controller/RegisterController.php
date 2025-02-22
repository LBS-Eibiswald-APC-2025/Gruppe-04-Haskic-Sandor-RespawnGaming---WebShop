<?php

use Random\RandomException;

/**
 * RegisterController
 * Register new user
 */
class RegisterController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class. The parent::__construct thing is necessary to
     * put checkAuthentication in here to make an entire controller only usable for logged-in users (for sure not
     * needed in the RegisterController).
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Register page
     * Show the register form, but redirect to main-page if user is already logged-in
     */
    public function index(): void
    {
        if (LoginModel::isUserLoggedIn()) {
            Redirect::home();
        } else {
            $this->View->render('register/index');
        }
    }

    /**
     * Register page action
     * POST-request after form submitted
     * @throws RandomException
     */
    public function register_action(): void
    {
        $registration_successful = RegistrationModel::registerNewUser();

        if ($registration_successful) {
            Redirect::to('login/index');
        } else {
            Redirect::to('register/index');
        }
    }

    /**
     * Verify user after activation mail link opened
     * @param int|null $user_id user's id
     * @param string|null $user_activation_verification_code user's verification token
     */
    public function verify(?int $user_id = null, ?string $user_activation_verification_code = null): void
    {
        if (isset($user_id)) {
            RegistrationModel::verifyNewUser($user_id, $user_activation_verification_code);
            $this->View->render('register/verify');
        } else {
            Session::add('feedback_negative', Text::get('FEEDBACK_VERIFICATION_FAILED'));
            Redirect::to('login/index');
        }
    }

    /**
     * Generate a captcha, write the characters into $_SESSION['captcha'] and returns a real image which will be used
     * like this: <img src="......./login/showCaptcha" />
     * IMPORTANT: As this action is called via <img ...> AFTER the real app has finished executing (!), the
     * SESSION["captcha"] has no content when the app is loaded. The SESSION["captcha"] gets filled at the
     * moment the end-user requests the <img. >
     * Maybe refactor this sometime.
     */
    public function showCaptcha(): void
    {
        CaptchaModel::generateAndShowCaptcha();
    }
}
