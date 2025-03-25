$(document).ready(function(){
    function handleImageUpload(type) {
        const titles = {
            avatar: "Avatar ändern",
            banner: "Banner ändern"
        };

        const texts = {
            avatar: "Wähle ein neues Avatarbild aus. (Max. 2MB | 200x200px)",
            banner: "Wähle ein neues Bannerbild aus. (Max. 5MB | 1920x300px)"
        };

        swal({
            title: titles[type],
            text: texts[type],
            content: {
                element: "div",
                attributes: {
                    innerHTML: `
                    <div class="file-upload-wrapper">
                        <label class="custom-file-upload">
                            <input type="file" accept="image/*" id="${type}-upload">
                            Datei auswählen
                        </label>
                        <div class="file-name"></div>
                    </div>
                `
                }
            },
            buttons: {
                cancel: "Abbrechen",
                confirm: "Hochladen"
            }
        }).then((value) => {
            if (value) {
                const file = document.getElementById(`${type}-upload`).files[0];
                if (file) {
                    const formData = new FormData();
                    formData.append(type, file);

                    $.ajax({
                        url: `https://respawngaming.at/user/${type}_upload`,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (type === 'avatar') {
                                $('.avatar img').attr('src', URL.createObjectURL(file));
                            } else if (type === 'banner') {
                                $('.cover').attr('src', URL.createObjectURL(file));
                            }

                            swal("Erfolg!", `Dein ${type} wurde erfolgreich aktualisiert.`, "success")
                                .then(() => location.reload());
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON?.message ||
                                `Beim Hochladen ist ein Fehler aufgetreten.`;
                            swal("Fehler", errorMsg, "error");
                        }
                    });
                }
            }
        });

        // Dateinamen anzeigen
        document.getElementById(`${type}-upload`).addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'Keine Datei ausgewählt';
            this.closest('.file-upload-wrapper').querySelector('.file-name').textContent = fileName;
        });
    }

    $('.icon-library').click(function(){
        $([document.documentElement, document.body]).animate({
            scrollTop: $("#library").offset().top
        }, 1000);
    });

    $('.icon-edit').click(function(){
       window.location.href = 'https://respawngaming.at/user/user_edit';
    })


    $('.icon-avatar').click(() => handleImageUpload('avatar'));

    $('.icon-banner').click(() => handleImageUpload('banner'));
})