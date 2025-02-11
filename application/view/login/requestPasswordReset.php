<div class="container">
    <h1>Password zurücksetzung</h1>
    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <!-- request password reset form box -->
        <form method="post" action="<?php echo Config::get('URL'); ?>login/requestPasswordReset_action">
            <label for="user_name_or_email">
                Bitte gibt deinen Benutzernamen oder dein E-Mail-Adresse ein:
                <input type="text" name="user_name_or_email" required />
            </label>

            <!-- show the captcha by calling the login/showCaptcha-method in the src attribute of the img tag -->
            <img id="captcha" src="<?php echo Config::get('URL'); ?>register/showCaptcha" /><br/>
            <input type="text" name="captcha" placeholder="Captcha eingabe" required />

            <!-- quick & dirty captcha reloader -->
            <a href="#" style="display: block; font-size: 11px; margin: 5px 0 15px 0;"
               onclick="document.getElementById('captcha').src = '<?php echo Config::get('URL'); ?>register/showCaptcha?' + Math.random(); return false">Neues Captcha laden<</a>

            <input type="submit" value="Password Rücksetzens Mail versenden" />
        </form>

    </div>
</div>
<div class="container">
    <p style="display: block; font-size: 11px; color: #999;">
        Bitte beachten: Dieses Captcha wird generiert, wenn der img-Tag die Captcha-Generierung
        (= ein echtes Bild) von YOURURL/register/showcaptcha anfordert. Da es sich um eine clientseitig ausgelöste Anforderung handelt,
        zeigt ein $_SESSION["captcha"]-Dump die Captcha-Zeichen nicht an. Die Captcha-Generierung erfolgt, NACHDEM die Anforderung,
        die DIESE Seite generiert, abgeschlossen ist.
    </p>
</div>
