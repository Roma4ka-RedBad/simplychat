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
        $companion = $conversation->get_companion($user)->user;
        foreach ($conversation->get_unreaded_actions($user) as $action) {
            $action->set_read_status();
        }

        $data = [
            'header' => [
                'user_id' => $companion->id,
                'login' => $companion->login,
                'online' => $companion->is_online(),
                'is_blocked' => $conversation->get_member($user)->is_block_conv
            ],
            'body' => []
        ];

        $block_status = false;
        foreach ($conversation->get_dialogue() as $action) {
            if ($action->deleted_for_all) continue;
            if ($action->only_for) {
                if (!$action->only_from_user($user)) continue;
            }
            if ($action->type === 'unblock_conversation') $block_status = false;
            if ($action->type === 'clear_conversation')  $data['body'] = [];

            if (!$block_status) {
                $data['body'][] = [
                    'action_id' => $action->id,
                    'conversation_id' => $conversation->id,
                    'type' => $action->type,
                    'content' => $action->content,
                    'is_mine' => $action->from_user($user),
                    'created_at' => $action->createdAt,
                    'user' => $action->user->login,
                    'style' => $action->get_style()
                ];
            }

            if ($action->type === 'block_conversation') $block_status = true;
        }
        echo create_response(true, $data);
    } else {
        echo create_response(false, reason: 'Conversation not found!');
    }
}