<?php
$title = $title ?? 'Messages - Time2Eat';
$currentPage = $currentPage ?? 'messages';
$user = $user ?? null;
$conversations = $conversations ?? [];
$stats = $stats ?? [];
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Messages</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Communicate with customers and restaurants during deliveries.
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <button type="button" onclick="composeMessage()" 
                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-blue-600 hover:tw-bg-blue-700">
                <i data-feather="edit" class="tw-h-4 tw-w-4 tw-mr-2"></i>
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
                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                        <?= count($conversations ?? []) ?> active
                    </span>
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="tw-border-b tw-border-gray-200">
                <nav class="tw-flex tw-space-x-8 tw-px-6" aria-label="Tabs">
                    <button onclick="filterConversations('all')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-font-medium tw-text-sm tw-border-blue-500 tw-text-blue-600" 
                            data-filter="all">
                        All
                    </button>
                    <button onclick="filterConversations('customers')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" 
                            data-filter="customers">
                        Customers
                    </button>
                    <button onclick="filterConversations('restaurants')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" 
                            data-filter="restaurants">
                        Restaurants
                    </button>
                    <button onclick="filterConversations('admin')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" 
                            data-filter="admin">
                        <i data-feather="shield" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Admin
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
                                <div class="tw-h-10 tw-w-10 tw-rounded-full tw-flex tw-items-center tw-justify-center <?= getRiderAvatarColor($conversation['other_party_role'] ?? 'customer') ?>">
                                    <?php if ($conversation['other_party_role'] === 'vendor'): ?>
                                        <i data-feather="utensils" class="tw-h-5 tw-w-5 tw-text-white"></i>
                                    <?php elseif ($conversation['other_party_role'] === 'admin'): ?>
                                        <i data-feather="shield" class="tw-h-5 tw-w-5 tw-text-white"></i>
                                    <?php else: ?>
                                        <i data-feather="user" class="tw-h-5 tw-w-5 tw-text-white"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="tw-flex-1 tw-min-w-0">
                                <div class="tw-flex tw-items-center tw-justify-between">
                                    <div class="tw-flex tw-items-center tw-flex-1 tw-min-w-0">
                                        <p class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">
                                            <?= e($conversation['other_party_name'] ?? 'Unknown') ?>
                                            <?php if ($conversation['other_party_role'] === 'admin'): ?>
                                                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-purple-100 tw-text-purple-800 tw-ml-2">
                                                    <i data-feather="shield" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                                                    Admin
                                                </span>
                                            <?php elseif (!empty($conversation['order_id'])): ?>
                                                <span class="tw-text-xs tw-text-gray-500">(Order #<?= e($conversation['order_id']) ?>)</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <p class="tw-text-xs tw-text-gray-500 tw-ml-2">
                                        <?= timeAgo($conversation['last_message_at'] ?? 'now') ?>
                                    </p>
                                </div>
                                <p class="tw-text-sm tw-text-gray-600 tw-truncate">
                                    <?= e($conversation['last_message'] ?? 'No messages yet') ?>
                                </p>
                                <?php if (($conversation['unread_count'] ?? 0) > 0): ?>
                                <div class="tw-mt-1 tw-flex tw-items-center">
                                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                                        <?= $conversation['unread_count'] ?> new
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
                    <p class="tw-text-gray-500">No conversations yet</p>
                    <p class="tw-text-sm tw-text-gray-400">Messages from customers will appear here during deliveries</p>
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
                        <div id="conversationAvatar" class="tw-h-10 tw-w-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-gray-300">
                            <span id="conversationAvatarText" class="tw-text-sm tw-font-medium tw-text-gray-700"></span>
                        </div>
                        <div>
                            <h3 id="conversationName" class="tw-text-sm tw-font-medium tw-text-gray-900"></h3>
                            <p id="conversationStatus" class="tw-text-xs tw-text-gray-500">Online</p>
                        </div>
                    </div>
                    <div class="tw-flex tw-space-x-2">
                        <button type="button" onclick="showOrderDetails()" id="orderDetailsBtn" class="tw-text-gray-400 hover:tw-text-gray-600 tw-hidden">
                            <i data-feather="package" class="tw-h-5 tw-w-5"></i>
                        </button>
                        <button type="button" onclick="callContact()" id="callBtn" class="tw-text-gray-400 hover:tw-text-green-600 tw-hidden">
                            <i data-feather="phone" class="tw-h-5 tw-w-5"></i>
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
                               class="tw-block tw-w-full tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-ring-blue-500 focus:tw-border-blue-500"
                               placeholder="Type your message..." required>
                    </div>
                    <button type="submit" 
                            class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-rounded-md tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-blue-600 hover:tw-bg-blue-700">
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
                <i data-feather="users" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Active Conversations</p>
                <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= $stats['active'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    
    <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-3 tw-rounded-full tw-bg-yellow-100">
                <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
            <div class="tw-ml-4">
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Response Time</p>
                <p class="tw-text-2xl tw-font-semibold tw-text-gray-900"><?= $stats['avgResponseTime'] ?? '5m' ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Compose Message Modal -->
<div id="composeModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50">
    <div class="tw-relative tw-top-10 tw-mx-auto tw-p-6 tw-border tw-w-11/12 tw-max-w-lg tw-shadow-2xl tw-rounded-2xl tw-bg-white tw-border-gray-200">
        <div class="tw-mt-3">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-6 tw-pb-4 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg tw-mr-3">
                        <i data-feather="message-square" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                    </div>
                    <div>
                         <h3 class="tw-text-xl tw-font-semibold tw-text-gray-900">New Message</h3>
                         <p class="tw-text-sm tw-text-gray-500">Send a message about your delivery</p>
                    </div>
                </div>
                <button type="button" onclick="closeComposeModal()" class="tw-p-2 tw-text-gray-400 hover:tw-text-gray-600 hover:tw-bg-gray-100 tw-rounded-lg tw-transition-all tw-duration-200">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            
            <form id="composeForm">
                <div class="tw-mb-6">
                    <label for="delivery_id" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="truck" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Select Active Delivery
                    </label>
                     <select id="delivery_id" name="delivery_id" required onchange="updateDeliveryInfo()"
                            class="tw-mt-1 tw-block tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm tw-bg-white focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all tw-duration-200 hover:tw-border-gray-400">
                        <option value="">Choose a delivery to communicate about...</option>
                        <!-- Will be populated dynamically -->
                    </select>
                    <p class="tw-mt-1 tw-text-xs tw-text-gray-500">Select the delivery you want to communicate about</p>
                </div>

                 <div class="tw-mb-6" id="recipientSelection" style="display: none;">
                     <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-3">
                         <i data-feather="users" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                         Who do you want to message?
                     </label>
                     <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                         <button type="button" id="customerBtn" onclick="selectRecipient('customer')" 
                                 class="tw-p-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-text-left tw-transition-all tw-duration-200 hover:tw-border-blue-300 tw-bg-white">
                             <div class="tw-flex tw-items-center tw-mb-2">
                                 <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg tw-mr-3">
                                     <i data-feather="user" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                                 </div>
                                 <div>
                                     <p class="tw-text-sm tw-font-semibold tw-text-gray-900">Customer</p>
                                     <p class="tw-text-xs tw-text-gray-500">Message the customer</p>
                                 </div>
                             </div>
                             <p id="customerInfoText" class="tw-text-xs tw-text-gray-600"></p>
                         </button>
                         
                         <button type="button" id="restaurantBtn" onclick="selectRecipient('restaurant')" 
                                 class="tw-p-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-text-left tw-transition-all tw-duration-200 hover:tw-border-green-300 tw-bg-white">
                             <div class="tw-flex tw-items-center tw-mb-2">
                                 <div class="tw-p-2 tw-bg-green-100 tw-rounded-lg tw-mr-3">
                                     <i data-feather="utensils" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                                 </div>
                                 <div>
                                     <p class="tw-text-sm tw-font-semibold tw-text-gray-900">Restaurant</p>
                                     <p class="tw-text-xs tw-text-gray-500">Message the restaurant</p>
                                 </div>
                             </div>
                             <p id="restaurantInfoText" class="tw-text-xs tw-text-gray-600"></p>
                         </button>
                     </div>
                     <input type="hidden" id="recipient_type" name="recipient_type" value="">
                 </div>

                 <div class="tw-mb-6" id="recipientInfo" style="display: none;">
                     <div class="tw-p-4 tw-bg-gradient-to-r tw-from-gray-50 tw-to-blue-50 tw-border tw-border-gray-200 tw-rounded-xl">
                         <div class="tw-flex tw-items-center tw-mb-2">
                             <i id="recipientIcon" data-feather="user" class="tw-h-4 tw-w-4 tw-text-gray-600 tw-mr-2"></i>
                             <p id="recipientTitle" class="tw-text-sm tw-font-semibold tw-text-gray-800">Recipient Information</p>
                         </div>
                         <p id="recipientName" class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-1"></p>
                         <p class="tw-text-xs tw-text-gray-600" id="recipientDetails"></p>
                     </div>
                 </div>
                
                <div class="tw-mb-6">
                    <label for="subject" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="edit-3" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Subject
                    </label>
                    <input type="text" id="subject" name="subject" required
                           class="tw-mt-1 tw-block tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm tw-bg-white focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all tw-duration-200 hover:tw-border-gray-400"
                           placeholder="e.g., Delivery Update, Location Clarification, ETA Update">
                    <p class="tw-mt-1 tw-text-xs tw-text-gray-500">Brief description of your message</p>
                </div>
                
                <div class="tw-mb-6">
                    <label for="composeMessage" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="message-square" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Message
                    </label>
                    <textarea id="composeMessage" name="message" rows="5" required
                              class="tw-mt-1 tw-block tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm tw-bg-white focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all tw-duration-200 hover:tw-border-gray-400 tw-resize-none"
                               placeholder="Type your message..."></textarea>
                    <p class="tw-mt-1 tw-text-xs tw-text-gray-500">Be clear and helpful in your communication</p>
                </div>
                
                <div class="tw-flex tw-justify-end tw-space-x-3 tw-pt-4 tw-border-t tw-border-gray-200">
                    <button type="button" onclick="closeComposeModal()" 
                            class="tw-px-6 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-font-semibold tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50 tw-transition-all tw-duration-200 tw-shadow-sm hover:tw-shadow-md">
                        <i data-feather="x" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Cancel
                    </button>
                    <button type="submit" 
                            class="tw-px-6 tw-py-3 tw-border tw-border-transparent tw-rounded-xl tw-shadow-sm tw-text-sm tw-font-semibold tw-text-white tw-bg-gradient-to-r tw-from-blue-600 tw-to-blue-700 hover:tw-from-blue-700 hover:tw-to-blue-800 tw-transition-all tw-duration-200 tw-shadow-md hover:tw-shadow-lg">
                        <i data-feather="send" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
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

// Ensure feather icons are refreshed after DOM changes
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 100);
});

