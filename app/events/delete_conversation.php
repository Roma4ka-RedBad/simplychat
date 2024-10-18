<?php
require __DIR__ . '/../models/user.php';
require __DIR__ . '/../models/conversation.php';
require __DIR__ . '/../models/member.php';
require __DIR__ . '/../models/action.php';
require __DIR__ . '/../utils.php';

if (isset($_COOKIE['simplychat-pd']) and isset($_POST['conversation_id'])) {
    /* @var User $user */
    $user = unserialize(decrypt($_COOKIE['simplychat-pd'], 'simplychat-redbad-2024'));
    $conversation = $user->get_conversation_by_id($_POST['conversation_id']);
    if ($conversation) {
        $member = $conversation->get_member($user);
        $member->set_delete_conversation_status();
        echo create_response(true, ['conversation_id' => $conversation->id]);
    } else {
        echo create_response(false, reason: 'Conversation not found!');
    }
}