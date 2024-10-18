<?php
require_once __DIR__ . '/../database.php';

class Member {
    public int $id;
    public Conversation $conversation;
    public User $user;
    public bool $is_block_conv;
    public bool $is_delete_conv;
    public int $joinedAt;
    public ?int $blockAt;
    
    function __construct(int $id, Conversation $conversation, User $user, bool $is_block_conv, bool $is_delete_conv, int $joinedAt, ?int $blockAt) {
        $this->id = $id;
        $this->conversation = $conversation;
        $this->user = $user;
        $this->is_block_conv = $is_block_conv;
        $this->is_delete_conv = $is_delete_conv;
        $this->joinedAt = $joinedAt;
        $this->blockAt = $blockAt;
    }

    public function set_block_conversation_status(): void
    {
        $connect = getConnection();
        $this->is_block_conv = !$this->is_block_conv;
        $connect->prepare('UPDATE conversation_members SET is_block_conv = NOT is_block_conv WHERE id = :member_id')->execute(['member_id' => $this->id]);
    }

    public function set_delete_conversation_status(): void
    {
        $connect = getConnection();
        $this->is_delete_conv = !$this->is_delete_conv;
        $connect->prepare('UPDATE conversation_members SET is_delete_conv = NOT is_delete_conv WHERE id = :member_id')->execute(['member_id' => $this->id]);
    }
}
