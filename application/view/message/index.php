<div class="container">
    <h1>Messages</h1>
    <div class="box">

        <?php $this->renderFeedbackMessages(); ?>

        <h3>My Messages:</h3>
        <div id="chat-window" style="border:1px solid #ccc; height:300px; overflow:auto; padding:10px; background:#fafafa;">
            <ul id="messages" style="list-style:none; margin:0; padding:0;">
                <?php if (!empty($this->messages) && (is_array($this->messages) || $this->messages instanceof Traversable)) {
                    foreach($this->messages as $message) { ?>
                        <li data-id="<?= intval($message->message_id); ?>" style="margin-bottom:8px;">
                            <strong>#<?= intval($message->message_id); ?>:</strong>
                            <span><?= htmlentities($message->body, ENT_QUOTES, 'UTF-8'); ?></span>
                        </li>
                    <?php }
                } else { ?>
                    <li>No messages yet.</li>
                <?php } ?>
            </ul>
        </div>

        <form id="chat-form" method="post" action="<?= htmlspecialchars(Config::get('URL') . 'message/create', ENT_QUOTES, 'UTF-8'); ?>" style="margin-top:10px;">
            <input type="text" id="message_text" name="message_text" placeholder="Type a message..." autocomplete="off" style="width:80%;" />
            <input type="submit" value="Send" />
        </form>

    </div>
</div>

<script>
(function(){
// derive application base path from Config URL (root-relative), e.g. '/lbs/phpuebung5'
const basePath = '<?= rtrim(parse_url(Config::get("URL"), PHP_URL_PATH), "/"); ?>';
const messagesEl = document.getElementById('messages');
const chatWindow = document.getElementById('chat-window');
const form = document.getElementById('chat-form');
const input = document.getElementById('message_text');

    // render messages array (each item: {message_id, body})
    function renderMessages(items) {
        // keep track of existing ids to avoid flicker
        const existing = {};
        Array.from(messagesEl.children).forEach(li => {
            const id = li.getAttribute('data-id');
            if (id) existing[id] = true;
        });

        // clear and re-render (simple approach)
        messagesEl.innerHTML = '';
        if (!items || items.length === 0) {
            const li = document.createElement('li');
            li.textContent = 'No messages yet.';
            messagesEl.appendChild(li);
            return;
        }

        items.forEach(item => {
            const li = document.createElement('li');
            li.setAttribute('data-id', item.message_id);
            li.style.marginBottom = '8px';
            const strong = document.createElement('strong');
            strong.textContent = '#' + item.message_id + ': ';
            const span = document.createElement('span');
            span.textContent = item.body;
            li.appendChild(strong);
            li.appendChild(span);
            messagesEl.appendChild(li);
        });

        // scroll to bottom
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    // fetch messages (expects JSON array)
    async function fetchMessages() {
        try {
            const res = await fetch(basePath + '/message/list', { cache: 'no-store', credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();
            renderMessages(data);
        } catch (e) {
            // fail silently
            console.error('fetchMessages error', e);
        }
    }

    // send message
    async function sendMessage(text) {
        try {
            const body = new URLSearchParams();
            body.append('message_text', text);
            const res = await fetch(basePath + '/message/create', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            });
            if (res && res.ok) {
                // after sending, refresh messages
                await fetchMessages();
            } else {
                // try refreshing anyway
                await fetchMessages();
            }
        } catch (e) {
            console.error('sendMessage error', e);
        }
    }

    function showTimestamp() {
        const now = new Date();
        return now.getHours().toString().padStart(2, '0') + ':' +
               now.getMinutes().toString().padStart(2, '0') + ':' +
               now.getSeconds().toString().padStart(2, '0');
    }

    
    form.addEventListener('submit', function(e){
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        sendMessage(text);
        input.value = '';
        input.focus();
    });

    // load initially and poll
    fetchMessages();
    setInterval(fetchMessages, 2000);
})();
</script>