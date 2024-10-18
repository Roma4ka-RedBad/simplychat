<?php
require __DIR__ . '/../models/user.php';
require __DIR__ . '/../models/conversation.php';
require __DIR__ . '/../models/member.php';
require __DIR__ . '/../models/action.php';
require __DIR__ . '/../utils.php';

if (isset($_COOKIE['simplychat-pd']) and isset($_POST['search'])) {
    /* @var User $user */
    $user = unserialize(decrypt($_COOKIE['simplychat-pd'], 'simplychat-redbad-2024'));
    $finded_user = User::get_by_login(str_replace('@', '', $_POST['search']));
    if ($finded_user and $finded_user->id != $user->id) {
        $conversation = Conversation::exists_with_users($finded_user, $user);
        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->add_user($user);
            $conversation->add_user($finded_user);
            $conversation->add_action($user, 'chat_created', content: "chat created by $user->login");
        }
        echo create_response(true, ['conversation_id' => $conversation->id]);
    } else {
        echo create_response(false, reason: 'User not found!');
    }
}
