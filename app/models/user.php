<?php
require_once __DIR__ . '/../database.php';

class User {
    public int $id;
    public string $login;
    public string $password;
    public ?string $vk_url;
    public int $lastActivity;

    function __construct(int $id, string $login, string $password, ?string $vk_url, int $lastActivity) {
        $this->id = $id;
        $this->login = $login;
        $this->password = $password;
        $this->vk_url = $vk_url;
        $this->lastActivity = $lastActivity;
    }

    public function is_online() : bool { 
        return (time() - $this->lastActivity) < 5; 
    }

    /* @return Conversation[] */
    public function get_conversations() : array {
        $connect = getConnection();
        $stmt = $connect->prepare('SELECT conversations.* FROM conversations JOIN conversation_members ON conversation_members.conversation = conversations.id WHERE conversation_members.user = :user_id');
        $stmt->execute(['user_id' => $this->id]);
        $user_conversations = $stmt->fetchAll(PDO::FETCH_NUM);
        $conversations = [];
        foreach ($user_conversations as $conversation) {
            $conversations[] = new Conversation((int)$conversation[0], strtotime($conversation[1]), (int)$conversation[2], $conversation[3]);
        }
        return $conversations;                                
    }

    public function get_conversation_by_id(int $conversation_id) : ?Conversation {
        $connect = getConnection();
        $stmt = $connect->prepare('SELECT conversations.* FROM conversations JOIN conversation_members ON conversation_members.conversation = conversations.id WHERE conversation_members.conversation = :conversation_id AND conversation_members.user = :user_id');
        $stmt->execute(['user_id' => $this->id, 'conversation_id' => $conversation_id]);
        $conversation_data = $stmt->fetch(PDO::FETCH_NUM);
        if ($conversation_data) {
            return new Conversation((int)$conversation_data[0], strtotime($conversation_data[1]), (int)$conversation_data[2], $conversation_data[3]);
        }
        return null;
    }

    public function update_last_activity() : void {
        $connect = getConnection();
        $this->lastActivity = time();
        $connect->prepare('UPDATE users SET lastActivity = CURRENT_TIMESTAMP WHERE id = :user_id')->execute(['user_id' => $this->id]);
    }

    public static function create(string $login, string $password) : self {
        $connect = getConnection();
        $connect->prepare('INSERT INTO users (login, password) VALUES (:login, :password)')->execute(['login' => $login, 'password' => $password]);
        return new User($connect->lastInsertId(), $login, $password, null, time());
    }

    public static function get_by_id(int $user_id) : ?self {
        $connect = getConnection();
        $stmt = $connect->prepare('SELECT * FROM users WHERE id = :user_id');
        $stmt->execute(['user_id' => $user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_NUM);
        if ($user_data) {
            return new User((int)$user_data[0], $user_data[1], $user_data[2], $user_data[3], strtotime($user_data[4]));
        }
        return null;
    }

    public static function get_by_login(string $login) : ?self {
        $connect = getConnection();
        $stmt = $connect->prepare('SELECT * FROM users WHERE login = :login');
        $stmt->execute(['login' => $login]);
        $user_data = $stmt->fetch(PDO::FETCH_NUM);
        if ($user_data) {
            return new User((int)$user_data[0], $user_data[1], $user_data[2], $user_data[3], strtotime($user_data[4]));
        }
        return null;
    }
}