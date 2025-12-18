<?php
$title = $title ?? 'Messages - Time2Eat';
$currentPage = $currentPage ?? 'messages';
$user = $user ?? null;
$messages = $messages ?? [];
$unreadCount = $unreadCount ?? 0;
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Messages</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Communicate with customers and support.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <button type="button" onclick="composeMessage()" 
                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700">
                <i data-feather="edit" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                New Message
            </button>
        </div>
    </div>
</div>

<!-- Messages Content -->
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">
            <!-- Message List -->
            <div class="lg:tw-col-span-1">
                <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden">
                    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <h2 class="tw-text-lg tw-font-medium tw-text-gray-900">Conversations</h2>
                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-orange-100 tw-text-orange-800">
                                <?= count($messages ?? []) ?> active
                            </span>
                        </div>
                    </div>
                    
                    <div class="tw-divide-y tw-divide-gray-200 tw-max-h-96 tw-overflow-y-auto">
                        <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $message): ?>
                            <div class="tw-p-4 hover:tw-bg-gray-50 tw-cursor-pointer <?= ($message['unread'] ?? false) ? 'tw-bg-blue-50' : '' ?>" 
                                 onclick="openConversation(<?= $message['message_id'] ?? $message['id'] ?? 0 ?>)">
                                <div class="tw-flex tw-items-start tw-space-x-3">
                                    <div class="tw-flex-shrink-0">
                                        <div class="tw-h-8 tw-w-8 tw-bg-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                            <span class="tw-text-xs tw-font-medium tw-text-gray-700">
                                                <?= strtoupper(substr($message['first_name'] ?? $message['sender_name'] ?? 'U', 0, 1)) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="tw-flex-1 tw-min-w-0">
                                        <div class="tw-flex tw-items-center tw-justify-between">
                                            <p class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">
                                                <?= e(($message['first_name'] ?? '') . ' ' . ($message['last_name'] ?? '') ?: ($message['sender_name'] ?? 'Unknown')) ?>
                                            </p>
                                            <p class="tw-text-xs tw-text-gray-500">
                                                <?= date('M j', strtotime($message['created_at'] ?? 'now')) ?>
                                            </p>
                                        </div>
                                        <p class="tw-text-sm tw-text-gray-600 tw-truncate">
                                            <?= e($message['message'] ?? $message['last_message'] ?? 'No message') ?>
                                        </p>
                                        <?php if ($message['unread'] ?? false): ?>
                                        <div class="tw-mt-1">
                                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                                                New
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div class="tw-p-8 tw-text-center">
                            <i data-feather="message-circle" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400 tw-mb-4"></i>
                            <p class="tw-text-gray-500">No messages yet</p>
                            <p class="tw-text-sm tw-text-gray-400">Customer messages will appear here</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Message Thread -->
            <div class="lg:tw-col-span-2">
                <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden tw-h-96">
                    <div id="conversationHeader" class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200 tw-hidden">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-h-8 tw-w-8 tw-bg-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                    <span id="conversationAvatar" class="tw-text-xs tw-font-medium tw-text-gray-700"></span>
                                </div>
                                <div>
                                    <h3 id="conversationName" class="tw-text-sm tw-font-medium tw-text-gray-900"></h3>
                                    <p id="conversationStatus" class="tw-text-xs tw-text-gray-500">Online</p>
                                </div>
                            </div>
                            <div class="tw-flex tw-space-x-2">
                                <button type="button" onclick="markAsResolved()" 
                                        class="tw-text-gray-400 hover:tw-text-gray-600">
                                    <i data-feather="check-circle" class="tw-h-5 tw-w-5"></i>
                                </button>
                                <button type="button" onclick="blockUser()" 
                                        class="tw-text-gray-400 hover:tw-text-red-600">
                                    <i data-feather="user-x" class="tw-h-5 tw-w-5"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="messageThread" class="tw-flex-1 tw-p-6 tw-overflow-y-auto tw-h-64">
                        <div class="tw-text-center tw-text-gray-500 tw-py-12">
                            <i data-feather="message-square" class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400 tw-mb-4"></i>
                            <p>Select a conversation to view messages</p>
                        </div>
                    </div>
                    
                    <div id="messageInput" class="tw-px-6 tw-py-4 tw-border-t tw-border-gray-200 tw-hidden">
                        <form id="messageForm" class="tw-flex tw-space-x-3">
                            <input type="hidden" id="conversationId" name="conversation_id">
                            <div class="tw-flex-1">
                                <input type="text" id="messageText" name="message" 
                                       class="tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-orange-500 focus:tw-border-orange-500"
                                       placeholder="Type your message..." required>
                            </div>
                            <button type="submit" 
                                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700">
                                <i data-feather="send" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="tw-mt-8 tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-blue-100">
                        <i data-feather="message-circle" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Unread Messages</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= $stats['unread'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-green-100">
                        <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Response Time</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= $stats['avgResponseTime'] ?? '5m' ?></p>
                    </div>
                </div>
            </div>
            
            <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-yellow-100">
                        <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
                    </div>
                    <div class="tw-ml-4">
                        <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Conversations</p>
                        <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= $stats['active'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
        </div>
</div>

<!-- Compose Message Modal -->
<div id="composeModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-5 tw-border tw-w-11/12 tw-max-w-md tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">New Message</h3>
                <button type="button" onclick="closeComposeModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            
            <form id="composeForm">
                    <!-- Tab Selection -->
                    <div class="tw-mb-4">
                        <div class="tw-border-b tw-border-gray-200">
                            <nav class="-tw-mb-px tw-flex tw-space-x-8">
                                <button type="button" id="customerTab" class="compose-tab tw-border-transparent tw-text-gray-500 tw-hover:text-gray-700 tw-hover:border-gray-300 tw-whitespace-nowrap tw-py-2 tw-px-1 tw-border-b-2 tw-font-medium tw-text-sm tw-active">
                                    Customer
                                </button>
                                <button type="button" id="orderTab" class="compose-tab tw-border-transparent tw-text-gray-500 tw-hover:text-gray-700 tw-hover:border-gray-300 tw-whitespace-nowrap tw-py-2 tw-px-1 tw-border-b-2 tw-font-medium tw-text-sm">
                                    Order
                                </button>
                                <button type="button" id="riderTab" class="compose-tab tw-border-transparent tw-text-gray-500 tw-hover:text-gray-700 tw-hover:border-gray-300 tw-whitespace-nowrap tw-py-2 tw-px-1 tw-border-b-2 tw-font-medium tw-text-sm">
                                    Rider
                                </button>
                                <button type="button" id="supportTab" class="compose-tab tw-border-transparent tw-text-gray-500 tw-hover:text-gray-700 tw-hover:border-gray-300 tw-whitespace-nowrap tw-py-2 tw-px-1 tw-border-b-2 tw-font-medium tw-text-sm">
                                    Support
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Customer Tab -->
                    <div id="customerTabContent" class="compose-tab-content">
                        <label for="customerRecipient" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Customer</label>
                        <select id="customerRecipient" name="recipient_id"
                                class="tw-mt-1 tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-orange-500 focus:tw-border-orange-500">
                            <option value="">Select customer</option>
                        </select>
                        <input type="hidden" name="recipient_type" value="customer">
                    </div>

                    <!-- Order Tab -->
                    <div id="orderTabContent" class="compose-tab-content tw-hidden">
                        <label for="orderRecipient" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Order</label>
                        <select id="orderRecipient" name="order_id"
                                class="tw-mt-1 tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-orange-500 focus:tw-border-orange-500">
                            <option value="">Select order</option>
                        </select>
                        <input type="hidden" name="recipient_type" value="order">
                    </div>

                    <!-- Rider Tab -->
                    <div id="riderTabContent" class="compose-tab-content tw-hidden">
                        <div class="tw-mb-4">
                            <label for="riderRecipient" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Rider</label>
                            <select id="riderRecipient" name="rider_id"
                                    class="tw-mt-1 tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-orange-500 focus:tw-border-orange-500">
                                <option value="">Select rider</option>
                            </select>
                        </div>
                        <div class="tw-mb-4">
                            <label for="riderOrderRecipient" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Order (Optional)</label>
                            <select id="riderOrderRecipient" name="order_id"
                                    class="tw-mt-1 tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-orange-500 focus:tw-border-orange-500">
                                <option value="">Select order (optional)</option>
                            </select>
                            <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Link message to a specific order for better context</p>
                        </div>
                        <input type="hidden" name="recipient_type" value="rider">
                    </div>

                    <!-- Support Tab -->
                    <div id="supportTabContent" class="compose-tab-content tw-hidden">
                        <div class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Support Team</div>
                        <p class="tw-text-sm tw-text-gray-500 tw-mt-1">Message will be sent to Time2Eat support team</p>
                        <input type="hidden" name="recipient_type" value="support">
                        <input type="hidden" name="recipient_id" value="0">
                    </div>

                    <div class="tw-mb-4">
                
                <div class="tw-mb-4">
                    <label for="subject" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Subject</label>
                    <input type="text" id="subject" name="subject" required
                           class="tw-mt-1 tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-orange-500 focus:tw-border-orange-500">
                </div>
                
                <div class="tw-mb-6">
                    <label for="composeMessage" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Message</label>
                    <textarea id="composeMessage" name="message" rows="4" required
                              class="tw-mt-1 tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-orange-500 focus:tw-border-orange-500"
                              placeholder="Type your message..."></textarea>
                </div>
                
                <div class="tw-flex tw-justify-end tw-space-x-3">
                    <button type="button" onclick="closeComposeModal()" 
                            class="tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-orange-600 hover:tw-bg-orange-700">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

let currentConversationId = null;
let customers = [];
let orders = [];
let riders = [];
let ordersWithRiders = [];

// Load data when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadCustomers();
    loadOrders();
    loadRiders();
    loadOrdersWithRiders();
    
    // Initialize tab functionality
    const tabs = document.querySelectorAll('.compose-tab');
    const tabContents = document.querySelectorAll('.compose-tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.id;
            const contentId = tabId.replace('Tab', 'TabContent');
            
            // Remove active class from all tabs
            tabs.forEach(t => {
                t.classList.remove('tw-border-orange-500', 'tw-text-orange-600');
                t.classList.add('tw-border-transparent', 'tw-text-gray-500');
            });
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('tw-hidden');
            });
            
            // Show selected tab content
            const activeContent = document.getElementById(contentId);
            if (activeContent) {
                activeContent.classList.remove('tw-hidden');
                // Add active class to tab
                this.classList.remove('tw-border-transparent', 'tw-text-gray-500');
                this.classList.add('tw-border-orange-500', 'tw-text-orange-600');
            }
        });
    });
    
    // Set first tab as active by default
    tabs[0].click();
});

