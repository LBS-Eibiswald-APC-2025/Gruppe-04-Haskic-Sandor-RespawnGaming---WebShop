<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

<div class="community-page">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Community-Bereich</h2>
        <p class="text-center mb-4">
            Willkommen im Community-Bereich von Respawn Gaming! Hier kannst du dich mit anderen Gamern austauschen und diskutieren.
        </p>

        <!-- Kategorie-Tabs -->
        <div class="forum-categories mb-4">
            <ul class="nav nav-tabs justify-content-center">
                <li class="nav-item"><a class="nav-link active" data-category="all" href="#">Alle Themen</a></li>
                <li class="nav-item"><a class="nav-link" data-category="announcements" href="#">Ankündigungen</a></li>
                <li class="nav-item"><a class="nav-link" data-category="discussions" href="#">Diskussionen</a></li>
            </ul>
        </div>

        <!-- Button: Neues Thema erstellen -->
        <div class="text-center mb-4">
            <button id="newThreadBtn" class="btn btn-primary">Neues Thema erstellen</button>
        </div>

        <!-- Forum-Threads-Liste -->
        <div class="forum-threads">
            <div class="thread-list">
                <?php if (!empty($this->data['threads'])): ?>
                    <?php foreach ($this->data['threads'] as $thread): ?>
                        <?php
//                            echo '<pre>';
//                            var_dump($thread);
//                            echo '</pre>';

                            $thread = (array) $thread;
                        ?>
                        <div class="thread-item" data-category="<?php echo htmlspecialchars($thread['category']); ?>">
                            <div class="thread-title">
                                <a href="<?php echo Config::get('URL'); ?>community/detail/<?php echo $thread['id']; ?>">
                                    <?php echo htmlspecialchars($thread['title']); ?>
                                </a>
                            </div>
                            <div class="thread-meta">
                                <span class="author">Von: <?php echo htmlspecialchars($thread['author']); ?></span>
<!--                                <span class="replies">--><?php //echo $thread['replies']; ?><!-- Antworten</span>-->
                                <span class="views"><?php echo $thread['views']; ?> Aufrufe</span>
<!--                                <span class="last-reply">Letzte Antwort: --><?php //echo $thread['last_reply']; ?><!--</span>-->
                            </div>
                            <div class="thread-snippet">
                                <?php echo nl2br(htmlspecialchars($thread['content'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-white text-center">Keine Threads vorhanden.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination (falls du das nutzt) -->
        <div class="pagination-container mt-4">
            <?php if (isset($pagination)) {
                echo $pagination->createLinks();
            } ?>
        </div>
    </div>
</div>

<!-- Modal: Neues Thema erstellen -->
<div class="modal" id="newThreadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="newThreadForm" method="post" action="<?php echo Config::get('URL'); ?>community/createThread">
                <div class="modal-header">
                    <h5 class="modal-title">Neues Thema erstellen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="threadTitle" class="form-label">Titel</label>
                        <input type="text" class="form-control" id="threadTitle" name="threadTitle" placeholder="Thema eingeben" required>
                    </div>
                    <div class="mb-3">
                        <label for="threadCategory" class="form-label">Kategorie</label>
                        <select class="form-select" id="threadCategory" name="threadCategory" required>
                            <option value="announcements">Ankündigungen</option>
                            <option value="support">Support</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="threadContent" class="form-label">Inhalt</label>
                        <textarea class="form-control" id="threadContent" name="threadContent" rows="5" placeholder="Dein Beitrag..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Thema erstellen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APP . 'view/_templates/footer.php'; ?>
