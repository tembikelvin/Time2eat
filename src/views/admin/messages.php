<?php
$title = $title ?? 'Admin Messages - Time2Eat';
$currentPage = 'messages';
$user = $user ?? null;
$conversations = $conversations ?? [];
$stats = $stats ?? [];
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-4 tw-rounded-2xl tw-bg-gradient-to-r tw-from-purple-500 tw-to-purple-600 tw-mr-4 tw-shadow-lg">
                <i data-feather="message-square" class="tw-h-8 tw-w-8 tw-text-white"></i>
            </div>
        <div>
                <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900">Admin Messages</h1>
                <p class="tw-mt-1 tw-text-sm tw-text-gray-500 tw-flex tw-items-center">
                    <i data-feather="shield" class="tw-h-4 tw-w-4 tw-mr-1"></i>
                    Communicate with customers, vendors, and riders across the platform
                </p>
            </div>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <button type="button" onclick="composeMessage()" 
                    class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-border tw-border-transparent tw-rounded-xl tw-shadow-lg tw-text-sm tw-font-semibold tw-text-white tw-bg-gradient-to-r tw-from-purple-500 tw-to-purple-600 hover:tw-from-purple-600 hover:tw-to-purple-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 tw-ease-in-out tw-transform hover:tw-scale-105 active:tw-scale-95">
                <i data-feather="plus" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                New Message
            </button>
        </div>
    </div>
</div>