// Load customers
function loadCustomers() {
    fetch('<?= url('/vendor/messages/customers') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                customers = data.customers;
                populateCustomerDropdown();
            }
        })
        .catch(error => {
            console.error('Error loading customers:', error);
        });
}

// Load orders
function loadOrders() {
    fetch('<?= url('/vendor/messages/orders') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                orders = data.orders;
                populateOrderDropdown();
            }
        })
        .catch(error => {
            console.error('Error loading orders:', error);
        });
}

// Load riders
function loadRiders() {
    fetch('<?= url('/vendor/messages/riders') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                riders = data.riders;
                populateRiderDropdown();
            }
        })
        .catch(error => {
            console.error('Error loading riders:', error);
        });
}

// Load orders with riders
function loadOrdersWithRiders() {
    fetch('<?= url('/vendor/messages/orders-with-riders') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ordersWithRiders = data.orders;
                populateRiderOrderDropdown();
            }
        })
        .catch(error => {
            console.error('Error loading orders with riders:', error);
        });
}

// Populate customer dropdown
function populateCustomerDropdown() {
    const select = document.getElementById('customerRecipient');
    select.innerHTML = '<option value="">Select customer</option>';
    
    customers.forEach(customer => {
        const option = document.createElement('option');
        option.value = customer.id;
        option.textContent = `${customer.first_name} ${customer.last_name} (${customer.email}) - ${customer.order_count} orders`;
        select.appendChild(option);
    });
}

