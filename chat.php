<!DOCTYPE html>
<html lang="en">

<head>
    <title>Simply Chat</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="images/favicon.ico">
    <link rel='stylesheet'
        href='https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/chat.css">
    <script src="https://kit.fontawesome.com/e0c365e60c.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="./scripts/chat.js"></script>
</head>

<body>
    <?php
    require './app/models/user.php';
    require './app/models/conversation.php';
    require './app/models/member.php';
    require './app/models/action.php';
    require './app/utils.php';

    if (!isset($_COOKIE['simplychat-pd'])) {
        header('Location: index.php');
    } else {
        /* @var User $user */
        $user = unserialize(decrypt($_COOKIE['simplychat-pd'], 'simplychat-redbad-2024'));
    }

    if (isset($_POST['exit'])) {
        setcookie('simplychat-pd', '', -1, '/');
        header('Location: index.php');
    }
    ?>
    <div id="toast-container">
        <template id="toast-template">
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                <div class="toast-header">
                    <span class="toast-circle"></span>
                    <strong class="mr-auto" id="header-text"></strong>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times</span>
                    </button>
                </div>
                <div class="toast-body" id="body-text"></div>
            </div>
        </template>
    </div>
    <div class="content">
        <div class="container p-0">
            <div class="card">
                <div class="row">
                    <div class="col-12 col-lg-5 col-xl-3 border-right">
                        <div class="px-4 d-none d-md-block">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <input type="text" class="form-control my-3" name="search" id="search-field" placeholder="Search...">
                                </div>
                            </div>
                        </div>
                        <div class="position-relative objects-list border-top" id="conversation-collection">
                            <template id="conversation-template">
                                <a href="#" class="list-group-item list-group-item-action border-0">
                                    <div class="d-flex align-items-start">
                                        <img src="images/logo.webp" class="rounded-circle mr-2" alt="" width="40" height="40">
                                        <div class="flex-grow-1 ml-3">
                                            <strong class="conversation-title"></strong>
                                            <div class="small" id="conv-online-status"><span class="fas online-status"></span> <span class="status-text"></span></div>
                                        </div>
                                    </div>
                                </a>
                            </template>
                        </div>
                        <div class="flex-grow-0 py-3 px-3 border-top">
                            <div class="d-flex align-items-start">
                                <img src="images/logo.webp" class="rounded-circle mr-2"
                                     alt="<?php echo $user->login ?>" width="40" height="40">
                                <div class="flex-grow-1">
                                    <strong><?php echo "@{$user->login}" ?></strong>
                                    <form method="post" class="float-right mt-2">
                                        <button type="submit" name="exit" class="btn btn-danger btn-sm">Quit</button>
                                    </form>
                                    <div class="small">You</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7 col-xl-9" id="chat-block">
                        <template id="chat-header-template">
                            <div class="py-2 px-4 border-bottom d-none d-lg-block">
                                <div class="d-flex align-items-center py-1">
                                    <div class="position-relative">
                                        <img src="images/logo.webp" class="rounded-circle mr-1" alt="" width="40" height="40">
                                    </div>
                                    <div class="flex-grow-1 pl-3">
                                        <strong id="chat-title"></strong>
                                        <div class="small" id="chat-online-status"><span class="fas fa-circle online-status"></span> <span class="status-text"></span></div>
                                    </div>
                                    <div>
                                        <button class="btn btn-warning btn-lg mr-1 px-2 fa-solid fa-eraser" id="clear-conv-btn"></button>
                                        <button class="btn btn-danger btn-lg mr-1 px-3 fa-solid fa-trash" id="delete-conv-btn"></button>
                                        <button class="btn btn-light border btn-lg px-3 fa-solid fa-ban" id="block-conv-btn"></button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template id="chat-body-template">
                            <div class="position-relative">
                                <div class="objects-list p-4" id="message-collection"></div>
                            </div>
                            <div class="flex-grow-0 py-3 px-4 border-top">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="content" id="content-field" placeholder="Write something...">
                                    <button class="btn btn-primary" id="send-message-btn">Send</button>
                                </div>
                            </div>
                        </template>
                        <template id="message-template">
                            <div class="chat-message pb-4">
                                <div class="d-flex flex-column align-items-center">
                                    <img src="images/logo.webp" class="rounded-circle mr-1" alt="" width="40" height="40">
                                    <div class="text-muted small text-nowrap mt-2" id="created-at"></div>
                                </div>
                                <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3 message-content">
                                    <div class="font-weight-bold mb-1" id="message-author"></div>
                                    <span id="message-content"></span>
                                </div>
                            </div>
                        </template>
                        <template id="event-template">
                            <div class="chat-message-center pb-4">
                                <div class="flex-shrink-1 rounded-pill py-2 px-3 mr-3" id="event-content"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>