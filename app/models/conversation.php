<?php
require_once __DIR__ . '/../database.php';

class Conversation
{
    public int $id;
    public int $createdAt;
    public int $maxMembers;
    public ?string $title;

    function __construct(int $id, int $createdAt, int $maxMembers, ?string $title)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->maxMembers = $maxMembers;
        $this->title = $title;
    }

    /* @return Member[] */
    public function get_members(): array
    {
        $connect = getConnection();
        $stmt = $connect->prepare('SELECT * FROM conversation_members WHERE conversation = :conversation_id');
        $stmt->execute(['conversation_id' => $this->id]);
        $conversation_members = $stmt->fetchAll(PDO::FETCH_NUM);
        $members = [];
        foreach ($conversation_members as $member) {
            $members[] = new Member((int)$member[0], $this, User::get_by_id($member[2]), (boolean)$member[3], (boolean)$member[4], strtotime($member[5]), $member[6] ? strtotime($member[6]) : null);
        }
        return $members;
    }

    /* @return Action[] */
    public function get_dialogue(): array
    {
        $connect = getConnection();
        $stmt = $connect->prepare('SELECT * FROM conversation_dialogue WHERE conversation = :conversation_id');
        $stmt->execute(['conversation_id' => $this->id]);
        $actions = $stmt->fetchAll(PDO::FETCH_NUM);
        $dialogue = [];
        foreach ($actions as $action) {
            $dialogue[] = new Action((int)$action[0], $this, User::get_by_id($action[2]), $action[3] ? User::get_by_id($action[3]) : null, $action[4], $action[5], (boolean)$action[6], (boolean)$action[7], (boolean)$action[8], strtotime($action[9]));
        }
        return $dialogue;
    }

    /* @return Action[] */
    public function get_unreaded_actions_by_user_id(int $user_id): array
    {
        $actions = [];
        $block_status = false;
        foreach ($this->get_dialogue() as $action) {
            if ($action->type === 'unblock_conversation') $block_status = false;
            if (!$action->from_user_id($user_id) and !$action->readed and !$block_status) {
                if ($action->only_for) {
                    if (!$action->only_from_user_id($user_id)) continue;
                }
                $actions[] = $action;
            }
            if ($action->type === 'block_conversation') $block_status = true;
        }
        return $actions;
    }

    /* @return Action[] */
    public function get_unreaded_actions(User $user): array
    {
        return $this->get_unreaded_actions_by_user_id($user->id);
    }

    public function get_member_by_user_id(int $user_id): Member
    {
        $filteredMembers = array_filter($this->get_members(), function ($member) use ($user_id) {
            return $member->user->id == $user_id;
        });
        return reset($filteredMembers);
    }

    public function get_member(User $user): Member
    {
        return $this->get_member_by_user_id($user->id);
    }

    public function get_companion_by_user_id(int $user_id): ?Member
    {
        if ($this->is_private()) {
            $filteredMembers = array_filter($this->get_members(), function ($member) use ($user_id) {
                return $member->user->id != $user_id;
            });
            return reset($filteredMembers);
        }
        return null;
    }

    public function get_companion(User $user): ?Member
    {
        return $this->get_companion_by_user_id($user->id);
    }

    public function is_private(): bool
    {
        return $this->maxMembers == 2;
    }

    public function add_user_by_id(int $user_id): Member
    {
        $connect = getConnection();
        $connect->prepare('INSERT INTO conversation_members (conversation, user) VALUES (:conversation_id, :user_id)')->execute(['conversation_id' => $this->id, 'user_id' => $user_id]);
        return $this->get_member_by_user_id($user_id);
    }

    public function add_user(User $user): Member
    {
        return $this->add_user_by_id($user->id);
    }

    public function add_action_by_user_id(int $user_id, string $type, ?string $content = null, ?int $only_for_id = null): Action
    {
        $connect = getConnection();
        $connect->prepare('INSERT INTO conversation_dialogue (conversation, user, only_for, type, content) VALUES (:conversation_id, :user_id, :only_for, :type, :content)')->execute([
            'conversation_id' => $this->id, 'user_id' => $user_id, 'only_for' => $only_for_id, 'type' => $type, 'content' => $content
        ]);
        return Action::get_by_id($connect->lastInsertId());
    }

    public function add_action(User $user, string $type, ?string $content = null, ?User $only_for = null): Action
    {
        return $this->add_action_by_user_id($user->id, $type, $content, $only_for?->id);
    }

    public static function get_by_id(int $conversation_id): ?self
    {
        $connect = getConnection();
        $stmt = $connect->prepare('SELECT * FROM conversations WHERE id = :conversation_id');
        $stmt->execute(['conversation_id' => $conversation_id]);
        $conversation_data = $stmt->fetch(PDO::FETCH_NUM);
        if ($conversation_data) {
            return new Conversation((int)$conversation_data[0], strtotime($conversation_data[1]), (int)$conversation_data[2], $conversation_data[3]);
        }
        return null;
    }

    public static function create(): self
    {
        $connect = getConnection();
        $connect->query('INSERT INTO conversations () VALUES ();');
        return Conversation::get_by_id($connect->lastInsertId());
    }

    public static function exists_with_user_ids(int $user_id_1, int $user_id_2): ?self
    {
        $connect = getConnection();
        $stmt = $connect->prepare('SELECT cm1.conversation FROM conversation_members cm1 JOIN conversation_members cm2 ON cm1.conversation = cm2.conversation WHERE cm1.user = :user_id_1 AND cm2.user = :user_id_2');
        $stmt->execute(['user_id_1' => $user_id_1, 'user_id_2' => $user_id_2]);
        $conversation_data = $stmt->fetch(PDO::FETCH_NUM);
        if ($conversation_data) {
            return Conversation::get_by_id($conversation_data[0]);
        }
        return null;
    }

    public static function exists_with_users(User $user_1, User $user_2): ?self
    {
        return Conversation::exists_with_user_ids($user_1->id, $user_2->id);
    }
}