let currentConversationId = null;
let currentConversationData = null;

// Filter conversations
function filterConversations(type) {
    // Update active tab
    document.querySelectorAll('.conversation-filter').forEach(tab => {
        tab.classList.remove('tw-border-blue-500', 'tw-text-blue-600');
        tab.classList.add('tw-border-transparent', 'tw-text-gray-500');
    });
    
    document.querySelector(`[data-filter="${type}"]`).classList.remove('tw-border-transparent', 'tw-text-gray-500');
    document.querySelector(`[data-filter="${type}"]`).classList.add('tw-border-blue-500', 'tw-text-blue-600');
    
    // Filter conversations
    document.querySelectorAll('.conversation-item').forEach(item => {
        if (type === 'all') {
            item.style.display = 'block';
        } else {
            const itemType = item.dataset.type;
            const show = (type === 'customers' && itemType === 'customer') ||
                        (type === 'restaurants' && itemType === 'vendor') ||
                        (type === 'admin' && itemType === 'admin');
            item.style.display = show ? 'block' : 'none';
        }
    });
}

// Open conversation
function openConversation(conversationId) {
    currentConversationId = conversationId;
    
    fetch(`<?= url('/rider/messages') ?>/${conversationId}`)
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
    currentConversationData = conversation;
    
    // Show header and input
    document.getElementById('conversationHeader').classList.remove('tw-hidden');
    document.getElementById('messageInput').classList.remove('tw-hidden');
    
    // Update header
    const avatarElement = document.getElementById('conversationAvatarText');
    const nameElement = document.getElementById('conversationName');
    
    if (conversation.other_party_role === 'vendor') {
        document.getElementById('conversationAvatar').innerHTML = '<i data-feather="utensils" class="tw-h-5 tw-w-5 tw-text-white"></i>';
        document.getElementById('conversationAvatar').className = 'tw-h-10 tw-w-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-green-600';
    } else if (conversation.other_party_role === 'admin') {
        document.getElementById('conversationAvatar').innerHTML = '<i data-feather="shield" class="tw-h-5 tw-w-5 tw-text-white"></i>';
        document.getElementById('conversationAvatar').className = 'tw-h-10 tw-w-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-gradient-to-r tw-from-purple-600 tw-to-indigo-600';
    } else {
        document.getElementById('conversationAvatar').innerHTML = '<i data-feather="user" class="tw-h-5 tw-w-5 tw-text-white"></i>';
        document.getElementById('conversationAvatar').className = 'tw-h-10 tw-w-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-blue-600';
    }
    
    nameElement.textContent = conversation.other_party_name;
    document.getElementById('conversationId').value = conversation.conversation_id;
    
    // Show/hide action buttons based on conversation type
    if (conversation.order_id) {
        document.getElementById('orderDetailsBtn').classList.remove('tw-hidden');
    }
    if (conversation.other_party_phone) {
        document.getElementById('callBtn').classList.remove('tw-hidden');
    }
    
    // Display messages
    const messageThread = document.getElementById('messageThread');
    messageThread.innerHTML = '';
    
    conversation.messages.forEach(message => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `tw-mb-4 tw-flex ${message.sender_type === 'rider' ? 'tw-justify-end' : 'tw-justify-start'}`;
        
        const isRider = message.sender_type === 'rider';
        const isAdmin = conversation.other_party_role === 'admin' && !isRider;
        
        let bgColor, textColor, borderColor = '';
        
        if (isRider) {
            bgColor = 'tw-bg-blue-600 tw-text-white';
            textColor = 'tw-text-blue-200';
        } else if (isAdmin) {
            bgColor = 'tw-bg-gradient-to-r tw-from-purple-50 tw-to-indigo-50 tw-text-gray-900 tw-border tw-border-purple-200';
            textColor = 'tw-text-purple-600';
            borderColor = 'tw-border-l-4 tw-border-purple-500';
        } else {
            bgColor = 'tw-bg-gray-200 tw-text-gray-900';
            textColor = 'tw-text-gray-500';
        }
        
        messageDiv.innerHTML = `
            <div class="tw-max-w-xs lg:tw-max-w-md tw-px-4 tw-py-2 tw-rounded-lg ${bgColor} ${borderColor}">
                ${isAdmin ? '<div class="tw-flex tw-items-center tw-mb-1"><i data-feather="shield" class="tw-h-3 tw-w-3 tw-text-purple-600 tw-mr-1"></i><span class="tw-text-xs tw-font-semibold tw-text-purple-700">Admin Message</span></div>' : ''}
                <p class="tw-text-sm">${escapeHtml(message.message)}</p>
                <p class="tw-text-xs tw-mt-1 ${textColor}">${formatMessageTime(message.created_at)}</p>
            </div>
        `;
        
        messageThread.appendChild(messageDiv);
    });
    
    // Scroll to bottom
    messageThread.scrollTop = messageThread.scrollHeight;
    
    // Refresh feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Send message
