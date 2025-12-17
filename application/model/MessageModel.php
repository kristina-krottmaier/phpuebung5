<?php

/**
 * MessageModel
 * Handles creation, retrieval and deletion of messages between users.
 */
class MessageModel
{
    /**
     * Get all messages for the current user (sent or received)
     * @return array an array of message objects
     */
    public static function getAllMessages()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $user_id = Session::get('user_id');
        if (empty($user_id)) {
            // no logged-in user, return empty result set
            return [];
        }

        $sql = "SELECT message_id, sender_id, recipient_id, subject, body, created_at
                FROM messages
                WHERE sender_id = :user_id OR recipient_id = :user_id
                ORDER BY created_at DESC";
        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id]);

        return $query->fetchAll(PDO::FETCH_OBJ);
    }

        /**
     * Get a single message
     * @param int $message_id id of the specific message
     * @return object a single object (the result)
     */
    public static function getMessage($message_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT message_id, sender_id, recipient_id, subject, body, created_at
                FROM messages
                WHERE message_id = :message_id AND (sender_id = :user_id OR recipient_id = :user_id)
                LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':message_id' => $message_id, ':user_id' => Session::get('user_id')]);

        return $query->fetch(PDO::FETCH_OBJ);
    }

    public static function saveMessage($recipient_id, $subject, $body)
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
    public static function createGroup($group_name, $member_ids)
    {


    }
}