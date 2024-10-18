<?php
require_once __DIR__ . '/../database.php';

class Action
{
    public int $id;
    public Conversation $conversation;
    public User $user;
    public ?User $only_for;
    public string $type;
    public ?string $content;
    public bool $readed;
    public bool $notified;
    public bool $deleted_for_all;
    public int $createdAt;

    public function __construct(int $id, Conversation $conversation, User $user, ?User $only_for, string $type, ?string $content, bool $readed, bool $notified, bool $deleted_for_all, int $createdAt)
    {
        $this->id = $id;
        $this->conversation = $conversation;
        $this->user = $user;
        $this->only_for = $only_for;
        $this->type = $type;
        $this->content = $content;
        $this->readed = $readed;
        $this->notified = $notified;
        $this->deleted_for_all = $deleted_for_all;
        $this->createdAt = $createdAt;
    }

    public function from_user_id(int $user_id): bool
    {
        return $user_id == $this->user->id;
    }

    public function from_user(User $user): bool
    {
        return $this->from_user_id($user->id);
    }

    public function only_from_user_id(int $user_id): bool
    {
        return $user_id == $this->only_for->id;
    }

    public function only_from_user(User $user): bool
    {
        return $this->only_from_user_id($user->id);
    }

    public function set_read_status(): void
    {
        $connect = getConnection();
        $this->readed = !$this->readed;
        $connect->prepare('UPDATE conversation_dialogue SET readed = NOT readed WHERE id = :action_id')->execute(['action_id' => $this->id]);
    }

    public function set_notification_status(): void
    {
        $connect = getConnection();
        $this->readed = !$this->notified;
        $connect->prepare('UPDATE conversation_dialogue SET notified = NOT notified WHERE id = :action_id')->execute(['action_id' => $this->id]);
    }

    public function get_style(): ?string
    {
        return match ($this->type) {
            'chat_created' => 'primary',
            'block_conversation' => 'danger',
            'unblock_conversation' => 'success',
            'clear_conversation' => 'warning',
            default => null
        };
    }

    public static function get_by_id(int $action_id): ?self
    {
        $connect = getConnection();
        $stmt = $connect->prepare('SELECT * FROM conversation_dialogue WHERE id = :action_id');
        $stmt->execute(['action_id' => $action_id]);
        $action_data = $stmt->fetch(PDO::FETCH_NUM);
        if ($action_data) {
            return new Action((int)$action_data[0], Conversation::get_by_id($action_data[1]), User::get_by_id($action_data[2]), $action_data[3] ? User::get_by_id($action_data[3]) : null, $action_data[4], $action_data[5], (bool)$action_data[6], (bool)$action_data[7], (bool)$action_data[8], strtotime($action_data[9]));
        }
        return null;
    }
}