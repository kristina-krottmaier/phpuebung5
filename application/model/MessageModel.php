<?php

/**
 * MessageModel
 */
class MessageModel
{
    /**
     * Get all messages for the current user
     * in controller= MessageController::list()
     */
    public static function getAllMessages()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $user_id = Session::get('user_id');

        if (empty($user_id)) {
            return [];
        }
        $sql = "SELECT message_id, sender_id, recipient_id, subject, body, created_at
                FROM messages
                WHERE sender_id = :user_id OR recipient_id = :user_id
                ORDER BY created_at ASC";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id]);

        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get conversation messages between current user and another user
     * in controller= MessageController::list($other_user_id)
     */
    public static function getConversation($other_user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $user_id = Session::get('user_id');
        
        if (empty($user_id) || empty($other_user_id)) {
            return [];
        }
        $sql = "SELECT message_id, sender_id, recipient_id, subject, body, created_at
                FROM messages
                WHERE (sender_id = :user_id AND recipient_id = :other_id)
                   OR (sender_id = :other_id AND recipient_id = :user_id)
                ORDER BY created_at ASC";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id, ':other_id' => $other_user_id]);

        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Save a new message
     * used to create/send a message
     * in controller=  MessageController::sendMessage()
     */
    public static function sendMessage($recipient_id, $subject, $body)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO messages (sender_id, recipient_id, subject, body, created_at)
                VALUES (:sender_id, :recipient_id, :subject, :body, NOW())";
        $query = $database->prepare($sql);
        $query->execute([
            ':sender_id' => Session::get('user_id'),
            ':recipient_id' => $recipient_id,
            // ensure subject is not NULL to avoid DB constraint issues
            ':subject' => $subject === null ? '' : $subject,
            ':body' => $body
        ]);
        if ($query->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Delete a message by its ID
     * in controller= MessageController::deleteMessage()
     * Not used currently
     */
    public static function deleteMessage($message_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "DELETE FROM messages
                WHERE message_id = :message_id AND (sender_id = :user_id OR recipient_id = :user_id)
                LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':message_id' => $message_id, ':user_id' => Session::get('user_id')]);
        if ($query->rowCount() == 1) {
            return true;
        }
    }

    /**
     * Format a timestamp into a human-readable string
     * in controller= MessageController::timestamp()
     */
    public static function formatTimestamp($created_at)
    {
        $dt = new DateTime($created_at);
        return $dt->format('H:i');
    }
}