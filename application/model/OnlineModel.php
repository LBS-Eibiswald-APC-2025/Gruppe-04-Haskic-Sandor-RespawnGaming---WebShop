<?php

class OnlineModel
{
    public static function getCurrentUsers()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        // Zähle eindeutige IP-Adressen der letzten 5 Minuten
        $sql = "SELECT COUNT(DISTINCT user_ip) AS online_count FROM online_users WHERE last_active > NOW() - INTERVAL 5 MINUTE";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetch();
        return $result->online_count;
    }

    public static function trackUser(): void
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $user_ip = $_SERVER['REMOTE_ADDR'];

        // Nutzer-IP einfügen oder aktualisieren
        $sql = "INSERT INTO online_users (user_ip, ip_address, last_active) 
            VALUES (:user_ip, :ip_address, NOW())
            ON DUPLICATE KEY UPDATE last_active = NOW()";

        $query = $database->prepare($sql);
        $query->execute([
            ':user_ip' => $user_ip,
            ':ip_address' => $user_ip
        ]);
    }
}
