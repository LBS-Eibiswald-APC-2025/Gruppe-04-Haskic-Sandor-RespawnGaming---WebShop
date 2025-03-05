<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<main>
    <div class="detail_container">
        <div class="detail_content">
            <h1 class="detail_title"><?php echo htmlspecialchars($this->data['post']->title); ?></h1>
            <div class="detail_meta">
                <span class="author">Von: <?php echo htmlspecialchars($this->data['post']->author); ?></span>
                <span class="date">Erstellt am: <?php echo date('d.m.Y H:i', strtotime($this->data['post']->created_at)); ?></span>
            </div>
            <div class="detail_text">
                <?php echo nl2br(htmlspecialchars($this->data['post']->content)); ?>
            </div>
        </div>
        <a href="<?php echo Config::get('URL'); ?>community/index" class="btn btn-primary">Zurück zur Übersicht</a>
    </div>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>