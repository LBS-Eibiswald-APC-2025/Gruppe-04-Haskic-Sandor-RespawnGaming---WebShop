<?php require APP . 'view/_templates/header.php'; ?>
<?php require APP . 'view/_templates/feedback.php'; ?>

    <div class="developer-dashboard">
        <h2 class="special-text">Entwickler-Dashboard</h2>

        <div class="row">
            <div class="col-md-12">
                <h3>Neues Spiel hochladen</h3>
                <form action="<?php echo Config::get('URL'); ?>developer/upload" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Spieltitel</label>
                                <input type="text" class="form-control glow-on-hover" id="title" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Beschreibung</label>
                                <textarea class="form-control glow-on-hover" id="description" name="description" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Preis (€)</label>
                                <input type="number" class="form-control glow-on-hover" id="price" name="price" step="0.01" required>
                            </div>

                            <div class="mb-3">
                                <label for="genre" class="form-label">Genre</label>
                                <select class="form-control glow-on-hover" id="genre" name="genre" required>
                                    <option value="">Genre auswählen...</option>
                                    <option value="action">Action</option>
                                    <option value="adventure">Adventure</option>
                                    <option value="rpg">RPG</option>
                                    <option value="strategy">Strategie</option>
                                    <option value="simulation">Simulation</option>
                                    <option value="sports">Sport</option>
                                    <option value="racing">Racing</option>
                                    <option value="puzzle">Puzzle</option>
                                    <option value="shooter">Shooter</option>
                                    <option value="horror">Horror</option>
                                    <option value="indie">Indie</option>
                                    <option value="other">Sonstiges</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Kategorie</label>
                                <select class="form-control glow-on-hover" id="category" name="category" required>
                                    <option value="">Kategorie auswählen...</option>
                                    <option value="singleplayer">Singleplayer</option>
                                    <option value="multiplayer">Multiplayer</option>
                                    <option value="coop">Kooperativ</option>
                                    <option value="vr">VR-Unterstützung</option>
                                    <option value="controller">Controller-Unterstützung</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="release_date" class="form-label">Erscheinungsdatum</label>
                                <input type="date" class="form-control glow-on-hover" id="release_date" name="release_date">
                            </div>

                            <div class="mb-3">
                                <label for="discount" class="form-label">Rabatt (%)</label>
                                <input type="number" class="form-control glow-on-hover" id="discount" name="discount" min="0" max="100" step="1" value="0">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="file-uploads-container">
                                <div class="mb-3">
                                    <label for="exe_file" class="form-label executable-label">
                                        <i class="fas fa-gamepad me-2"></i>Spiel-Executable (.exe)
                                    </label>
                                    <div class="custom-file-upload">
                                        <input type="file" class="form-control" id="exe_file" name="exe_file" accept=".exe" required>
                                        <div class="upload-icon-container">
                                            <i class="fas fa-upload upload-icon"></i>
                                        </div>
                                    </div>
                                    <div class="file-info" id="exe_file_info"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="image" class="form-label">Spielcover-Bild</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                    <div class="upload-preview" id="cover_preview"></div>
                                </div>

                                <div class="mb-3 timageDiv">
                                    <label class="form-label">Screenshots (4 Bilder)</label>
                                    <div class="timage-grid">
                                        <div class="timage-container">
                                            <input type="file" class="timage" id="tImage1" name="tImage1" accept="image/*" required>
                                            <div class="thumbnail-preview" id="preview1"></div>
                                        </div>
                                        <div class="timage-container">
                                            <input type="file" class="timage" id="tImage2" name="tImage2" accept="image/*" required>
                                            <div class="thumbnail-preview" id="preview2"></div>
                                        </div>
                                        <div class="timage-container">
                                            <input type="file" class="timage" id="tImage3" name="tImage3" accept="image/*" required>
                                            <div class="thumbnail-preview" id="preview3"></div>
                                        </div>
                                        <div class="timage-container">
                                            <input type="file" class="timage" id="tImage4" name="tImage4" accept="image/*" required>
                                            <div class="thumbnail-preview" id="preview4"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="video" class="form-label">Spieltrailer-Video</label>
                                    <input type="file" class="form-control" id="video" name="video" accept="video/*">
                                    <div class="video-info" id="video_info"></div>
                                </div>
                            </div>

                            <!-- Systemanforderungen Bereich -->
                            <div class="card mb-4 system-requirements-card">
                                <div class="card-header" id="sysreq-header">
                                    <h5 class="mb-0">Systemanforderungen</h5>
                                    <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                                </div>
                                <div class="card-body" id="sysreq-body">
                                    <!-- Mindestanforderungen -->
                                    <h6>Mindestanforderungen</h6>
                                    <div class="mb-2">
                                        <label for="min_os" class="form-label">Betriebssystem</label>
                                        <input type="text" class="form-control glow-on-hover" id="min_os" name="systemRequirements[min_os]" placeholder="z.B. Win 10 64 Bit">
                                    </div>
                                    <div class="mb-2">
                                        <label for="min_processor" class="form-label">Prozessor</label>
                                        <input type="text" class="form-control glow-on-hover" id="min_processor" name="systemRequirements[min_processor]" placeholder="z.B. Intel i5-4690 / AMD Ryzen 3 1200">
                                    </div>
                                    <div class="mb-2">
                                        <label for="min_memory" class="form-label">Arbeitsspeicher</label>
                                        <input type="text" class="form-control glow-on-hover" id="min_memory" name="systemRequirements[min_memory]" placeholder="z.B. 8 GB RAM">
                                    </div>
                                    <div class="mb-2">
                                        <label for="min_graphics" class="form-label">Grafik</label>
                                        <input type="text" class="form-control glow-on-hover" id="min_graphics" name="systemRequirements[min_graphics]" placeholder="z.B. NVIDIA GTX 1050 / AMD RX 460">
                                    </div>
                                    <div class="mb-2">
                                        <label for="min_directx" class="form-label">DirectX</label>
                                        <input type="text" class="form-control glow-on-hover" id="min_directx" name="systemRequirements[min_directx]" placeholder="z.B. Version 12">
                                    </div>
                                    <div class="mb-3">
                                        <label for="min_storage" class="form-label">Speicherplatz</label>
                                        <input type="text" class="form-control glow-on-hover" id="min_storage" name="systemRequirements[min_storage]" placeholder="z.B. 20 GB verfügbarer Speicherplatz">
                                    </div>

                                    <hr>

                                    <!-- Empfohlene Anforderungen -->
                                    <h6>Empfohlene Anforderungen</h6>
                                    <div class="mb-2">
                                        <label for="rec_os" class="form-label">Betriebssystem</label>
                                        <input type="text" class="form-control glow-on-hover" id="rec_os" name="systemRequirements[rec_os]" placeholder="z.B. Win 10 64 Bit">
                                    </div>
                                    <div class="mb-2">
                                        <label for="rec_processor" class="form-label">Prozessor</label>
                                        <input type="text" class="form-control glow-on-hover" id="rec_processor" name="systemRequirements[rec_processor]" placeholder="z.B. Intel Core i5-10400 / AMD Ryzen 5 3600X">
                                    </div>
                                    <div class="mb-2">
                                        <label for="rec_memory" class="form-label">Arbeitsspeicher</label>
                                        <input type="text" class="form-control glow-on-hover" id="rec_memory" name="systemRequirements[rec_memory]" placeholder="z.B. 16 GB RAM">
                                    </div>
                                    <div class="mb-2">
                                        <label for="rec_graphics" class="form-label">Grafik</label>
                                        <input type="text" class="form-control glow-on-hover" id="rec_graphics" name="systemRequirements[rec_graphics]" placeholder="z.B. NVIDIA RTX 2060 / AMD RX 6600">
                                    </div>
                                    <div class="mb-2">
                                        <label for="rec_directx" class="form-label">DirectX</label>
                                        <input type="text" class="form-control glow-on-hover" id="rec_directx" name="systemRequirements[rec_directx]" placeholder="z.B. Version 12">
                                    </div>
                                    <div class="mb-2">
                                        <label for="rec_storage" class="form-label">Speicherplatz</label>
                                        <input type="text" class="form-control glow-on-hover" id="rec_storage" name="systemRequirements[rec_storage]" placeholder="z.B. 50 GB verfügbarer Speicherplatz">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="publish-controls">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="license_required" name="license_required" value="1">
                            <label class="form-check-label" for="license_required">
                                Produktlizenz erforderlich (für DRM-geschützte Spiele)
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary game-upload-btn">
                            <i class="fas fa-upload me-2"></i>Spiel veröffentlichen
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-md-12 mt-5">
                <h3>Meine Spiele</h3>
                <?php if (!empty($this->data['games'])): ?>
                    <div class="list-group game-list-container">
                        <?php foreach ($this->data['games'] as $game): ?>
                            <div class="list-group-item">
                                <div class="row">
                                    <div class="col-md-2 col-sm-3">
                                        <?php if (!empty($game->image)): ?>
                                            <img src="<?php echo Config::get('URL') . $game->image; ?>" class="img-fluid game-list-thumbnail" alt="<?php echo htmlspecialchars($game->title); ?>">
                                        <?php else: ?>
                                            <div class="no-image-placeholder">
                                                <i class="fas fa-images"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 col-sm-5">
                                        <h5><?php echo htmlspecialchars($game->title); ?></h5>
                                        <p><?php echo substr(htmlspecialchars($game->description), 0, 120) . (strlen($game->description) > 120 ? '...' : ''); ?></p>
                                        <div class="game-meta">
                                            <span class="badge badge-primary"><?php echo htmlspecialchars($game->genre); ?></span>
                                            <?php if (!empty($game->discount) && $game->discount > 0): ?>
                                                <span class="badge badge-success"><?php echo $game->discount; ?>% Rabatt</span>
                                            <?php endif; ?>
                                            <span class="game-price"><?php echo number_format($game->price, 2); ?>€</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4 text-end game-actions">
                                        <a href="<?php echo Config::get('URL'); ?>developer/edit/<?php echo $game->id; ?>" class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fas fa-edit"></i> Bearbeiten
                                        </a>
                                        <a href="<?php echo Config::get('URL'); ?>developer/stats/<?php echo $game->id; ?>" class="btn btn-sm btn-outline-info me-2">
                                            <i class="fas fa-chart-line"></i> Statistiken
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" data-game-id="<?php echo $game->id; ?>" data-bs-toggle="modal" data-bs-target="#deleteGameModal">
                                            <i class="fas fa-trash-alt"></i> Löschen
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Löschen-Modal -->
                    <div class="modal fade" id="deleteGameModal" tabindex="-1" aria-labelledby="deleteGameModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteGameModalLabel">Spiel löschen</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                                </div>
                                <div class="modal-body">
                                    Bist du sicher, dass du dieses Spiel löschen möchtest? Diese Aktion kann nicht rückgängig gemacht werden.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Löschen bestätigen</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-games-message">
                        <div class="empty-games-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <p>Keine Spiele verfügbar</p>
                        <p class="text-muted">Lade dein erstes Spiel hoch, um zu beginnen</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php require APP . 'view/_templates/footer.php'; ?>