<?php

/**
 * Class DatabaseFactory
 *
 * Beispiel für die Nutzung:
 * $database = DatabaseFactory::getFactory()->getConnection();
 *
 * Dieses Singleton-Pattern stellt sicher, dass pro Anfrage nur eine Datenbankverbindung
 * aufgebaut wird. Die Klasse basiert auf einem modifizierten Ansatz aus einem bekannten
 * StackOverflow-Beitrag, der die Flexibilität bei der Verwaltung von Datenbankverbindungen verbessert.
 */
class DatabaseFactory
{
    // Statische Variable, die die Singleton-Instanz der Factory speichert
    private static $factory;

    // Eigenschaft zur Speicherung der PDO-Datenbankverbindung
    private $database;

    /**
     * Gibt die Singleton-Instanz der DatabaseFactory zurück.
     * Falls noch keine Instanz existiert, wird eine neue erstellt.
     *
     * @return DatabaseFactory
     */
    public static function getFactory()
    {
        if (!self::$factory) {
            self::$factory = new DatabaseFactory();
        }
        return self::$factory;
    }

    /**
     * Gibt die bestehende Datenbankverbindung zurück oder baut eine neue auf.
     *
     * @return PDO Die Datenbankverbindung
     */
    public function getConnection(): PDO
    {
        if (!$this->database) {
            try {
                // PDO-Optionen festlegen: Standard-Fetch-Modus als Objekt und Fehlerbehandlung als Warning
                $options = array(
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
                );

                // DSN (Data Source Name) aus Konfigurationswerten zusammenbauen
                $dsn = Config::get('DB_TYPE') . ':host=' . Config::get('DB_HOST')
                    . ';dbname=' . Config::get('DB_NAME') . ';port=' . Config::get('DB_PORT')
                    . ';charset=' . Config::get('DB_CHARSET');

                // Neue PDO-Verbindung erstellen
                $this->database = new PDO(
                    $dsn,
                    Config::get('DB_USER'),
                    Config::get('DB_PASS'),
                    $options
                );
            } catch (PDOException $e) {
                // Bei einem Fehler wird eine benutzerfreundliche Nachricht ausgegeben, ohne sensible Daten preiszugeben
                echo 'Die Datenbankverbindung konnte nicht hergestellt werden. Bitte versuchen Sie es später erneut.' . '<br>';
                echo 'Fehlercode: ' . $e->getCode();
                exit; // Beendet die Ausführung, wenn keine Verbindung aufgebaut werden kann
            }
        }
        return $this->database;
    }
}
