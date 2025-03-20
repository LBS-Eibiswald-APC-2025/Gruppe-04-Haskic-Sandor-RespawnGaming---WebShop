<?php

/* Using PHPMailer's namespace */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Class Mail
 *
 * Handles everything regarding mail-sending.
 */
#[AllowDynamicProperties] class Mail
{

    /**
     * Try to send a mail by using PHP's native mail() function.
     * Please note that not PHP itself will send a mail, it's just a wrapper for Linux's sendmail or other mail tools
     *
     * Good guideline on how to send mails natively with mail():
     * @see http://stackoverflow.com/a/24644450/1114320
     * @see http://www.php.net/manual/en/function.mail.php
     */
    public function sendMailWithNativeMailFunction(): false
    {
        // no code yet, so we just return something to make IDEs and code analyzer tools happy
        return false;
    }

    /**
     * Try to send a mail by using SwiftMailer.
     * Make sure you have loaded SwiftMailer via Composer.
     *
     * @return bool
     */
    public function sendMailWithSwiftMailer(): bool
    {
        // no code yet, so we just return something to make IDEs and code analyzer tools happy
        return false;
    }

    /**
     * Try to send a mail by using PHPMailer.
     * Make sure you have loaded PHPMailer via Composer.
     * Depending on your EMAIL_USE_SMTP setting this will work via SMTP credentials or via native mail()
     *
     * @param $user_email
     * @param $from_email
     * @param $from_name
     * @param $subject
     * @param $body
     * @param null $attachement
     * @return bool
     * @throws Exception
     */
    public function sendMailWithPHPMailer($user_email, $from_email, $from_name, $subject, $body, $attachement = null): bool
    {
        $mail = new PHPMailer(true);

        // you should use UTF-8 to avoid encoding issues
        $mail->CharSet = 'UTF-8';

        // if you want to send mail via PHPMailer using SMTP credentials
        if (Config::get('EMAIL_USE_SMTP')) {

            // set PHPMailer to use SMTP
            $mail->IsSMTP();
            $mail->isHTML();

            // 0 = off, 1 = commands, 2 = commands and data, perfect to see SMTP errors
            $mail->SMTPDebug = 0;

            // enable SMTP authentication
            $mail->SMTPAuth = Config::get('EMAIL_SMTP_AUTH');

            // encryption
            if (Config::get('EMAIL_SMTP_ENCRYPTION')) {
                $mail->SMTPSecure = Config::get('EMAIL_SMTP_ENCRYPTION');
            }

            // set SMTP provider's credentials
            $mail->Host = Config::get('EMAIL_SMTP_HOST');
            $mail->Username = Config::get('EMAIL_SMTP_USERNAME');
            $mail->Password = Config::get('EMAIL_SMTP_PASSWORD');
            $mail->Port = Config::get('EMAIL_SMTP_PORT');

        } else {

            $mail->IsMail();
        }

        // fill mail with data
        $mail->setFrom($from_email, $from_name);
        $mail->AddAddress($user_email);
        $mail->Subject = $subject;
        $mail->Body = $body;

        if (isset($attachement) && $attachement != null) {
            $mail->addAttachment($attachement);
        }

        // try to send mail, put result status (true/false into $wasSendingSuccessful)
        // I'm unsure if mail->send really returns true or false every time, this method in PHPMailer is quite complex
        $wasSendingSuccessful = $mail->Send();

        if ($wasSendingSuccessful) {
            return true;

        } else {

            // if not successful, copy errors into Mail's error property
            return $mail->ErrorInfo;
        }
    }

    /**
     * The main mail sending method, this simply calls a certain mail sending method depending on which mail provider
     * you've selected in the app's config.
     *
     * @param $user_email string email
     * @param $from_email string sender's email
     * @param $from_name string sender's name
     * @param $subject string subject
     * @param $body string full mail body text
     * @return bool the success status of the according mail sending method
     * @throws Exception
     */
    public function sendMail(string $user_email, string $from_email, string $from_name, string $subject, string $body, ?string $attachement = null): bool
    {
        if (Config::get('EMAIL_USED_MAILER') == "phpmailer") {

            // returns true if successful, false if not
            return $this->sendMailWithPHPMailer(
                $user_email, $from_email, $from_name, $subject, $body, $attachement
            );
        }

        if (Config::get('EMAIL_USED_MAILER') == "swiftmailer") {
            return $this->sendMailWithSwiftMailer();
        }

        if (Config::get('EMAIL_USED_MAILER') == "native") {
            return $this->sendMailWithNativeMailFunction();
        }
    return false;}


}
