<?php
require __DIR__ . '/../models/user.php';
require __DIR__ . '/../models/conversation.php';
require __DIR__ . '/../models/member.php';
require __DIR__ . '/../models/action.php';
require __DIR__ . '/../utils.php';

if (isset($_COOKIE['simplychat-pd'])) {
    /* @var User $user */
    $user = unserialize(decrypt($_COOKIE['simplychat-pd'], 'simplychat-redbad-2024'));
    $user->update_last_activity();
    $data = [
        'conversations' => [],
        'notifications' => [],
        'actions' => [],
    ];
    foreach ($user->get_conversations() as $conversation) {
        $unreaded_actions = $conversation->get_unreaded_actions($user);
        $member = $conversation->get_member($user);
        if (!$member->is_delete_conv) {
            $companion = $conversation->is_private() ? $conversation->get_companion($user) : $conversation->get_member($user);
            $data['conversations'][] = [
                'conversation_id' => $conversation->id,
                'is_private' => $conversation->is_private(),
                'title' => !$conversation->is_private() ? $conversation->title : $companion->user->login,
                'online' => !$companion->is_block_conv && $companion->user->is_online(),
                'unreaded' => count($unreaded_actions)
            ];
        }
        if (!$member->is_block_conv) {
            foreach ($unreaded_actions as $action) {
                if (!$action->notified) {
                    $data['actions'][] = [
                        'action_id' => $action->id,
                        'conversation_id' => $conversation->id,
                        'type' => $action->type,
                        'content' => $action->content,
                        'is_mine' => $action->from_user($user),
                        'created_at' => $action->createdAt,
                        'user' => $action->user->login,
                        'style' => $action->get_style()
                    ];
                    $data['notifications'][] = [
                        'conversation_id' => $conversation->id,
                        'is_private' => $conversation->is_private(),
                        'title' => !$conversation->is_private() ? $conversation->title : $action->user->login,
                        'type' => $action->type,
                        'content' => $action->content
                    ];
                    $action->set_notification_status();
                    if ($member->is_delete_conv) $member->set_delete_conversation_status();
                }
            }
        }
    }
    echo create_response(true, $data);
} else {
    echo create_response(false, reason: 'Authentication data has expired. Please reload the page!');
}
