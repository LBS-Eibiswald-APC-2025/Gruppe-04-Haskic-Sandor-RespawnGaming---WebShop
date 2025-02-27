<?php
// Feedback-Nachrichten aus der Session abrufen
$feedback_positive = Session::get('feedback_positive');
$feedback_negative = Session::get('feedback_negative');
$feedback_info = Session::get('feedback_info');

// Falls es positive oder negative Rückmeldungen gibt, wird ein JS-Block ausgegeben
if (isset($feedback_positive) || isset($feedback_negative) || isset($feedback_info)) {
    echo "<script>";

    // Positive Feedbacks via SweetAlert ausgeben
    if (isset($feedback_positive)) {
        foreach ($feedback_positive as $feedback) {
            // Sonderzeichen im Feedback absichern
            $safeFeedback = addslashes($feedback);
            // SweetAlert2-Alert mit Erfolgsmeldung anzeigen
            echo "swal({ title: 'Erfolg', text: '{$safeFeedback}', icon: 'success' });";
        }
        // Positive Feedbacks aus der Session entfernen
        Session::remove('feedback_positive');
    }

    // Negative Feedbacks via SweetAlert ausgeben
    if (isset($feedback_negative)) {
        foreach ($feedback_negative as $feedback) {
            $safeFeedback = addslashes($feedback);
            // SweetAlert2-Alert mit Fehlermeldung anzeigen
            echo "swal({ title: 'Fehler', text: '{$safeFeedback}', icon: 'error' });";
        }
        // Negative Feedbacks aus der Session entfernen
        Session::remove('feedback_negative');
    }

    // Information Feedbacks via SweetAlert ausgeben
    if (isset($feedback_info)) {
        foreach ($feedback_info as $feedback) {
            $safeFeedback = addslashes($feedback);
            // SweetAlert2-Alert mit Fehlermeldung anzeigen
            echo "swal({ title: 'Information', text: '{$safeFeedback}', icon: 'info' });";
        }
        // Negative Feedbacks aus der Session entfernen
        Session::remove('feedback_info');
    }

    echo "</script>";
}