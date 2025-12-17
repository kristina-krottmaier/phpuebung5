<?php

/**
 * The note controller: Just an example of simple create, read, update and delete (CRUD) actions.
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
        $this->View->render('message/index', array(
            'messages' => MessageModel::getAllMessages()
        ));
    }


    public function showMessage()
    {
        MessageModel::getMessage(Request::post('message_id'));
        Redirect::to('message');
    }
/**
 * Return JSON list of messages for current user (used by AJAX).
 */
public function list()
{
    $messages = MessageModel::getAllMessages();

    $out = array();
    if (!empty($messages)) {
        foreach ($messages as $m) {
            $out[] = array(
                'message_id' => (int) $m->message_id,
                'body'       => (string) $m->body,
            );
        }
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

    if ($text === '' || $text === null) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('success' => false, 'error' => 'Empty message'));
        return;
    }

    // store as self message — change this if you have a recipient selection
    $recipientId = Session::get('user_id');
    $saved = MessageModel::saveMessage($recipientId, null, $text);

    header('Content-Type: application/json; charset=utf-8');
    if ($saved) {
        echo json_encode(array('success' => true));
    } else {
        http_response_code(500);
        echo json_encode(array('success' => false));
    }
}

    public function sendMessage()
    {
        MessageModel::saveMessage(Request::post('recipient_id'), Request::post('subject'), Request::post('body'));
        Redirect::to('message');
    }

    /**
     * This method controls what happens when you move to /message/edit(/XX) in your app.
     * Shows the current content of the message and an editing form.
     * @param $message_id int id of the message
     */
    public function edit($message_id)
    {
        $this->View->render('message/edit', array(
            'message' => MessageModel::getMessage($message_id)
        ));
    }
    public function delete($message_id)
    {
        MessageModel::deleteMessage($message_id);
        Redirect::to('message');
    }
}
