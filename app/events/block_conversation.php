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
        if ($member) {
            $member->set_block_conversation_status();
            if ($member->is_block_conv) {
                $action = $conversation->add_action($user, "block_conversation", "You have blocked this chat!", $user);
            } else {
                $action = $conversation->add_action($user, "unblock_conversation", "You have unblocked this chat!", $user);
            }

            echo create_response(true, [
                'conversation_id' => $conversation->id,
                'is_blocked' => $member->is_block_conv,
                'action' => [
                    'action_id' => $action->id,
                    'conversation_id' => $conversation->id,
                    'type' => $action->type,
                    'content' => $action->content,
                    'created_at' => $action->createdAt,
                    'style' => $action->get_style()
                ]
            ]);
        } else {
            echo create_response(false, reason: 'Member not found!');
        }
    } else {
        echo create_response(false, reason: 'Conversation not found!');
    }
}