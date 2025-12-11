<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Skincare Assistant';

// Get all questions organized by category
$skincare_questions = [];
$app_questions = [];

$result = mysqli_query($conn, "SELECT * FROM chatbot_qa ORDER BY order_num ASC");
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['category'] == 'skincare') {
        $skincare_questions[] = $row;
    } else {
        $app_questions[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="chatbot-container">
    <div class="chatbot-header">
        <h1>💬 Skincare Assistant</h1>
        <p>Get instant answers to your skincare and app questions</p>
    </div>
    
    <div class="chatbot-layout">
        <!-- Questions Sidebar -->
        <div class="questions-sidebar">
            <div class="sidebar-search">
                <input type="text" id="searchBox" placeholder="🔍 Search questions..." />
            </div>
            
            <div class="questions-category">
                <h3>💆‍♀️ Skincare Tips</h3>
                <div class="questions-list">
                    <?php foreach ($skincare_questions as $qa): ?>
                        <div class="question-item" data-id="<?php echo $qa['id']; ?>" data-question="<?php echo htmlspecialchars($qa['question']); ?>" data-answer="<?php echo htmlspecialchars($qa['answer']); ?>" data-category="skincare">
                            <?php echo htmlspecialchars($qa['question']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="questions-category">
                <h3>📱 App Usage</h3>
                <div class="questions-list">
                    <?php foreach ($app_questions as $qa): ?>
                        <div class="question-item" data-id="<?php echo $qa['id']; ?>" data-question="<?php echo htmlspecialchars($qa['question']); ?>" data-answer="<?php echo htmlspecialchars($qa['answer']); ?>" data-category="app-usage">
                            <?php echo htmlspecialchars($qa['question']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Chat Display -->
        <div class="chat-display">
            <div class="chat-welcome">
                <div class="welcome-icon">✨</div>
                <h2>Welcome to Your Skincare Assistant!</h2>
                <p>Click any question from the left to get instant answers about skincare tips or how to use LuminElle.</p>
            </div>
            
            <div id="chatMessages" class="chat-messages">
                <!-- Messages will be added here dynamically -->
            </div>
        </div>
    </div>
</div>

<style>
.chatbot-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 0;
}

.chatbot-header {
    text-align: center;
    margin-bottom: 2rem;
}

.chatbot-header h1 {
    color: var(--bright-pink);
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.chatbot-header p {
    color: var(--text-light);
    font-size: 1.1rem;
}

.chatbot-layout {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 2rem;
    height: 600px;
}

/* Questions Sidebar */
.questions-sidebar {
    background: var(--white);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 5px 20px var(--shadow);
    overflow-y: auto;
}

.sidebar-search {
    margin-bottom: 1.5rem;
}

.sidebar-search input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 10px;
    font-size: 0.95rem;
}

.sidebar-search input:focus {
    outline: none;
    border-color: var(--fuchsia);
}

.questions-category {
    margin-bottom: 2rem;
}

.questions-category h3 {
    color: var(--deep-pink);
    font-size: 1.1rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--baby-pink);
}

.questions-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.question-item {
    padding: 0.75rem 1rem;
    background: var(--light-pink);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9rem;
    color: var(--text-dark);
    border: 2px solid transparent;
}

.question-item:hover {
    background: var(--baby-pink);
    border-color: var(--fuchsia);
    transform: translateX(5px);
}

.question-item.hidden {
    display: none;
}

/* Chat Display */
.chat-display {
    background: var(--white);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 20px var(--shadow);
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.chat-welcome {
    text-align: center;
    padding: 4rem 2rem;
}

.welcome-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.chat-welcome h2 {
    color: var(--deep-pink);
    margin-bottom: 1rem;
}

.chat-welcome p {
    color: var(--text-light);
    font-size: 1.05rem;
}

.chat-messages {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.message {
    display: flex;
    gap: 1rem;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-question {
    justify-content: flex-end;
}

.message-answer {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 75%;
    padding: 1rem 1.5rem;
    border-radius: 15px;
    line-height: 1.6;
}

.message-question .message-bubble {
    background: var(--bright-pink);
    color: white;
    border-bottom-right-radius: 5px;
}

.message-answer .message-bubble {
    background: var(--light-pink);
    color: var(--text-dark);
    border-bottom-left-radius: 5px;
    border-left: 4px solid var(--fuchsia);
}

.message-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.message-question .message-icon {
    background: var(--baby-pink);
}

.message-answer .message-icon {
    background: var(--fuchsia);
    color: white;
}

/* Scrollbar Styling */
.questions-sidebar::-webkit-scrollbar,
.chat-display::-webkit-scrollbar {
    width: 8px;
}

.questions-sidebar::-webkit-scrollbar-track,
.chat-display::-webkit-scrollbar-track {
    background: var(--light-pink);
    border-radius: 10px;
}

.questions-sidebar::-webkit-scrollbar-thumb,
.chat-display::-webkit-scrollbar-thumb {
    background: var(--fuchsia);
    border-radius: 10px;
}

@media (max-width: 968px) {
    .chatbot-layout {
        grid-template-columns: 1fr;
        height: auto;
    }
    
    .questions-sidebar {
        max-height: 400px;
    }
    
    .chat-display {
        min-height: 400px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    const chatWelcome = document.querySelector('.chat-welcome');
    const questionItems = document.querySelectorAll('.question-item');
    const searchBox = document.getElementById('searchBox');
    
    // Handle question clicks
    questionItems.forEach(item => {
        item.addEventListener('click', function() {
            const question = this.dataset.question;
            const answer = this.dataset.answer;
            
            // Hide welcome message
            if (chatWelcome) {
                chatWelcome.style.display = 'none';
            }
            
            // Add question message
            addMessage(question, 'question');
            
            // Add answer message with slight delay
            setTimeout(() => {
                addMessage(answer, 'answer');
            }, 300);
            
            // Scroll to bottom
            setTimeout(() => {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }, 400);
        });
    });
    
    // Add message function
    function addMessage(text, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message message-${type}`;
        
        if (type === 'question') {
            messageDiv.innerHTML = `
                <div class="message-bubble">${text}</div>
                <div class="message-icon">👤</div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="message-icon">🤖</div>
                <div class="message-bubble">${text}</div>
            `;
        }
        
        chatMessages.appendChild(messageDiv);
    }
    
    // Search functionality
    searchBox.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        questionItems.forEach(item => {
            const questionText = item.dataset.question.toLowerCase();
            if (questionText.includes(searchTerm)) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>