<!-- Messages Content -->
<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">
    <!-- Conversations List -->
    <div class="lg:tw-col-span-1">
        <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden">
            <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <h2 class="tw-text-lg tw-font-medium tw-text-gray-900">Conversations</h2>
                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-purple-100 tw-text-purple-800">
                        <?= count($conversations ?? []) ?> active
                    </span>
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="tw-border-b tw-border-gray-200">
                <nav class="tw-flex tw-space-x-8 tw-px-6" aria-label="Tabs">
                    <button onclick="filterConversations('all')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-font-medium tw-text-sm tw-border-purple-500 tw-text-purple-600" 
                            data-filter="all">
                        All
                    </button>
                    <button onclick="filterConversations('customers')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" 
                            data-filter="customers">
                        Customers
                    </button>
                    <button onclick="filterConversations('vendors')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" 
                            data-filter="vendors">
                        Vendors
                    </button>
                    <button onclick="filterConversations('riders')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" 
                            data-filter="riders">
                        Riders
                    </button>
                </nav>
            </div>
            
            <div class="tw-divide-y tw-divide-gray-200 tw-max-h-96 tw-overflow-y-auto" id="conversations-list">
                <?php if (!empty($conversations)): ?>
                    <?php foreach ($conversations as $conversation): ?>
                    <div class="conversation-item tw-p-4 hover:tw-bg-gray-50 tw-cursor-pointer <?= ($conversation['unread_count'] ?? 0) > 0 ? 'tw-bg-blue-50' : '' ?>" 
                         data-type="<?= e($conversation['other_party_role'] ?? 'general') ?>"
                         onclick="openConversation('<?= e($conversation['conversation_id'] ?? '') ?>')">
                        <div class="tw-flex tw-items-start tw-space-x-3">
                            <div class="tw-flex-shrink-0">
                                <div class="tw-h-10 tw-w-10 tw-rounded-full tw-flex tw-items-center tw-justify-center <?= getAvatarColor($conversation['other_party_role'] ?? 'general') ?>">
                                    <?php if ($conversation['other_party_role'] === 'customer'): ?>
                                        <i data-feather="user" class="tw-h-5 tw-w-5 tw-text-white"></i>
                                    <?php elseif ($conversation['other_party_role'] === 'vendor'): ?>
                                        <i data-feather="utensils" class="tw-h-5 tw-w-5 tw-text-white"></i>
                                    <?php elseif ($conversation['other_party_role'] === 'rider'): ?>
                                        <i data-feather="truck" class="tw-h-5 tw-w-5 tw-text-white"></i>
                                    <?php else: ?>
                                        <span class="tw-text-sm tw-font-medium tw-text-white">
                                            <?= strtoupper(substr($conversation['other_party_name'] ?? 'U', 0, 1)) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <div class="tw-flex tw-items-center tw-justify-between">
                                    <p class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">
                                        <?= e($conversation['other_party_name'] ?? 'Unknown') ?>
                                        <?php if (!empty($conversation['order_id'])): ?>
                                            <span class="tw-text-xs tw-text-gray-500">(Order #<?= e($conversation['order_id']) ?>)</span>
                                        <?php endif; ?>
                                    </p>
                                    <div class="tw-flex tw-items-center tw-space-x-2">
                                        <?php if (($conversation['unread_count'] ?? 0) > 0): ?>
                                            <span class="tw-inline-flex tw-items-center tw-justify-center tw-px-2 tw-py-1 tw-text-xs tw-font-bold tw-leading-none tw-text-white tw-bg-red-500 tw-rounded-full">
                                                <?= $conversation['unread_count'] ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="tw-text-xs tw-text-gray-500">
                                            <?= date('M j', strtotime($conversation['last_message_at'] ?? 'now')) ?>
                                        </span>
                                    </div>
                                </div>
                                <p class="tw-text-sm tw-text-gray-600 tw-truncate">
                                    <?= e($conversation['last_message'] ?? 'No messages yet') ?>
                                </p>
                                <div class="tw-flex tw-items-center tw-mt-1">
                                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-gray-100 tw-text-gray-800">
                                        <?= ucfirst($conversation['other_party_role'] ?? 'user') ?>
                                    </span>
                                    <?php if (!empty($conversation['restaurant_name'])): ?>
                                        <span class="tw-ml-2 tw-text-xs tw-text-gray-500">
                                            <?= e($conversation['restaurant_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="tw-p-8 tw-text-center">
                        <i data-feather="message-square" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                        <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">No conversations yet</h3>
                        <p class="tw-text-gray-500 tw-mb-4">Start a conversation with users on the platform</p>
                        <button onclick="composeMessage()" 
                                class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-lg tw-text-sm tw-font-medium tw-text-white tw-bg-purple-600 hover:tw-bg-purple-700">
                            <i data-feather="plus" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                            New Message
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="lg:tw-col-span-2">
        <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden tw-h-96">
            <div id="chat-area" class="tw-h-full tw-flex tw-flex-col">
                <!-- Default state when no conversation is selected -->
                <div id="no-conversation" class="tw-h-full tw-flex tw-items-center tw-justify-center">
                    <div class="tw-text-center">
                        <i data-feather="message-circle" class="tw-h-16 tw-w-16 tw-text-gray-400 tw-mx-auto tw-mb-4"></i>
                        <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">Select a conversation</h3>
                        <p class="tw-text-gray-500">Choose a conversation from the list to start messaging</p>
                    </div>
                </div>

                <!-- Active conversation -->
                <div id="active-conversation" class="tw-h-full tw-flex tw-flex-col tw-hidden">
                    <!-- Chat header -->
                    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200 tw-bg-gray-50">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div id="chat-avatar" class="tw-h-8 tw-w-8 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-purple-500">
                                    <i data-feather="user" class="tw-h-4 tw-w-4 tw-text-white"></i>
                                </div>
                                <div>
                                    <h3 id="chat-title" class="tw-text-sm tw-font-medium tw-text-gray-900">User Name</h3>
                                    <p id="chat-subtitle" class="tw-text-xs tw-text-gray-500">User Role</p>
                                </div>
                            </div>
                            <div class="tw-flex tw-items-center tw-space-x-2">
                                <button onclick="refreshConversation()" 
                                        class="tw-p-2 tw-text-gray-400 hover:tw-text-gray-600 tw-rounded-lg hover:tw-bg-gray-100">
                                    <i data-feather="refresh-cw" class="tw-h-4 tw-w-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Messages area -->
                    <div id="messages-container" class="tw-flex-1 tw-overflow-y-auto tw-p-4 tw-space-y-4">
                        <!-- Messages will be loaded here -->
                    </div>

                    <!-- Message input -->
                    <div class="tw-px-6 tw-py-4 tw-border-t tw-border-gray-200 tw-bg-gray-50">
                        <form id="message-form" class="tw-flex tw-space-x-3">
                            <input type="hidden" id="conversation-id" name="conversation_id">
                            <div class="tw-flex-1">
                                <textarea id="message-input" 
                                          name="message" 
                                          rows="2" 
                                          class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-placeholder-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-transparent" 
                                          placeholder="Type your message..."></textarea>
                            </div>
                            <button type="submit" 
                                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-lg tw-text-sm tw-font-medium tw-text-white tw-bg-purple-600 hover:tw-bg-purple-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-ring-offset-2">
                                <i data-feather="send" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Compose Message Modal -->
<div id="compose-modal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-96 tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Compose Message</h3>
                <button onclick="closeComposeModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            
            <form id="compose-form" class="tw-space-y-4">
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Recipient Type</label>
                    <select id="recipient-type" name="recipient_type" 
                            class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-transparent"
                            onchange="loadRecipients()">
                        <option value="">Select recipient type</option>
                        <option value="customer">Customer</option>
                        <option value="vendor">Vendor/Restaurant</option>
                        <option value="rider">Rider</option>
                    </select>
                </div>
                
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Recipient</label>
                    <select id="recipient-select" name="recipient_id" 
                            class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-transparent"
                            disabled>
                        <option value="">Select recipient</option>
                    </select>
                </div>
                
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Subject</label>
                    <input type="text" id="subject-input" name="subject" 
                           class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-transparent"
                           placeholder="Enter message subject">
                </div>
                
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Message</label>
                    <textarea id="compose-message" name="message" rows="4" 
                              class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-transparent"
                              placeholder="Enter your message"></textarea>
                </div>
                
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Related Order (Optional)</label>
                    <select id="order-select" name="order_id" 
                            class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-transparent">
                        <option value="">No specific order</option>
                    </select>
                </div>
                
                <div class="tw-flex tw-justify-end tw-space-x-3">
                    <button type="button" onclick="closeComposeModal()" 
                            class="tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="tw-px-4 tw-py-2 tw-bg-purple-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-purple-700">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentConversationId = null;
let conversations = <?= json_encode($conversations ?? []) ?>;

// Filter conversations by type
function filterConversations(type) {
    const items = document.querySelectorAll('.conversation-item');
    const buttons = document.querySelectorAll('.conversation-filter');
    
    // Update active button
    buttons.forEach(btn => {
        btn.classList.remove('tw-border-purple-500', 'tw-text-purple-600');
        btn.classList.add('tw-border-transparent', 'tw-text-gray-500');
    });
    
    const activeBtn = document.querySelector(`[data-filter="${type}"]`);
    if (activeBtn) {
        activeBtn.classList.remove('tw-border-transparent', 'tw-text-gray-500');
        activeBtn.classList.add('tw-border-purple-500', 'tw-text-purple-600');
    }
    
    // Filter items
    items.forEach(item => {
        if (type === 'all' || item.dataset.type === type) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Open conversation
function openConversation(conversationId) {
    currentConversationId = conversationId;
    
    // Update UI
    document.getElementById('no-conversation').classList.add('tw-hidden');
    document.getElementById('active-conversation').classList.remove('tw-hidden');
    
    // Load conversation
    loadConversation(conversationId);
}

// Load conversation messages
function loadConversation(conversationId) {
    fetch(`<?= url('/admin/messages') ?>/${conversationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayConversation(data.conversation);
            } else {
                showAlert('Error loading conversation', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading conversation', 'error');
        });
}

// Display conversation
function displayConversation(conversation) {
    // Update chat header
    document.getElementById('chat-title').textContent = conversation.other_party_name || 'Unknown';
    document.getElementById('chat-subtitle').textContent = conversation.other_party_role || 'User';
    document.getElementById('conversation-id').value = conversation.conversation_id;
    
    // Update avatar
    const avatar = document.getElementById('chat-avatar');
    avatar.className = `tw-h-8 tw-w-8 tw-rounded-full tw-flex tw-items-center tw-justify-center ${getAvatarColorClass(conversation.other_party_role)}`;
    
    const icon = avatar.querySelector('i');
    if (conversation.other_party_role === 'customer') {
        icon.setAttribute('data-feather', 'user');
    } else if (conversation.other_party_role === 'vendor') {
        icon.setAttribute('data-feather', 'utensils');
    } else if (conversation.other_party_role === 'rider') {
        icon.setAttribute('data-feather', 'truck');
    }
    feather.replace();
    
    // Display messages
    const container = document.getElementById('messages-container');
    container.innerHTML = '';
    
    if (conversation.messages && conversation.messages.length > 0) {
        conversation.messages.forEach(message => {
            const messageEl = createMessageElement(message);
            container.appendChild(messageEl);
        });
        
        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }
}

// Create message element
function createMessageElement(message) {
    const div = document.createElement('div');
    const isAdmin = message.sender_type === 'support';
    
    div.className = `tw-flex ${isAdmin ? 'tw-justify-end' : 'tw-justify-start'}`;
    
    div.innerHTML = `
        <div class="tw-max-w-xs lg:tw-max-w-md tw-px-4 tw-py-2 tw-rounded-lg ${isAdmin ? 'tw-bg-purple-500 tw-text-white' : 'tw-bg-gray-200 tw-text-gray-900'}">
            <p class="tw-text-sm">${escapeHtml(message.message)}</p>
            <p class="tw-text-xs tw-mt-1 ${isAdmin ? 'tw-text-purple-100' : 'tw-text-gray-500'}">
                ${formatTime(message.created_at)}
            </p>
        </div>
    `;
    
    return div;
}

// Send message
document.getElementById('message-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= url('/admin/messages/send') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear input
            document.getElementById('message-input').value = '';
            
            // Reload conversation
            if (currentConversationId) {
                loadConversation(currentConversationId);
            }
        } else {
            showAlert(data.message || 'Failed to send message', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error sending message', 'error');
    });
});

// Compose message modal
function composeMessage() {
    document.getElementById('compose-modal').classList.remove('tw-hidden');
    loadRecentOrders();
}

function closeComposeModal() {
    document.getElementById('compose-modal').classList.add('tw-hidden');
    document.getElementById('compose-form').reset();
    document.getElementById('recipient-select').disabled = true;
}

// Load recipients based on type
function loadRecipients() {
    const type = document.getElementById('recipient-type').value;
    const select = document.getElementById('recipient-select');
    
    if (!type) {
        select.disabled = true;
        select.innerHTML = '<option value="">Select recipient</option>';
        return;
    }
    
    select.disabled = true;
    select.innerHTML = '<option value="">Loading...</option>';
    
    let endpoint = '';
    switch (type) {
        case 'customer':
            endpoint = '<?= url('/admin/messages/customers') ?>';
            break;
        case 'vendor':
            endpoint = '<?= url('/admin/messages/vendors') ?>';
            break;
        case 'rider':
            endpoint = '<?= url('/admin/messages/riders') ?>';
            break;
    }
    
    fetch(endpoint)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                select.innerHTML = '<option value="">Select recipient</option>';
                
                const items = data[type + 's'] || [];
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.first_name} ${item.last_name} (${item.email})`;
                    if (item.restaurant_name) {
                        option.textContent += ` - ${item.restaurant_name}`;
                    }
                    select.appendChild(option);
                });
                
                select.disabled = false;
            } else {
                showAlert('Failed to load recipients', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading recipients', 'error');
        });
}

// Load recent orders
function loadRecentOrders() {
    fetch('<?= url('/admin/messages/orders') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('order-select');
                select.innerHTML = '<option value="">No specific order</option>';
                
                data.orders.forEach(order => {
                    const option = document.createElement('option');
                    option.value = order.id;
                    option.textContent = `Order #${order.order_number} - ${order.customer_name} (${order.restaurant_name})`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading orders:', error);
        });
}

// Send compose message
document.getElementById('compose-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= url('/admin/messages/compose') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Message sent successfully', 'success');
            closeComposeModal();
            
            // Reload conversations
            location.reload();
        } else {
            showAlert(data.message || 'Failed to send message', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error sending message', 'error');
    });
});

// Utility functions
function getAvatarColorClass(role) {
    switch (role) {
        case 'customer': return 'tw-bg-blue-500';
        case 'vendor': return 'tw-bg-green-500';
        case 'rider': return 'tw-bg-orange-500';
        default: return 'tw-bg-gray-500';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) { // Less than 1 minute
        return 'Just now';
    } else if (diff < 3600000) { // Less than 1 hour
        return Math.floor(diff / 60000) + 'm ago';
    } else if (diff < 86400000) { // Less than 1 day
        return Math.floor(diff / 3600000) + 'h ago';
    } else {
        return date.toLocaleDateString();
    }
}

function showAlert(message, type) {
    // Simple alert implementation - you can enhance this with a proper notification system
    alert(message);
}

function refreshConversation() {
    if (currentConversationId) {
        loadConversation(currentConversationId);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});
</script>

<?php
// Helper function for avatar colors
function getAvatarColor($type) {
    switch ($type) {
        case 'customer': return 'tw-bg-blue-500';
        case 'vendor': return 'tw-bg-green-500';
        case 'rider': return 'tw-bg-orange-500';
        default: return 'tw-bg-gray-500';
    }
}
?>