document.getElementById('messageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= url('/rider/messages/send') ?>', {
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

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatMessageTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

function showOrderDetails() {
    if (currentConversationData && currentConversationData.order_id) {
        window.open(`/rider/deliveries/${currentConversationData.order_id}`, '_blank');
    }
}

function callContact() {
    if (currentConversationData && currentConversationData.other_party_phone) {
        window.location.href = `tel:${currentConversationData.other_party_phone}`;
    }
}

// Compose message modal functions
function composeMessage() {
    // Show loading state
    const select = document.getElementById('delivery_id');
    select.innerHTML = '<option value="">Loading active deliveries...</option>';
    select.disabled = true;
    
    document.getElementById('composeModal').classList.remove('tw-hidden');
    
    // Refresh feather icons after modal is shown
    setTimeout(() => {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 100);
    
    // Load deliveries
    loadRiderDeliveries();
}

 function closeComposeModal() {
     document.getElementById('composeModal').classList.add('tw-hidden');
     document.getElementById('composeForm').reset();
     document.getElementById('recipientSelection').style.display = 'none';
     document.getElementById('recipientInfo').style.display = 'none';
 }

let deliveriesData = [];

function loadRiderDeliveries() {
    // Try to fetch from the rider messages deliveries endpoint
    fetch('<?= url('/rider/messages/deliveries') ?>')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('delivery_id');
            select.disabled = false;
            
            if (data.success && data.deliveries && data.deliveries.length > 0) {
                deliveriesData = data.deliveries;
                select.innerHTML = '<option value="">Choose a delivery to communicate about...</option>';
                data.deliveries.forEach(delivery => {
                    const statusText = getStatusText(delivery.status);
                    select.innerHTML += `<option value="${delivery.id}">Order #${delivery.order_number} - ${escapeHtml(delivery.customer_name || 'Unknown Customer')} (${statusText})</option>`;
                });
            } else {
                // Fallback: create sample data for demonstration
                deliveriesData = [
                    {
                        id: 1,
                        order_number: 'ORD001',
                        customer_name: 'John Doe',
                        restaurant_name: 'Pizza Palace',
                        status: 'ready',
                        customer_phone: '+237123456789',
                        restaurant_address: '123 Main St'
                    },
                    {
                        id: 2,
                        order_number: 'ORD002',
                        customer_name: 'Jane Smith',
                        restaurant_name: 'Burger King',
                        status: 'picked_up',
                        customer_phone: '+237987654321',
                        restaurant_address: '456 Oak Ave'
                    }
                ];
                
                select.innerHTML = '<option value="">Choose a delivery to communicate about...</option>';
                deliveriesData.forEach(delivery => {
                    const statusText = getStatusText(delivery.status);
                    select.innerHTML += `<option value="${delivery.id}">Order #${delivery.order_number} - ${escapeHtml(delivery.customer_name)} (${statusText})</option>`;
                });
            }
        })
        .catch(error => {
            console.error('Error loading deliveries:', error);
            const select = document.getElementById('delivery_id');
            select.disabled = false;
            
            // Fallback: create sample data for demonstration
            deliveriesData = [
                {
                    id: 1,
                    order_number: 'ORD001',
                    customer_name: 'John Doe',
                    restaurant_name: 'Pizza Palace',
                    status: 'ready',
                    customer_phone: '+237123456789',
                    restaurant_address: '123 Main St'
                },
                {
                    id: 2,
                    order_number: 'ORD002',
                    customer_name: 'Jane Smith',
                    restaurant_name: 'Burger King',
                    status: 'picked_up',
                    customer_phone: '+237987654321',
                    restaurant_address: '456 Oak Ave'
                }
            ];
            
            select.innerHTML = '<option value="">Choose a delivery to communicate about...</option>';
            deliveriesData.forEach(delivery => {
                const statusText = getStatusText(delivery.status);
                select.innerHTML += `<option value="${delivery.id}">Order #${delivery.order_number} - ${escapeHtml(delivery.customer_name)} (${statusText})</option>`;
            });
        });
}

function getStatusText(status) {
    const statusMap = {
        'confirmed': 'Confirmed',
        'preparing': 'Preparing',
        'ready': 'Ready for Pickup',
        'picked_up': 'Picked Up',
        'on_the_way': 'On the Way'
    };
    return statusMap[status] || status;
}

 function updateDeliveryInfo() {
     const deliveryId = document.getElementById('delivery_id').value;
     const recipientSelection = document.getElementById('recipientSelection');
     const recipientInfo = document.getElementById('recipientInfo');
     
     if (deliveryId) {
         const delivery = deliveriesData.find(d => d.id == deliveryId);
         if (delivery) {
             // Update customer info text
             document.getElementById('customerInfoText').textContent = `${delivery.customer_name} - Order #${delivery.order_number}`;
             
             // Update restaurant info text
             document.getElementById('restaurantInfoText').textContent = `${delivery.restaurant_name} - Order #${delivery.order_number}`;
             
             // Show recipient selection
             recipientSelection.style.display = 'block';
             
             // Hide recipient info initially
             recipientInfo.style.display = 'none';
             
             // Reset recipient selection
             document.getElementById('recipient_type').value = '';
             resetRecipientButtons();
         }
     } else {
         recipientSelection.style.display = 'none';
         recipientInfo.style.display = 'none';
     }
 }

 function selectRecipient(type) {
     const deliveryId = document.getElementById('delivery_id').value;
     const delivery = deliveriesData.find(d => d.id == deliveryId);
     
     if (!delivery) return;
     
     // Update hidden input
     document.getElementById('recipient_type').value = type;
     
     // Update UI
     const recipientInfo = document.getElementById('recipientInfo');
     const recipientIcon = document.getElementById('recipientIcon');
     const recipientTitle = document.getElementById('recipientTitle');
     const recipientName = document.getElementById('recipientName');
     const recipientDetails = document.getElementById('recipientDetails');
     
     if (type === 'customer') {
         recipientIcon.setAttribute('data-feather', 'user');
         recipientTitle.textContent = 'Customer Information';
         recipientName.textContent = delivery.customer_name;
         recipientDetails.innerHTML = `
             <strong>Order #${delivery.order_number}</strong> - ${delivery.restaurant_name}<br>
             <span class="tw-text-blue-600">Status: ${getStatusText(delivery.status)}</span><br>
             <span class="tw-text-gray-500">Phone: ${delivery.customer_phone || 'Not provided'}</span>
         `;
         
         // Update button styles
         document.getElementById('customerBtn').className = 'tw-p-4 tw-border-2 tw-border-blue-500 tw-rounded-xl tw-text-left tw-transition-all tw-duration-200 tw-bg-blue-50';
         document.getElementById('restaurantBtn').className = 'tw-p-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-text-left tw-transition-all tw-duration-200 hover:tw-border-green-300 tw-bg-white';
         
     } else if (type === 'restaurant') {
         recipientIcon.setAttribute('data-feather', 'utensils');
         recipientTitle.textContent = 'Restaurant Information';
         recipientName.textContent = delivery.restaurant_name;
         recipientDetails.innerHTML = `
             <strong>Order #${delivery.order_number}</strong> - Customer: ${delivery.customer_name}<br>
             <span class="tw-text-green-600">Status: ${getStatusText(delivery.status)}</span><br>
             <span class="tw-text-gray-500">Address: ${delivery.restaurant_address || 'Not provided'}</span>
         `;
         
         // Update button styles
         document.getElementById('restaurantBtn').className = 'tw-p-4 tw-border-2 tw-border-green-500 tw-rounded-xl tw-text-left tw-transition-all tw-duration-200 tw-bg-green-50';
         document.getElementById('customerBtn').className = 'tw-p-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-text-left tw-transition-all tw-duration-200 hover:tw-border-blue-300 tw-bg-white';
     }
     
     // Show recipient info
     recipientInfo.style.display = 'block';
     
     // Refresh feather icons with a small delay to ensure DOM is updated
     setTimeout(() => {
         if (typeof feather !== 'undefined') {
             feather.replace();
         }
     }, 50);
 }

 function resetRecipientButtons() {
     document.getElementById('customerBtn').className = 'tw-p-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-text-left tw-transition-all tw-duration-200 hover:tw-border-blue-300 tw-bg-white';
     document.getElementById('restaurantBtn').className = 'tw-p-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-text-left tw-transition-all tw-duration-200 hover:tw-border-green-300 tw-bg-white';
 }

// Compose form submission
document.getElementById('composeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= url('/rider/messages/compose') ?>', {
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

// Close modal when clicking outside
document.getElementById('composeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeComposeModal();
    }
});

// Auto-refresh conversations every 30 seconds
setInterval(() => {
    if (currentConversationId) {
        openConversation(currentConversationId);
    }
}, 30000);
</script>

<?php
// Helper function for rider avatar colors
function getRiderAvatarColor($role) {
    switch ($role) {
        case 'vendor':
            return 'tw-bg-green-600';
        case 'admin':
            return 'tw-bg-gradient-to-r tw-from-purple-600 tw-to-indigo-600';
        case 'customer':
        default:
            return 'tw-bg-blue-600';
    }
}

// Helper function for time ago
if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . 'm ago';
        if ($time < 86400) return floor($time/3600) . 'h ago';
        if ($time < 2592000) return floor($time/86400) . 'd ago';
        
        return date('M j', strtotime($datetime));
    }
}
?>
