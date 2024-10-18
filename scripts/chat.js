let CONVERSATION_TEMPLATE;
let CHAT_HEADER_TEMPLATE;
let CHAT_BODY_TEMPLATE;
let MESSAGE_TEMPLATE;
let EVENT_TEMPLATE;
let TOAST_TEMPLATE;

const NEW_CHAT_SOUND = new Audio('/sounds/new_conversation.ogg')
const NEW_MESSAGE_SOUND = new Audio('/sounds/new_message.ogg')
NEW_CHAT_SOUND.volume = 0.05
NEW_MESSAGE_SOUND.volume = 0.2

function is12HourFormat() {
    const formattedTime = new Intl.DateTimeFormat('default', {hour: 'numeric'}).format(new Date())
    return formattedTime.includes('AM') || formattedTime.includes('PM')
}

function getConversationID() {
    let hash = window.location.hash
    return hash ? parseInt(hash.substring(1)) : hash.substring(1)
}

function toast(headerText, bodyText, circleColor = '#fb5151') {
    let clone = TOAST_TEMPLATE.content.cloneNode(true)
    clone.querySelector('#header-text').textContent = headerText;
    clone.querySelector('#body-text').textContent = bodyText;
    clone.querySelector('.toast-circle').style.backgroundColor = circleColor;
    document.getElementById('toast-container').appendChild(clone)
    let toastElement = document.querySelector('#toast-container .toast:last-child')

    toastElement.querySelector('.close').addEventListener('click', () => {
        toastElement.classList.remove('show');
        setTimeout(() => {
            toastElement.remove();
        }, 500);
    });

    setTimeout(() => {
        toastElement.classList.add('show')
    }, 10)

    setTimeout(() => {
        toastElement.classList.remove('show')
        setTimeout(() => {
            toastElement.remove()
        }, 500)
    }, 5000)
}

function addObjectToChat(action) {
    let clone;
    if (action.type === "message") {
        clone = MESSAGE_TEMPLATE.content.cloneNode(true)
        let createdAt = new Date(action.created_at * 1000)
        clone.querySelector('.chat-message').classList.add('chat-message-' + (action.is_mine ? "right" : "left"))
        clone.querySelector('img').alt = action.action_id
        clone.querySelector('#created-at').textContent = createdAt.toLocaleTimeString("default", {
            hour: '2-digit',
            minute: '2-digit',
            day: 'numeric',
            month: 'short',
            hour12: is12HourFormat(),
            timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone
        })
        clone.querySelector('#message-author').textContent = action.user
        clone.querySelector('#message-content').textContent = action.content
    } else {
        clone = EVENT_TEMPLATE.content.cloneNode(true)
        clone.querySelector('#event-content').textContent = action.content
        clone.querySelector('#event-content').classList.add('bg-' + action.style)
    }
    let messageCollection = document.getElementById('message-collection')
    messageCollection.appendChild(clone)
    messageCollection.scrollTop = messageCollection.scrollHeight
}

function updateConversationsList(conversations) {
    let convCollection = document.getElementById('conversation-collection')
    let oldLength = convCollection.querySelectorAll('a').length

    convCollection.innerHTML = ''

    conversations.forEach(conversation => {
        let clone = CONVERSATION_TEMPLATE.content.cloneNode(true)
        if (conversation.unreaded > 0)
            clone.querySelector('a').insertAdjacentHTML('afterbegin', `<div class="badge bg-info mt-1">${conversation.unreaded}</div>`)
        clone.querySelector('a').href = `#${conversation.conversation_id}`
        clone.querySelector('img').alt = conversation.conversation_id
        clone.querySelector('.conversation-title').textContent = `${conversation.title}`
        if (conversation.is_private) {
            conversation.online = conversation.online ? 'online' : 'offline'
            clone.querySelector('.online-status').classList.add('fa-circle')
            clone.querySelector('.online-status').classList.add(`chat-${conversation.online}`)
            clone.querySelector('.status-text').textContent = conversation.online.charAt(0).toUpperCase() + conversation.online.slice(1)
        }
        convCollection.appendChild(clone)
        updateConversationChat(conversation)
    })

    let newLength = convCollection.querySelectorAll('a').length

    if (oldLength < newLength) {
        NEW_CHAT_SOUND.play()
    }
}

function createConversationChat(chat) {
    let conversationBlock = document.getElementById('chat-block')
    conversationBlock.textContent = ''
    conversationBlock.classList.remove('choose-conv')
    let clone = CHAT_HEADER_TEMPLATE.content.cloneNode(true)

    clone.querySelector('img').alt = chat.header.login;
    clone.querySelector('#chat-title').textContent = chat.header.login;
    chat.header.online = chat.header.online ? 'online' : 'offline'
    clone.querySelector('.online-status').classList.add(`chat-${chat.header.online}`);
    clone.querySelector('.status-text').textContent = chat.header.online.charAt(0).toUpperCase() + chat.header.online.slice(1);

    conversationBlock.appendChild(clone)
    conversationBlock.appendChild(CHAT_BODY_TEMPLATE.content.cloneNode(true))

    function submit() {
        let field = document.getElementById('content-field').value
        if (field) sendMessage(field, getConversationID())
    }

    document.getElementById('content-field').addEventListener('keydown', function (event) {
        if (event.key === 'Enter') submit()
    })
    document.getElementById('send-message-btn').addEventListener('click', submit)
    document.getElementById('clear-conv-btn').addEventListener('click', function ()  { clearConversationChat(getConversationID()) })
    document.getElementById('delete-conv-btn').addEventListener('click', function ()  { deleteConversation(getConversationID()) })
    document.getElementById('block-conv-btn').addEventListener('click', function ()  { blockConversation(getConversationID()) })

    document.getElementById("content-field").disabled = chat.header.is_blocked
    document.getElementById("send-message-btn").disabled = chat.header.is_blocked

    chat.body.forEach(action => {
        addObjectToChat(action)
    })
}

