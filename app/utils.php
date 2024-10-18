<?php
require_once __DIR__ . '/models/user.php';

function auth(string $login, string $password) : bool
{
    $password = md5($password);
    $user = User::get_by_login($login);
    if ($user) {
        if ($user->password != $password) {
            toast('Error!', 'Incorrect password!');
            return false;
        }
        toast('Success!', 'You are authorized!', '#55ff5d');
    } else {
        $user = User::create($login, $password);
        toast('Success!', 'You are registered!', '#55ff5d');
    }
    setcookie('simplychat-pd', encrypt(serialize($user), 'simplychat-redbad-2024'), strtotime('+1 hours'), '/');
    return true;
}

function toast(string $header, string $body, string $color = '#fb5151') : void
{
    echo sprintf('<script>toast("%s", "%s", "%s")</script>', $header, $body, $color);
}

function create_response(bool $status, array $data = [], string|null $reason = null) : string
{
    header('Content-Type: application/json');
    return json_encode(value: [
        'status' => $status,
        'data' => $data,
        'reason' => $reason
    ]);
}

function decrypt(string $string, string $secret_key) : string
{
    $result = '';
    $string = base64_decode($string);
    for ($i = 0; $i < strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($secret_key, ($i % strlen($secret_key)) - 1, 1);
        $char = chr(ord($char) - ord($keychar));
        $result .= $char;
    }
    return $result;
}

function encrypt(string $string, string $secret_key) : string
{
    $result = '';
    for ($i = 0; $i < strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($secret_key, ($i % strlen($secret_key)) - 1, 1);
        $char = chr(ord($char) + ord($keychar));
        $result .= $char;
    }
    return base64_encode($result);
}