// Populate order dropdown
function populateOrderDropdown() {
    const select = document.getElementById('orderRecipient');
    select.innerHTML = '<option value="">Select order</option>';
    
    orders.forEach(order => {
        const option = document.createElement('option');
        option.value = order.id;
        option.textContent = `Order #${order.order_number} - ${order.customer_name} (${order.status}) - ${new Date(order.created_at).toLocaleDateString()}`;
        select.appendChild(option);
    });
}

// Populate rider dropdown
function populateRiderDropdown() {
    const select = document.getElementById('riderRecipient');
    select.innerHTML = '<option value="">Select rider</option>';
    
    riders.forEach(rider => {
        const option = document.createElement('option');
        option.value = rider.id;
        option.textContent = `${rider.full_name} (${rider.delivery_count} deliveries) - ${new Date(rider.last_delivery_at).toLocaleDateString()}`;
        select.appendChild(option);
    });
}

// Populate rider order dropdown
function populateRiderOrderDropdown() {
    const select = document.getElementById('riderOrderRecipient');
    select.innerHTML = '<option value="">Select order (optional)</option>';
    
    ordersWithRiders.forEach(order => {
        const option = document.createElement('option');
        option.value = order.id;
        option.textContent = `Order #${order.order_number} - ${order.rider_name} (${order.status}) - ${new Date(order.created_at).toLocaleDateString()}`;
        select.appendChild(option);
    });
}

