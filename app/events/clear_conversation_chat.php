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
        $action = $conversation->add_action($user, "clear_conversation", "Chat cleared!", $user);

        echo create_response(true, [
            'action_id' => $action->id,
            'conversation_id' => $conversation->id,
            'type' => $action->type,
            'content' => $action->content,
            'created_at' => $action->createdAt,
            'style' => $action->get_style()
        ]);
    } else {
        echo create_response(false, reason: 'Conversation not found!');
    }
}
