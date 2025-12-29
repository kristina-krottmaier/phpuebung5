<div class="container">
    <h1>Messages</h1>
    <?php $this->renderFeedbackMessages(); ?>
    <div class="box with-sidebar">

        <!-- Sidebar -->
        <div id="user-list">
            <h3>Users</h3>
            <div id="users">
                <?php if (!empty($this->users) && is_array($this->users)) : ?>
                    <?php foreach ($this->users as $u) : ?>
                        <button class="user-btn" type="button"
                                data-userid="<?= (int)$u->user_id; ?>">
                            <?= htmlentities($u->user_name, ENT_QUOTES, 'UTF-8'); ?>
                        </button>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div>No users</div>
                <?php endif; ?>
            </div>

            <div class="sidebar-actions">
                <button class="user-btn" id="btn-all" type="button">All messages</button>
            </div>
        </div>

        <!-- Main panel -->
        <div class="main-panel">
            <h3 id="chat-title">All Messages</h3>

            <div id="chat-window">
                <ul id="messages">

                    <li class="empty">Loading…</li>
                </ul>
            </div>

            <form id="chat-form" method="post" action="<?= htmlspecialchars(Config::get('URL') . 'message/create', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="text" id="message_text" name="message_text"
                       placeholder="Type a message..." autocomplete="off" />
                <input type="submit" value="Send" />
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    // IMPORTANT: Config::get('URL') already contains the correct base url, no 'index.php' here!
    const baseUrl = <?= json_encode(rtrim(Config::get('URL'), '/')); ?>;

    const messagesEl = document.getElementById('messages');
    const chatWindow = document.getElementById('chat-window');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message_text');
    const usersContainer = document.getElementById('users');
    const chatTitle = document.getElementById('chat-title');
    const btnAll = document.getElementById('btn-all');

    let selectedUserId = null;

    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/[&"'<>]/g, c => ({'&':'&amp;','"':'&quot;',"'":'&#39;','<':'&lt;','>':'&gt;'}[c]));
    }

    function setTitle() {
        if (!selectedUserId) {
            chatTitle.textContent = 'All Messages';
            return;
        }
        const btn = document.querySelector('.user-btn[data-userid="' + selectedUserId + '"]');
        chatTitle.textContent = btn ? ('Chat with ' + btn.textContent) : 'Chat';
    }

    function renderMessages(items) {
        if (!items || items.length === 0) {
            messagesEl.innerHTML = '<li class="empty">No messages yet.</li>';
            return;
        }

        messagesEl.innerHTML = items.map(item => {
            const label = item.created_at ? item.created_at : ('#' + item.message_id);
            const mine = item.is_mine === true;

            return (
                '<li data-id="' + esc(item.message_id) + '" class="' + (mine ? 'mine' : '') + '">' +
                    '<strong>' + esc(label) + '</strong>' +
                    '<span>' + esc(item.body) + '</span>' +
                '</li>'
            );
        }).join('');

    }

    async function fetchMessages() {
        try {
            const url = selectedUserId
                ? (baseUrl + '/message/list/' + selectedUserId)
                : (baseUrl + '/message/list');

            const res = await fetch(url, { cache: 'no-store', credentials: 'same-origin' });
            if (!res.ok) return;

            const data = await res.json();
            renderMessages(data || []);
            setTitle();
        } catch (err) {
            console.error('fetchMessages error', err);
        }
    }

    async function sendMessage(text) {
        try {
            const body = new URLSearchParams();
            body.append('message_text', text);
            body.append('recipient_id', selectedUserId);

            const res = await fetch(baseUrl + '/message/create', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            });

            if (!res.ok) {
                const errText = await res.text().catch(() => '');
                console.warn('sendMessage failed', res.status, errText);
            }

            await fetchMessages();
        } catch (err) {
            console.error('sendMessage error', err);
        }
    }

    function setActiveUserButton(userId) {
        document.querySelectorAll('.user-btn.active').forEach(b => b.classList.remove('active'));
        if (!userId) return;
        const btn = document.querySelector('.user-btn[data-userid="' + userId + '"]');
        if (btn) btn.classList.add('active');
    }

    // user click
    if (usersContainer) {
        usersContainer.addEventListener('click', function (ev) {
            const btn = ev.target.closest && ev.target.closest('.user-btn');
            if (!btn) return;

            selectedUserId = parseInt(btn.getAttribute('data-userid'), 10);
            setActiveUserButton(selectedUserId);
            fetchMessages();
        });
    }

    // all messages click
    if (btnAll) {
        btnAll.addEventListener('click', function () {
            selectedUserId = null;
            setActiveUserButton(null);
            fetchMessages();
        });
    }

    // form submit
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const text = (input.value || '').trim();
            if (!text) return;

            sendMessage(text);
            input.value = '';
            input.focus();
        });
    }

    // initial load + poll
    fetchMessages();
    setInterval(fetchMessages, 2000);
})();
</script>

<style>
.container { max-width:900px; margin:24px auto; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial; color:#222; }
.box { background:#fff; border-radius:10px; padding:18px; box-shadow:0 6px 18px rgba(23,24,26,0.06); }

.with-sidebar { display:flex; gap:16px; align-items:flex-start; }
#user-list { width:220px; }
.main-panel { flex:1; }

h1 { margin:0 0 8px; font-size:26px; color:#111; }
h3 { margin:14px 0 8px; font-size:16px; color:#444; font-weight:600; }

#users { display:flex; flex-direction:column; gap:8px; }
.user-btn { width:100%; text-align:left; padding:8px; border-radius:8px; border:1px solid #e6e9ee; background:#fff; cursor:pointer; }
.user-btn.active { background:#e8f0ff; border-color:#cfe0ff; }
.sidebar-actions { margin-top:10px; }

#chat-window { border-radius:8px; background:#fafafa; border:1px solid #e6e9ee; height:300px; overflow:auto; padding:12px; }

#messages { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:8px; }
#messages li { max-width: 50%;display:flex; align-items:flex-start; gap:8px; padding:10px; border-radius:8px; background:rgba(10,13,20,0.02); }
#messages li strong { color:#1a73e8; min-width:60px; font-weight:700; }
#messages li span { color:#222; word-break:break-word; line-height:1.35; }
#messages li.empty { text-align:center; color:#777; background:transparent; }

#messages li.mine { margin-left:auto; background:#eaf8ea; text-align:right; flex-direction:row-reverse; }
#messages li.mine strong { color:#0b6b2a; min-width:60px; }
#messages li.mine span { text-align:right; }

#chat-form { display:flex; gap:8px; margin-top:12px; align-items:center; }
#message_text { flex:1; padding:10px 12px; border-radius:8px; border:1px solid #dce3ee; background:#fff; outline:none; font-size:14px; }
#chat-form input[type="submit"] { background:#1a73e8; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; font-weight:600; }

.hint { margin-top:10px; font-size:12px; color:#666; }

@media (max-width:600px) {
    .container { padding:12px; }
    #chat-window { height:240px; }
    #messages li strong { min-width:120px; font-size:13px; }
}
</style>
