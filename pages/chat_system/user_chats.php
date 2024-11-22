<?php
session_start();

// Check if the user is logged in and their role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Page title
$title = "Chat with Admins";

include '../../include/header.php';
include '../../include/sidebar.php';
include '../../include/topbar.php';

// Get or create the user's thread ID
function getUserThreadId($userId, $conn)
{
    try {
        $stmt = $conn->prepare("SELECT id FROM chat_threads WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $thread = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($thread) {
            return $thread['id'];
        } else {
            $stmt = $conn->prepare("INSERT INTO chat_threads (user_id) VALUES (:user_id)");
            $stmt->execute(['user_id' => $userId]);
            return $conn->lastInsertId();
        }
    } catch (PDOException $e) {
        error_log("Error fetching/creating thread: " . $e->getMessage());
        return null;
    }
}

$thread_id = getUserThreadId($_SESSION['user_id'], $conn);

?>

<div id="page-content-wrapper">
    <div class="container mt-4">
        <h1 class="mb-4">Chat with Admins</h1>
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="chat-messages" class="chat-messages">
                    <p>Loading chat...</p>
                </div>
                <form id="chat-form" class="mt-3">
                    <input type="hidden" id="thread_id" name="thread_id" value="<?php echo $thread_id; ?>">
                    <div class="input-group">
                        <textarea id="message" name="message" class="form-control" placeholder="Type your message..." rows="1"></textarea>
                        <button class="btn btn-primary" type="submit">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>

<script>
    const threadId = <?php echo $thread_id; ?>;

    function fetchMessages() {
        fetch(`../../../backend/chat_system/fetch_messages.php?thread_id=${threadId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const chatMessages = document.getElementById('chat-messages');
                    chatMessages.innerHTML = '';

                    data.messages.forEach(message => {
                        const messageElement = document.createElement('div');
                        messageElement.className = message.sender_id == <?php echo $_SESSION['user_id']; ?> ? 'message-sent' : 'message-received';
                        messageElement.textContent = `${message.name} (${message.role}): ${message.message}`;
                        chatMessages.appendChild(messageElement);
                    });

                    chatMessages.scrollTop = chatMessages.scrollHeight; // Auto-scroll to the bottom
                }
            });
    }

    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const message = document.getElementById('message').value;

        if (!message.trim()) return;

        fetch('../../../backend/chat_system/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `thread_id=${threadId}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('message').value = '';
                    fetchMessages();
                } else {
                    alert(data.message);
                }
            });
    });

    setInterval(fetchMessages, 2000);
    fetchMessages();
</script>

<style>
    .chat-messages {
        height: 400px;
        overflow-y: scroll;
        border: 1px solid #ddd;
        padding: 15px;
        background-color: #f9f9f9;
    }

    .message-received {
        background-color: #f1f1f1;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        text-align: left;
    }

    .message-sent {
        background-color: #007bff;
        color: #fff;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        text-align: right;
    }
</style>