<div class="container">
    <h1>Admin/index</h1>

    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <h3>What happens here ?</h3>

        <div>
            This controller/action/view shows a list of all users in the system. with the ability to soft delete a user
            or suspend a user.
        </div>
        <div>
            <table class="overview-table">
                <thead>
                <tr>
                    <td>Id</td>
                    <td>Avatar</td>
                    <td>Username</td>
                    <td>User's email</td>
                    <td>Activated ?</td>
                    <td>Link to user's profile</td>
                    <td>suspension Time in days</td>
                    <td>Soft delete</td>
                    <td>Submit</td>
                </tr>
                </thead>
                <?php foreach ($this->users as $user) { ?>
                <tr class="<?= ($user->user_active == 0 ? 'inactive' : 'active'); ?>">
                    <form action="<?= Config::get("URL"); ?>admin/actionAccountSettings" method="post">
                        <td><?= $user->user_id; ?></td>
                        <td class="avatar">
                            <?php if (isset($user->user_avatar_link)) { ?>
                                <img src="<?= $user->user_avatar_link; ?>"/>
                            <?php } ?>
                        </td>
                        <td onclick="editCell(this)"
                            data-userid="<?= $user->user_id; ?>">
                            <?= htmlspecialchars($user->user_name, ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td><?= htmlspecialchars($user->user_email, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= ($user->user_active == 0 ? 'No' : 'Yes'); ?></td>
                        <td>
                            <a href="<?= Config::get('URL') . 'profile/showProfile/' . $user->user_id; ?>">Profile</a>
                        </td>
                        <td><input type="number" name="suspension" /></td>
                        <td>
                            <input type="checkbox" name="softDelete" <?php if ($user->user_deleted) { ?> checked <?php } ?> />
                        </td>
                        <td>
                            <input type="hidden" name="user_id" value="<?= $user->user_id; ?>" />
                            <input type="hidden" name="user_name"
                                id="user_name_<?= $user->user_id; ?>"
                                value="<?= htmlspecialchars($user->user_name, ENT_QUOTES, 'UTF-8'); ?>" />
                            <input type="submit" value="Save" />
                        </td>

                    </form>
                </tr>
                <?php } ?>
            </table>
            <script>
            function editCell(cell) {
                if (cell.querySelector('input')) return;

                const userId = cell.dataset.userid;
                const hidden = document.getElementById('user_name_' + userId);

                const oldValue = cell.innerText.trim();
                cell.innerHTML = `<input type="text" value="${oldValue.replace(/"/g, '&quot;')}" />`;

                const input = cell.querySelector('input');
                input.focus();
                input.select();

                input.addEventListener('blur', () => {
                    const newValue = input.value.trim();
                    cell.innerText = newValue;
                    hidden.value = newValue; // <-- THIS is the important part
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') input.blur();
                    if (e.key === 'Escape') {
                        cell.innerText = oldValue;
                        hidden.value = oldValue;
                    }
                });
            }
            </script>
        </div>
    </div>
</div>
