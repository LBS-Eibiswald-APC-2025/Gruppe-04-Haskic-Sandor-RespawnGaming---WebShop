<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<main>
    <div class="auth-page-box">
        <div class="verify_box">
            <h1>Verifikation</h1>

            <a href="<?php echo Config::get('URL'); ?>login" class="verification-button">Zum Login</a>
        </div>
    </div>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>