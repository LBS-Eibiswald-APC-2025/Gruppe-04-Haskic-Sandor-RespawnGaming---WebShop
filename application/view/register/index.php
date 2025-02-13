<div class="container">

    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>

    <!-- login box on left side -->
    <div class="login-box" style="width: 50%; display: block;">
        <h2>Neuen Account erstellen</h2>

        <!-- register form -->
        <form method="post" action="<?php echo Config::get('URL'); ?>register/register_action">
            <!-- the user name input field uses a HTML5 pattern check -->
            <input type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" placeholder="Benutzername" required />
            <input type="text" name="user_email" placeholder="Email Adresse" required />
            <input type="text" name="user_email_repeat" placeholder="Email erneut eingeben" required />
            <input type="password" name="user_password_new" pattern=".{6,}" placeholder="Password (min. 6+ Ziffern)" required autocomplete="off" />
            <input type="password" name="user_password_repeat" pattern=".{6,}" required placeholder="Password erneut eingeben" autocomplete="off" />

            <!-- show the captcha by calling the login/showCaptcha-method in the src attribute of the img tag -->
            <img id="captcha" src="<?php echo Config::get('URL'); ?>register/showCaptcha" />
            <input type="text" name="captcha" placeholder="Captcha eingeben" required />

            <!-- quick & dirty captcha reloader -->
            <a href="#" style="display: block; font-size: 11px; margin: 5px 0 15px 0; text-align: center"
               onclick="document.getElementById('captcha').src = '<?php echo Config::get('URL'); ?>register/showCaptcha?' + Math.random(); return false">Neues Captcha laden</a>

            <input type="submit" value="Anmelden" />
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
