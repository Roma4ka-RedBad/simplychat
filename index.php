<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simply Chat - Login</title>
    <script src="./scripts/index.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
            integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
    <link rel="icon" href="images/favicon.ico">
    <link rel='stylesheet'
          href='https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
<div>
    <form class="screen-1" method="post" action="index.php">
        <div class="toast" id="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
            <div class="toast-header">
                <span class="toast-circle"></span>
                <strong class="me-auto" id="toast-header-text"></strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-body-text"></div>
        </div>
        <?php
        require './app/utils.php';

        if (isset($_COOKIE['simplychat-pd'])) {
            header('Location: chat.php');
        }

        if (!empty($_POST['password']) and !empty($_POST['login'])) {
            if (auth($_POST['login'], $_POST['password'])) {
                header('Location: chat.php');
            }
        } else {
            if (isset($_POST['login'])) {
                toast('Error', 'Fill in the blank fields!');
            }
        }
        ?>
        <div class="label">
            <h1>Enter the chat</h1>
        </div>
        <span class="logo">
                <img src="images/logo.webp" alt="logo">
            </span>
        <div class="login">
            <label for="login">Nickname</label>
            <div class="sec-2">
                <ion-icon name="at-outline"></ion-icon>
                <input id="login" type="text" name="login" placeholder="member" pattern="^[a-zA-Z][a-zA-Z0-9_]{4,31}$"
                       title="Username must start with a letter and contain between 5 and 32 characters, and may include numbers and underscores."/>
            </div>
        </div>
        <div class="password">
            <label for="password">Password</label>
            <div class="sec-2">
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input id="password" type="password" name="password" placeholder="········"
                       pattern="^[A-Za-z\d@$!%*?&]{8,}$"
                       title="Password must contain at least 8 characters and may include letters, numbers, and special characters (@$!%*?&)."/>
                <button type="button" class="show-hide" onclick="showhide()">
                    <ion-icon name="eye-outline"></ion-icon>
                </button>
            </div>
        </div>
        <button type="submit" class="login-btn">Login</button>
    </form>
</div>
</body>

</html>