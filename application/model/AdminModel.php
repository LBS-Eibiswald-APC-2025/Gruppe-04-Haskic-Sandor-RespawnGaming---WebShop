<?php
/**
 * Handles all data manipulation of the admin part
 */
class AdminModel
{
    /**
     * Sets the deletion and suspension values
     *
     * @param int $deactivate Checkbox-Wert ("on" bedeutet Soft-Delete aktiv)
     * @param int $userId ID des zu bearbeitenden Nutzers
     * @return bool
     */
    public static function setAccountSuspensionAndDeletionStatus(int $deactivate, int $userId): bool
    {
        // Verhindern, dass der Admin sein eigenes Konto sperrt oder löscht
        if ($userId == Session::get('user_id')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CANT_DELETE_SUSPEND_OWN'));
            return false;
        }

        // Schreibe die Informationen in die Datenbank
        return self::writeDeleteAndSuspensionInfoToDatabase($userId, $deactivate);
    }

    /**
     * Schreibt die Lösch- und Sperrinformationen in die Datenbank
     *
     * @param int $userId
     * @param int $delete
     * @return bool
     */
    private static function writeDeleteAndSuspensionInfoToDatabase(int $userId, int $delete)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users 
            SET user_active = :u_a  
            WHERE user_id = :user_id LIMIT 1");
        $query->execute(array(
            ':u_a'                       => $delete,
            ':user_id'                   => $userId
        ));

        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_SUSPENSION_DELETION_STATUS'));
            return true;
        }
        return false;
    }

    /**
     * Setzt die Session des Nutzers zurück, sodass dieser sofort ausgeloggt wird.
     *
     * @param int $userId
     * @return bool
     */
    private static function resetUserSession($userId)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users 
            SET session_id = :session_id  
            WHERE user_id = :user_id LIMIT 1");
        $query->execute(array(
            ':session_id' => null,
            ':user_id'    => $userId
        ));

        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_USER_SUCCESSFULLY_KICKED'));
            return true;
        }
        return false;
    }
}
