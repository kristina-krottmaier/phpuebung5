<?php

/**
 * The Message controller
 */
class MessageController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();

        // VERY IMPORTANT: All controllers/areas that should only be usable by logged-in users
        // need this line! Otherwise not-logged in users could do actions. If all of your pages should only
        // be usable by logged-in users: Put this line into libs/Controller->__construct
        Auth::checkAuthentication();
    }

public function index()
{
    $this->View->render('message/index', [
        'users' => UserModel::getPublicProfilesOfAllUsers(),
        'current_user_id' => (int) Session::get('user_id'),
        'messages' => [],
    ]);
}
/**
 * Return JSON list of messages for current user (used by AJAX).
 */
public function list($other_user_id = null)
{
    $current_user_id = (int) Session::get('user_id');
    $messages = $other_user_id
        ? MessageModel::getConversation((int) $other_user_id)
        : MessageModel::getAllMessages();

    $out = [];
    foreach (($messages ?? []) as $m) {
        $sender_id = (int) $m->sender_id;

        $out[] = [
            'message_id' => (int) $m->message_id,
            'sender_id'  => $sender_id,
            'recipient_id' => isset($m->recipient_id) ? (int) $m->recipient_id : null,
            'subject'    => (string) $m->subject,
            'body'       => (string) $m->body,
            'created_at' => MessageModel::formatTimestamp($m->created_at),
            'is_mine'    => $sender_id === $current_user_id,
        ];
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($out);
}

/**
 * Create a new message (used by AJAX form in the view).
 * Expects POST 'message_text'. Saves a message where recipient = sender (self-message).
 * Adjust recipient/subject handling if you need real recipient logic.
 */
public function create()
{
    $text = trim(Request::post('message_text'));
    $recipientId = (int) Request::post('recipient_id');

    if ($text === '' || $text === null) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Empty message']);
        return;
    }

    if ($recipientId <= 0) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Invalid recipient']);
        return;
    }

    $saved = MessageModel::sendMessage($recipientId, null, $text);

    header('Content-Type: application/json; charset=utf-8');
    if ($saved) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false]);
    }
}

    public function sendMessage()
    {
        MessageModel::sendMessage(Request::post('recipient_id'), Request::post('subject'), Request::post('body'));
        Redirect::to('message');
    }

    public function delete($message_id)
    {
        MessageModel::deleteMessage($message_id);
        Redirect::to('message');
    }
    public function timestamp()
    {
        MessageModel::formatTimestamp(Request::post('timestamp'));
        $this->View->render('message');
    }
}