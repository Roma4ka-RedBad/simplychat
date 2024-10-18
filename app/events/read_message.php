<?php
require __DIR__ . '/../models/user.php';
require __DIR__ . '/../models/conversation.php';
require __DIR__ . '/../models/member.php';
require __DIR__ . '/../models/action.php';
require __DIR__ . '/../utils.php';

if (isset($_COOKIE['simplychat-pd']) and isset($_POST['conversation_id']) and isset($_POST['action_id'])) {
    /* @var User $user */
    $user = unserialize(decrypt($_COOKIE['simplychat-pd'], 'simplychat-redbad-2024'));
    $conversation = $user->get_conversation_by_id($_POST['conversation_id']);
    if ($conversation) {
        $member = $conversation->get_member($user);
        $action = Action::get_by_id($_POST['action_id']);
        if ($action->conversation->id == $conversation->id) {
            if (!$member->is_block_conv) {
                $action->set_read_status();
                echo create_response(true);
            } else {
                echo create_response(false, reason: 'Conversation is blocked!');
            }
        } else {
            echo create_response(false, reason: 'Action not found!');
        }
    } else {
        echo create_response(false, reason: 'Conversation not found!');
    }
}