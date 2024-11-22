<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

$title = "Chat System";
include '../../include/header.php';
include '../../include/sidebar.php';
include '../../include/topbar.php';

// Fetch all user threads
try {
    $stmt = $conn->prepare("
        SELECT ct.id AS thread_id, u.name 
        FROM chat_threads ct 
        JOIN users u ON ct.user_id = u.id
    ");
    $stmt->execute();
    $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $threads = [];
    error_log("Error fetching chat threads: " . $e->getMessage());
}
?>

<div id="page-content-wrapper">
    <div class="container mt-4">
        <h1 class="mb-4 text-center">Chat System</h1>
        <div class="row">
            <div class="col-lg-3 col-md-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header text-center">User List</div>
                    <div class="card-body user-list">
                        <ul class="list-group">
                            <?php foreach ($threads as $thread) : ?>
                                <li class="list-group-item thread-item" data-thread-id="<?php echo $thread['thread_id']; ?>">
                                    <?php echo htmlspecialchars($thread['name']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-9 col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header text-center">Chat</div>
                    <div class="card-body">
                        <div id="chat-messages" class="chat-messages">
                            <p class="text-center">Select a user to start chatting.</p>
                        </div>
                        <form id="chat-form" class="mt-3">
                            <input type="hidden" id="thread_id" name="thread_id">
                            <div class="input-group">
                                <textarea id="message" name="message" class="form-control" placeholder="Type your message..." rows="1"></textarea>
                                <button class="btn btn-primary" type="submit">Send</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>

<script>
    let activeThreadId = null;

    function fetchMessages(threadId) {
        if (!threadId) return;

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

    document.querySelectorAll('.thread-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.thread-item').forEach(el => el.classList.remove('active'));
            this.classList.add('active');

            const threadId = this.getAttribute('data-thread-id');
            document.getElementById('thread_id').value = threadId;
            activeThreadId = threadId;
            fetchMessages(threadId);
        });
    });

    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const threadId = document.getElementById('thread_id').value;
        const message = document.getElementById('message').value;

        if (!threadId || !message.trim()) return;

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
                    fetchMessages(threadId);
                } else {
                    alert(data.message);
                }
            });
    });

    setInterval(() => {
        if (activeThreadId) fetchMessages(activeThreadId);
    }, 2000);
</script>

<style>
    .chat-messages {
        height: 400px;
        overflow-y: auto;
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

    .thread-item.active {
        background-color: #007bff;
        color: white;
    }

    @media (max-width: 768px) {
        .thread-item {
            font-size: 14px;
        }

        .message-received,
        .message-sent {
            font-size: 12px;
            padding: 8px;
        }
    }
</style>