function updateConversationChat(chat) {
    if (getConversationID() === chat.conversation_id) {
        let statusBlock = document.querySelector(`a[href="#${getConversationID()}"]`).querySelector('#conv-online-status').cloneNode(true)
        statusBlock.id = 'chat-online-status'
        document.querySelector('#chat-online-status').replaceWith(statusBlock)
    }
}

function checkUpdates() {
    $.ajax({
        url: '/app/events/check_updates.php',
        method: 'POST',
        success: function (response) {
            if (response.status) {
                updateConversationsList(response.data.conversations)
                response.data.notifications.forEach(action => {
                    if (getConversationID() !== action.conversation_id && action.type === "message") {
                        NEW_MESSAGE_SOUND.play()
                        toast(`New message from ${action.title}`, action.content, '#3094EA')
                    }
                })
                response.data.actions.forEach(action => {
                    if (getConversationID() === action.conversation_id) {
                        addObjectToChat(action)
                        readMessage(action.action_id, action.conversation_id)
                    }
                })
            } else {
                toast("Error", response.reason)
            }
        },
    })
}

function searchConversation(login) {
    $.ajax({
        url: '/app/events/search_conversation.php',
        method: 'POST',
        data: {
            search: login,
        },
        success: function (response) {
            if (response.status) {
                window.location.hash = '#' + response.data.conversation_id
                document.getElementById('search-field').value = ''
            } else {
                toast('Error', response.reason)
            }
        },
    })
}

function sendMessage(content, conversation_id) {
    $.ajax({
        url: '/app/events/send_message.php',
        method: 'POST',
        data: {
            content: content,
            conversation_id: conversation_id
        },
        success: function (response) {
            if (response.status && getConversationID() === response.data.conversation_id) {
                addObjectToChat(response.data)
                document.getElementById('content-field').value = ''
            }
        },
    })
}

function blockConversation(conversation_id) {
    $.ajax({
        url: '/app/events/block_conversation.php',
        method: 'POST',
        data: {
            conversation_id: conversation_id
        },
        success: function (response) {
            if (response.status && getConversationID() === response.data.conversation_id) {
                document.getElementById("content-field").disabled = response.data.is_blocked
                document.getElementById("send-message-btn").disabled = response.data.is_blocked
                addObjectToChat(response.data.action)
            }
        },
    })
}

function deleteConversation(conversation_id) {
    $.ajax({
        url: '/app/events/delete_conversation.php',
        method: 'POST',
        data: {
            conversation_id: conversation_id
        },
        success: function (response) {
            if (getConversationID() === response.data.conversation_id) {
                window.location.hash = ""
                let conversationBlock = document.getElementById('chat-block')
                conversationBlock.innerHTML = ''
                conversationBlock.textContent = 'Select whom to write to...'
                conversationBlock.classList.add('choose-conv')
            }
        },
    })
}

function clearConversationChat(conversation_id) {
    $.ajax({
        url: '/app/events/clear_conversation_chat.php',
        method: 'POST',
        data: {
            conversation_id: conversation_id
        },
        success: function (response) {
            if (response.status && getConversationID() === response.data.conversation_id) {
                document.getElementById('message-collection').innerHTML = ''
                addObjectToChat(response.data)
            }
        },
    })
}

function readMessage(action_id, conversation_id) {
    $.ajax({
        url: '/app/events/read_message.php',
        method: 'POST',
        data: {
            action_id: action_id,
            conversation_id: conversation_id,
        }
    })
}

function getConversationChat(conversation_id) {
    $.ajax({
        url: '/app/events/get_conversation_chat.php',
        method: 'POST',
        data: {
            conversation_id: conversation_id,
        },
        success: function (response) {
            if (response.status) createConversationChat(response.data)
        },
    })
}

setInterval(checkUpdates, 1000)

window.addEventListener('hashchange', function () { getConversationChat(getConversationID()) })

document.addEventListener('DOMContentLoaded', function () {
    CONVERSATION_TEMPLATE = document.getElementById('conversation-template')
    CHAT_HEADER_TEMPLATE = document.getElementById('chat-header-template')
    CHAT_BODY_TEMPLATE = document.getElementById('chat-body-template')
    MESSAGE_TEMPLATE = document.getElementById('message-template')
    EVENT_TEMPLATE = document.getElementById('event-template')
    TOAST_TEMPLATE = document.getElementById('toast-template')

    let conversationBlock = document.getElementById('chat-block')
    if (!window.location.hash) {
        conversationBlock.textContent = 'Select whom to write to...'
        conversationBlock.classList.add('choose-conv')
    } else {
        getConversationChat(getConversationID())
    }

    document.getElementById('search-field').addEventListener('keydown', function (event) {
        let field = document.getElementById('search-field').value
        if (event.key === 'Enter' && field) searchConversation(field)
    })
})