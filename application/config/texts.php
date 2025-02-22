<?php
/**
 * Deutsche Texte, die in der App verwendet werden.
 * Diese Texte werden über Text::get('FEEDBACK_USERNAME_ALREADY_TAKEN') aufgerufen.
 * Könnte für i18n etc. erweitert werden.
 */
return array(
    "FEEDBACK_UNKNOWN_ERROR" => "Ein unbekannter Fehler ist aufgetreten!",
    "FEEDBACK_DELETED" => "Das Konto wurde gelöscht.",
    "FEEDBACK_ACCOUNT_SUSPENDED" => "Konto gesperrt für ",
    "FEEDBACK_ACCOUNT_SUSPENSION_DELETION_STATUS" => "Der Sperr-/Löschstatus dieses Kontos wurde bearbeitet.",
    "FEEDBACK_ACCOUNT_CANT_DELETE_SUSPEND_OWN" => "Man kann das eigene Konto nicht löschen oder sperren.",
    "FEEDBACK_ACCOUNT_USER_SUCCESSFULLY_KICKED" => "Der ausgewählte Benutzer wurde erfolgreich aus dem System entfernt (Sitzung zurückgesetzt).",
    "FEEDBACK_PASSWORD_WRONG_3_TIMES" => "Es wurde bereits dreimal oder öfter ein falsches Passwort eingegeben. Bitte 30 Sekunden warten und erneut versuchen.",
    "FEEDBACK_ACCOUNT_NOT_ACTIVATED_YET" => "Das Konto ist noch nicht aktiviert. Bitte auf den Bestätigungslink in der E-Mail klicken.",
    "FEEDBACK_USERNAME_OR_PASSWORD_WRONG" => "Der Benutzername oder das Passwort ist falsch. Bitte erneut versuchen.",
    "FEEDBACK_USER_DOES_NOT_EXIST" => "Dieser Benutzer existiert nicht.",
    "FEEDBACK_LOGIN_FAILED" => "Anmeldung fehlgeschlagen.",
    "FEEDBACK_LOGIN_FAILED_3_TIMES" => "Die Anmeldung ist bereits dreimal oder öfter fehlgeschlagen. Bitte 30 Sekunden warten und erneut versuchen.",
    "FEEDBACK_USERNAME_FIELD_EMPTY" => "Das Feld für den Benutzernamen war leer.",
    "FEEDBACK_PASSWORD_FIELD_EMPTY" => "Das Passwortfeld war leer.",
    "FEEDBACK_USERNAME_OR_PASSWORD_FIELD_EMPTY" => "Benutzername- oder Passwortfeld war leer.",
    "FEEDBACK_USERNAME_EMAIL_FIELD_EMPTY" => "Benutzername-/E-Mail-Feld war leer.",
    "FEEDBACK_EMAIL_FIELD_EMPTY" => "Das E-Mail-Feld war leer.",
    "FEEDBACK_EMAIL_REPEAT_WRONG" => "E-Mail und E-Mail-Wiederholung stimmen nicht überein.",
    "FEEDBACK_EMAIL_AND_PASSWORD_FIELDS_EMPTY" => "E-Mail- und Passwortfelder waren leer.",
    "FEEDBACK_USERNAME_SAME_AS_OLD_ONE" => "Dieser Benutzername ist identisch mit dem aktuellen. Bitte einen anderen auswählen.",
    "FEEDBACK_USERNAME_ALREADY_TAKEN" => "Dieser Benutzername ist bereits vergeben. Bitte einen anderen auswählen.",
    "FEEDBACK_USER_EMAIL_ALREADY_TAKEN" => "Diese E-Mail-Adresse wird bereits verwendet. Bitte eine andere auswählen.",
    "FEEDBACK_USERNAME_CHANGE_SUCCESSFUL" => "Der Benutzername wurde erfolgreich geändert.",
    "FEEDBACK_USERNAME_AND_PASSWORD_FIELD_EMPTY" => "Benutzername- und Passwortfelder waren leer.",
    "FEEDBACK_USERNAME_DOES_NOT_FIT_PATTERN" => "Der Benutzername passt nicht zum Muster: Nur Buchstaben (a-z) und Zahlen, 2 bis 64 Zeichen.",
    "FEEDBACK_EMAIL_DOES_NOT_FIT_PATTERN" => "Die angegebene E-Mail-Adresse entspricht nicht dem erforderlichen Format.",
    "FEEDBACK_EMAIL_SAME_AS_OLD_ONE" => "Diese E-Mail-Adresse ist identisch mit der aktuellen. Bitte eine andere auswählen.",
    "FEEDBACK_EMAIL_CHANGE_SUCCESSFUL" => "Die E-Mail-Adresse wurde erfolgreich geändert.",
    "FEEDBACK_EMAIL_ALREADY_TAKEN" => "Diese E-Mail-Adresse wird bereits von einem anderen Benutzer verwendet. Bitte eine andere auswählen.",
    "FEEDBACK_CAPTCHA_WRONG" => "Der eingegebene Captcha-Code war falsch.",
    "FEEDBACK_PASSWORD_REPEAT_WRONG" => "Passwort und Passwortwiederholung stimmen nicht überein.",
    "FEEDBACK_PASSWORD_TOO_SHORT" => "Das Passwort muss mindestens 6 Zeichen lang sein.",
    "FEEDBACK_USERNAME_TOO_SHORT_OR_TOO_LONG" => "Der Benutzername darf nicht kürzer als 2 und nicht länger als 64 Zeichen sein.",
    "FEEDBACK_ACCOUNT_SUCCESSFULLY_CREATED" => "Das Konto wurde erfolgreich erstellt und eine E-Mail wurde verschickt. Bitte den Bestätigungslink in dieser E-Mail nutzen.",
    "FEEDBACK_VERIFICATION_MAIL_SENDING_FAILED" => "Es konnte keine Bestätigungs-E-Mail gesendet werden. Das Konto wurde NICHT erstellt.",
    "FEEDBACK_ACCOUNT_CREATION_FAILED" => "Die Registrierung ist fehlgeschlagen. Bitte noch einmal versuchen.",
    "FEEDBACK_VERIFICATION_MAIL_SENDING_ERROR" => "Die Bestätigungs-E-Mail konnte nicht gesendet werden aufgrund von: ",
    "FEEDBACK_VERIFICATION_MAIL_SENDING_SUCCESSFUL" => "Eine Bestätigungs-E-Mail wurde erfolgreich gesendet.",
    "FEEDBACK_VERIFICATION_FAILED" => "Die Verifizierung ist fehlgeschlagen oder der Link ist ungültig!",
    "FEEDBACK_ACCOUNT_ACTIVATION_SUCCESSFUL" => "Die Aktivierung war erfolgreich! Nun kann man sich anmelden.",
    "FEEDBACK_ACCOUNT_ACTIVATION_FAILED" => "Keine passende ID-/Verifizierungscode-Kombination gefunden! Möglicherweise wurde der Link bereits aufgerufen. Bitte versuchen, sich auf der Hauptseite anzumelden.",
    "FEEDBACK_AVATAR_UPLOAD_SUCCESSFUL" => "Das Hochladen des Avatars war erfolgreich.",
    "FEEDBACK_AVATAR_UPLOAD_WRONG_TYPE" => "Es werden nur JPEG- und PNG-Dateien unterstützt.",
    "FEEDBACK_AVATAR_UPLOAD_TOO_SMALL" => "Die Avatar-Datei ist zu klein (mindestens 100x100 Pixel).",
    "FEEDBACK_AVATAR_UPLOAD_TOO_BIG" => "Die Avatar-Datei ist zu groß. Maximal 5 MB erlaubt.",
    "FEEDBACK_AVATAR_FOLDER_DOES_NOT_EXIST_OR_NOT_WRITABLE" => "Der Avatar-Ordner existiert nicht oder es fehlen Schreibrechte (chmod 775 oder 777).",
    "FEEDBACK_AVATAR_IMAGE_UPLOAD_FAILED" => "Es ist ein Fehler beim Hochladen des Bildes aufgetreten.",
    "FEEDBACK_AVATAR_IMAGE_DELETE_SUCCESSFUL" => "Der Avatar wurde erfolgreich gelöscht.",
    "FEEDBACK_AVATAR_IMAGE_DELETE_NO_FILE" => "Kein benutzerdefinierter Avatar vorhanden.",
    "FEEDBACK_AVATAR_IMAGE_DELETE_FAILED" => "Beim Löschen des Avatars ist ein Fehler aufgetreten.",
    "FEEDBACK_PASSWORD_RESET_TOKEN_FAIL" => "Das Token konnte nicht in der Datenbank gespeichert werden.",
    "FEEDBACK_PASSWORD_RESET_TOKEN_MISSING" => "Es wurde kein Token zum Zurücksetzen des Passworts übermittelt.",
    "FEEDBACK_PASSWORD_RESET_MAIL_SENDING_ERROR" => "Die E-Mail zum Zurücksetzen des Passworts konnte nicht gesendet werden aufgrund von: ",
    "FEEDBACK_PASSWORD_RESET_MAIL_SENDING_SUCCESSFUL" => "Eine E-Mail zum Zurücksetzen des Passworts wurde erfolgreich gesendet.",
    "FEEDBACK_PASSWORD_RESET_LINK_EXPIRED" => "Der Link zum Zurücksetzen ist abgelaufen. Er ist nur eine Stunde gültig.",
    "FEEDBACK_PASSWORD_RESET_COMBINATION_DOES_NOT_EXIST" => "Die Kombination aus Benutzername und Verifizierungscode existiert nicht.",
    "FEEDBACK_PASSWORD_RESET_LINK_VALID" => "Der Link zum Zurücksetzen ist gültig. Jetzt das Passwort ändern.",
    "FEEDBACK_PASSWORD_CHANGE_SUCCESSFUL" => "Das Passwort wurde erfolgreich geändert.",
    "FEEDBACK_PASSWORD_CHANGE_FAILED" => "Die Passwortänderung ist fehlgeschlagen.",
    "FEEDBACK_PASSWORD_NEW_SAME_AS_CURRENT" => "Das neue Passwort ist identisch mit dem aktuellen.",
    "FEEDBACK_PASSWORD_CURRENT_INCORRECT" => "Das aktuelle Passwort wurde falsch eingegeben.",
    "FEEDBACK_ACCOUNT_TYPE_CHANGE_SUCCESSFUL" => "Der Kontotyp wurde erfolgreich geändert.",
    "FEEDBACK_ACCOUNT_TYPE_CHANGE_FAILED" => "Das Ändern des Kontotyps ist fehlgeschlagen.",
    "FEEDBACK_NOTE_CREATION_FAILED" => "Das Erstellen der Notiz ist fehlgeschlagen.",
    "FEEDBACK_NOTE_EDITING_FAILED" => "Das Bearbeiten der Notiz ist fehlgeschlagen.",
    "FEEDBACK_NOTE_DELETION_FAILED" => "Das Löschen der Notiz ist fehlgeschlagen.",
    "FEEDBACK_COOKIE_INVALID" => "Das Remember-Me-Cookie ist ungültig.",
    "FEEDBACK_COOKIE_LOGIN_SUCCESSFUL" => "Die Anmeldung über das Remember-Me-Cookie war erfolgreich.",
    "FEEDBACK_PASSWORD_TOO_WEAK" => "Das Passwort ist zu schwach. Bitte ein sichereres wählen.",
    "FEEDBACK_TWO_FACTOR_CODE_WRONG" => "Der Zwei-Faktor-Code war ungültig.",
    "FEEDBACK_LOGIN_ATTEMPT_NEW_DEVICE" => "Anmeldeversuch von einem unbekannten Gerät erkannt. Bitte überprüfen.",
    "FEEDBACK_PROFILE_UPDATE_SUCCESSFUL" => "Das Profil wurde erfolgreich aktualisiert.",
    "FEEDBACK_PROFILE_UPDATE_FAILED" => "Die Aktualisierung des Profils ist fehlgeschlagen.",
);
