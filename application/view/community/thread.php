<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>


<div class="community-page">
    <div class="container mt-5">
        <?php if ($thread): ?>

            <!-- NEU: Hier den thread-item-Container mit data-category öffnen -->
            <div class="thread-item" data-category="<?php echo htmlspecialchars($thread->category); ?>">

                <h2 class="mb-4"><?php echo htmlspecialchars($thread->title); ?></h2>
                <p class="text-white mb-4">
                    Kategorie: <?php echo htmlspecialchars($thread->category); ?> |
                    Antworten: <?php echo $thread->replies; ?> |
                    Aufrufe: <?php echo $thread->views; ?>
                </p>

                <!-- Thread-Inhalt -->
                <div class="thread-snippet mb-4 p-3 bg-dark text-white rounded">
                    <?php echo nl2br(htmlspecialchars($thread->snippet)); ?>
                </div>

                <!-- Posts / Antworten -->
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="thread-post mb-3 p-3 bg-secondary text-white rounded">
                            <p class="mb-1">
                                <strong><?php echo htmlspecialchars($post->author); ?></strong> schrieb am
                                <?php echo htmlspecialchars($post->created_at); ?>:
                            </p>
                            <div class="post-content">
                                <?php echo nl2br(htmlspecialchars($post->content)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-white">Keine Antworten vorhanden.</p>
                <?php endif; ?>

                <!-- Formular: Neue Antwort erstellen -->
                <hr class="text-white my-4">
                <h4 class="text-white mb-3">Antwort erstellen</h4>
                <form method="post" action="<?php echo Config::get('URL'); ?>community/createPost_action">
                    <input type="hidden" name="thread_id" value="<?php echo $thread->id; ?>">
                    <div class="mb-3">
                        <label>
                            <textarea class="form-control" name="postContent" rows="5" placeholder="Dein Beitrag..." required></textarea>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">Antwort senden</button>
                </form>

                <!-- NEU: Hier den thread-item-Container schließen -->
            </div>

        <?php else: ?>
            <p class="text-white">Thread wurde nicht gefunden.</p>
        <?php endif; ?>
    </div>
</div>

<?php require APP . 'view/_templates/footer.php'; ?>