// Open conversation
function openConversation(conversationId) {
    currentConversationId = conversationId;
    
    fetch(`<?= url('/vendor/messages') ?>/${conversationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayConversation(data.conversation);
            } else {
                alert('Error: ' + (data.message || 'Failed to load conversation'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading the conversation');
        });
}

// Display conversation
function displayConversation(conversation) {
    // Show header and input
    document.getElementById('conversationHeader').classList.remove('tw-hidden');
    document.getElementById('messageInput').classList.remove('tw-hidden');
    
    // Update header
    document.getElementById('conversationAvatar').textContent = conversation.sender_name.charAt(0).toUpperCase();
    document.getElementById('conversationName').textContent = conversation.sender_name;
    document.getElementById('conversationId').value = conversation.id;
    
    // Display messages
    const messageThread = document.getElementById('messageThread');
    messageThread.innerHTML = '';
    
    conversation.messages.forEach(message => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `tw-mb-4 tw-flex ${message.sender_type === 'vendor' ? 'tw-justify-end' : 'tw-justify-start'}`;
        
        messageDiv.innerHTML = `
            <div class="tw-max-w-xs lg:tw-max-w-md tw-px-4 tw-py-2 tw-rounded-lg ${
                message.sender_type === 'vendor' 
                    ? 'tw-bg-orange-600 tw-text-white' 
                    : 'tw-bg-gray-200 tw-text-gray-900'
            }">
                <p class="tw-text-sm">${message.message}</p>
                <p class="tw-text-xs tw-mt-1 ${
                    message.sender_type === 'vendor' ? 'tw-text-orange-200' : 'tw-text-gray-500'
                }">${new Date(message.created_at).toLocaleTimeString()}</p>
            </div>
        `;
        
        messageThread.appendChild(messageDiv);
    });
    
    // Scroll to bottom
    messageThread.scrollTop = messageThread.scrollHeight;
}

// Send message
document.getElementById('messageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= url('/vendor/messages/send') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('messageText').value = '';
            openConversation(currentConversationId); // Refresh conversation
        } else {
            alert('Error: ' + (data.message || 'Failed to send message'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending the message');
    });
});

// Compose message modal
function composeMessage() {
    document.getElementById('composeModal').classList.remove('tw-hidden');
}

function closeComposeModal() {
    document.getElementById('composeModal').classList.add('tw-hidden');
    document.getElementById('composeForm').reset();
}

// Compose form submission
document.getElementById('composeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const recipientType = formData.get('recipient_type');
    
    // Determine the correct endpoint based on recipient type
    let endpoint = '<?= url('/vendor/messages/compose') ?>';
    if (recipientType === 'rider') {
        endpoint = '<?= url('/vendor/messages/compose-to-rider') ?>';
    }
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeComposeModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to send message'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending the message');
    });
});

// Mark as resolved
function markAsResolved() {
    if (currentConversationId && confirm('Mark this conversation as resolved?')) {
        fetch(`<?= url('/vendor/messages') ?>/${currentConversationId}/resolve`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to mark as resolved'));
            }
        });
    }
}

// Block user
function blockUser() {
    if (currentConversationId && confirm('Block this user? They will not be able to message you.')) {
        fetch(`<?= url('/vendor/messages') ?>/${currentConversationId}/block`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to block user'));
            }
        });
    }
}

// Close modal when clicking outside
document.getElementById('composeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeComposeModal();
    }
});

// Auto-refresh messages every 30 seconds
setInterval(() => {
    if (currentConversationId) {
        openConversation(currentConversationId);
    }
}, 30000);
</script>
