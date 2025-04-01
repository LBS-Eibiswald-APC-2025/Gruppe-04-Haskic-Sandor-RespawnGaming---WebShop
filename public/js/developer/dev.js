$(document).ready(function() {
    // EXE-Datei Upload Handler
    $('#exe_file').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Dateityp überprüfen
            const fileExt = file.name.split('.').pop().toLowerCase();
            if (fileExt !== 'exe') {
                // Fehlermeldung anzeigen
                $('#exe_file_info').html(
                    '<div class="alert alert-danger mb-0">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>' +
                    'Nur .exe-Dateien werden unterstützt.' +
                    '</div>'
                );
                $(this).val(''); // Eingabe zurücksetzen
                return;
            }

            // Dateigröße in MB berechnen
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);

            // Dateinfo anzeigen
            $('#exe_file_info').html(
                '<div class="d-flex align-items-center justify-content-between">' +
                '<div><i class="fas fa-file-code file-icon"></i> ' + file.name + '</div>' +
                '<div class="file-size">' + fileSizeMB + ' MB</div>' +
                '</div>'
            );

            // Visuelle Veränderung des Upload-Bereichs
            $('.custom-file-upload').css({
                'border-style': 'solid',
                'border-color': 'rgba(74, 144, 217, 0.6)',
                'background': 'rgba(74, 144, 217, 0.1)'
            });

            // Icon ändern
            $('.upload-icon-container').html('<i class="fas fa-check-circle upload-icon" style="color: #4a90d9;"></i><div style="color: #fff;">Datei ausgewählt</div>');
        }
    });

    // Cover-Bild Vorschau
    $('#image').on('change', function() {
        const file = this.files[0];
        handleImagePreview(file, '#cover_preview');
    });

    // Screenshot Vorschauen
    $('#tImage1, #tImage2, #tImage3, #tImage4').on('change', function() {
        const file = this.files[0];
        const previewId = '#preview' + $(this).attr('id').substring(6, 7);
        handleImagePreview(file, previewId);
    });

    // Video-Info anzeigen
    $('#video').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Dateigröße in MB berechnen
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);

            // Video-Info anzeigen
            $('#video_info').html(
                '<div class="d-flex align-items-center justify-content-between p-2">' +
                '<div><i class="fas fa-film me-2"></i> ' + file.name + '</div>' +
                '<div class="file-size">' + fileSizeMB + ' MB</div>' +
                '</div>'
            );
        }
    });

    // Systemanforderungen-Karte Umschalter
    $('#sysreq-header').on('click', function() {
        $('#sysreq-body').slideToggle(300);
        $(this).find('.toggle-icon i').toggleClass('fa-chevron-down fa-chevron-up');
    });

    // Lösch-Modal Funktionalität
    $('#deleteGameModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const gameId = button.data('game-id');
        const confirmDeleteBtn = $('#confirmDeleteBtn');

        // Link für den Lösch-Button setzen
        confirmDeleteBtn.attr('href', `${window.location.origin}/developer/delete/${gameId}`);
    });

    // Formularvalidierung
    $('form').on('submit', function(e) {
        let isValid = true;

        // Überprüfe alle erforderlichen Felder
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                isValid = false;

                // Effekt hinzufügen
                if (!$(this).parent().find('.invalid-feedback').length) {
                    $(this).parent().append('<div class="invalid-feedback">Dieses Feld wird benötigt</div>');
                }

                // Schüttel-Animation (wenn jQuery UI verfügbar ist)
                if ($.fn.effect) {
                    $(this).effect('shake', { times: 2, distance: 5 }, 200);
                }
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $(this).parent().find('.invalid-feedback').remove();
            }
        });

        if (!isValid) {
            e.preventDefault();

            // Scroll zum ersten ungültigen Feld
            $('html, body').animate({
                scrollTop: $('.is-invalid:first').offset().top - 100
            }, 500);

            // Fehlermeldung am Anfang des Formulars hinzufügen
            if ($('.form-error-message').length === 0) {
                $('<div class="alert alert-danger form-error-message">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>' +
                    'Bitte fülle alle erforderlichen Felder aus.</div>')
                    .prependTo(this)
                    .hide()
                    .fadeIn();
            }
        } else {
            // Zeige Upload-Fortschritt an
            $(this).find('button[type="submit"]').prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Wird hochgeladen...'
            );

            // Füge einen Fortschrittsbalken hinzu
            if ($('.js-upload-progress').length === 0) {
                $('<div class="js-upload-progress mt-3 mb-2"><div class="progress-bar"></div></div>')
                    .insertBefore('button[type="submit"]');

                // Simuliere Fortschritt (in echter Anwendung würde AJAX verwendet werden)
                let progress = 0;
                const progressInterval = setInterval(function() {
                    progress += 5;
                    $('.progress-bar').css('width', progress + '%');

                    if (progress >= 100) {
                        clearInterval(progressInterval);
                    }
                }, 200);
            }
        }
    });

    // Hilfsfunktion für Bildvorschauen
    function handleImagePreview(file, previewSelector) {
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(previewSelector).html(`<img src="${e.target.result}" class="img-fluid">`).show();
            };
            reader.readAsDataURL(file);
        } else {
            $(previewSelector).empty().hide();
        }
    }

    // Einblendanimation für die Spieleliste
    function animateGamesList() {
        $('.list-group-item').each(function(index) {
            $(this).css({
                'opacity': 0,
                'transform': 'translateY(20px)'
            });

            setTimeout(() => {
                $(this).animate({
                    'opacity': 1,
                    'transform': 'translateY(0)'
                }, 300);
            }, index * 100);
        });
    }

    // Führe die Animation aus, wenn die Elemente vorhanden sind
    if ($('.list-group-item').length > 0) {
        animateGamesList();
    }

    // Tooltips initialisieren (wenn Bootstrap Tooltips verfügbar sind)
    if (typeof $().tooltip === 'function') {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    // Hover-Effekte für Aktionsbuttons
    $('.btn-outline-primary, .btn-outline-info, .btn-outline-danger').hover(
        function() {
            $(this).find('i').addClass('fa-bounce');
        },
        function() {
            $(this).find('i').removeClass('fa-bounce');
        }
    );
});