<?php
$title = $title ?? 'Messages - Time2Eat';
$currentPage = $currentPage ?? 'messages';
$user = $user ?? null;
$conversations = $conversations ?? [];
$stats = $stats ?? [];
?>

<!-- Page Header -->
<div class="tw-mb-6">
    <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Messages</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Communicate with restaurants, delivery riders, and support team
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-gap-3">
            <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-gray-100 tw-text-gray-700">
                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                <?= count($conversations ?? []) ?> Conversations
            </span>
            <button type="button" onclick="composeMessage()"
                    class="tw-bg-orange-600 tw-border tw-border-transparent tw-rounded-lg tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white hover:tw-bg-orange-700 tw-transition-colors tw-flex tw-items-center">
                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
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
                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-orange-100 tw-text-orange-800">
                        <?= count($conversations ?? []) ?> active
                    </span>
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="tw-border-b tw-border-gray-200">
                <nav class="tw-flex tw-space-x-8 tw-px-6" aria-label="Tabs">
                    <button onclick="filterConversations('all')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-font-medium tw-text-sm tw-border-orange-500 tw-text-orange-600" 
                            data-filter="all">
                        All
                    </button>
                    <button onclick="filterConversations('restaurants')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" 
                            data-filter="restaurants">
                        Restaurants
                    </button>
                    <button onclick="filterConversations('riders')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" 
                            data-filter="riders">
                        Riders
                    </button>
                    <button onclick="filterConversations('support')" 
                            class="conversation-filter tw-whitespace-nowrap tw-py-4 tw-px-1 tw-border-b-2 tw-border-transparent tw-font-medium tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300" 
                            data-filter="support">
                        Support
                    </button>
                </nav>
            </div>
            
            <div class="tw-divide-y tw-divide-gray-200 tw-max-h-96 tw-overflow-y-auto" id="conversations-list">
                <?php if (!empty($conversations)): ?>
                    <?php foreach ($conversations as $conversation): ?>
                    <div class="conversation-item tw-p-4 hover:tw-bg-gray-50 tw-cursor-pointer <?= ($conversation['unread_count'] ?? 0) > 0 ? 'tw-bg-blue-50' : '' ?>" 
                         data-type="<?= e($conversation['type'] ?? 'general') ?>"
                         onclick="openConversation('<?= e($conversation['conversation_id'] ?? '') ?>')">
                        <div class="tw-flex tw-items-start tw-space-x-3">
                            <div class="tw-flex-shrink-0">
                                <div class="tw-h-10 tw-w-10 tw-rounded-full tw-flex tw-items-center tw-justify-center <?= getAvatarColor($conversation['type'] ?? 'general') ?>">
                                    <?php if ($conversation['type'] === 'restaurant'): ?>
                                        <i data-feather="utensils" class="tw-h-5 tw-w-5 tw-text-white"></i>
                                    <?php elseif ($conversation['type'] === 'rider'): ?>
                                        <i data-feather="truck" class="tw-h-5 tw-w-5 tw-text-white"></i>
                                    <?php elseif ($conversation['type'] === 'support'): ?>
                                        <i data-feather="help-circle" class="tw-h-5 tw-w-5 tw-text-white"></i>
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
                                    <p class="tw-text-xs tw-text-gray-500">
                                        <?= timeAgo($conversation['last_message_at'] ?? 'now') ?>
                                    </p>
                                </div>
                                <p class="tw-text-sm tw-text-gray-600 tw-truncate">
                                    <?= e($conversation['last_message'] ?? 'No messages yet') ?>
                                </p>
                                <?php if (($conversation['unread_count'] ?? 0) > 0): ?>
                                <div class="tw-mt-1 tw-flex tw-items-center">
                                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-orange-100 tw-text-orange-800">
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
                    <p class="tw-text-sm tw-text-gray-400">Start a conversation with a restaurant or get help from support</p>
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
            
            <div id="messageInput" class="tw-px-6 tw-py-4 tw-bg-gray-50 tw-border-t tw-border-gray-200 tw-hidden">
                <form id="messageForm" class="tw-flex tw-space-x-3">
                    <input type="hidden" id="conversationId" name="conversation_id">
                    <div class="tw-flex-1 tw-relative">
                        <input type="text" id="messageText" name="message" 
                               class="tw-block tw-w-full tw-px-4 tw-py-3 tw-pl-12 tw-pr-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-shadow-sm tw-bg-white tw-text-gray-900 tw-placeholder-gray-500 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 focus:tw-outline-none tw-transition-all tw-duration-200 tw-ease-in-out hover:tw-border-gray-300"
                               placeholder="Type your message..." required>
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="message-circle" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                        </div>
                    </div>
                    <button type="submit" 
                            class="tw-inline-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-border tw-border-transparent tw-rounded-xl tw-shadow-sm tw-text-sm tw-font-medium tw-text-white tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600 hover:tw-from-orange-600 hover:tw-to-orange-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 tw-ease-in-out tw-transform hover:tw-scale-105 active:tw-scale-95">
                        <i data-feather="send" class="tw-h-4 tw-w-4 tw-mr-1"></i>
                        <span class="tw-hidden sm:tw-inline">Send</span>
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
<div id="composeModal" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-hidden tw-z-50 tw-transition-opacity tw-duration-300">
    <div class="tw-relative tw-top-20 tw-mx-auto tw-p-0 tw-border-0 tw-w-11/12 tw-max-w-2xl tw-shadow-2xl tw-rounded-2xl tw-bg-white tw-transform tw-transition-all tw-duration-300 tw-scale-95 tw-opacity-0" id="modalContent">
        <div class="tw-p-8">
            <!-- Modal Header -->
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-8 tw-pb-4 tw-border-b tw-border-gray-200">
                <div class="tw-flex tw-items-center">
                    <div class="tw-p-3 tw-rounded-full tw-bg-orange-100 tw-mr-4">
                        <i data-feather="message-square" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
                    </div>
                    <div>
                        <h3 class="tw-text-2xl tw-font-bold tw-text-gray-900">New Message</h3>
                        <p class="tw-text-sm tw-text-gray-500 tw-mt-1">Send a message to riders, restaurants, or support</p>
                    </div>
                </div>
                <button type="button" onclick="closeComposeModal()" 
                        class="tw-p-2 tw-text-gray-400 hover:tw-text-gray-600 hover:tw-bg-gray-100 tw-rounded-full tw-transition-all tw-duration-200">
                    <i data-feather="x" class="tw-h-6 tw-w-6"></i>
                </button>
            </div>
            
            <form id="composeForm" class="tw-space-y-6">
                <div class="tw-space-y-1">
                    <label for="recipient_type" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="users" class="tw-inline tw-h-4 tw-w-4 tw-mr-2"></i>
                        Message Type
                    </label>
                    <div class="tw-relative">
                    <select id="recipient_type" name="recipient_type" required onchange="updateRecipientOptions()"
                                class="tw-block tw-w-full tw-px-4 tw-py-3 tw-pl-10 tw-pr-10 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-shadow-sm tw-bg-white tw-text-gray-900 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 focus:tw-outline-none tw-transition-all tw-duration-200 tw-ease-in-out hover:tw-border-gray-300 tw-appearance-none">
                            <option value="">Choose who to message...</option>
                            <option value="rider">🚚 Delivery Rider</option>
                            <option value="restaurant">🍽️ Restaurant</option>
                            <option value="support">🛠️ Customer Support</option>
                            <option value="order_inquiry">📦 Order Inquiry</option>
                    </select>
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="chevron-down" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div class="tw-space-y-1 tw-transition-all tw-duration-300 tw-ease-in-out" id="riderSelect" style="display: none;">
                    <label for="rider_id" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="truck" class="tw-inline tw-h-4 tw-w-4 tw-mr-2"></i>
                        Delivery Rider
                    </label>
                    <div class="tw-relative">
                        <select id="rider_id" name="rider_id"
                                class="tw-block tw-w-full tw-px-4 tw-py-3 tw-pl-10 tw-pr-10 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-shadow-sm tw-bg-white tw-text-gray-900 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 focus:tw-outline-none tw-transition-all tw-duration-200 tw-ease-in-out hover:tw-border-gray-300 tw-appearance-none">
                            <option value="">Select a rider...</option>
                            <!-- Will be populated dynamically -->
                        </select>
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="user" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div class="tw-space-y-1 tw-transition-all tw-duration-300 tw-ease-in-out" id="riderOrderSelect" style="display: none;">
                    <label for="rider_order_id" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="package" class="tw-inline tw-h-4 tw-w-4 tw-mr-2"></i>
                        Order (Optional)
                    </label>
                    <div class="tw-relative">
                        <select id="rider_order_id" name="order_id"
                                class="tw-block tw-w-full tw-px-4 tw-py-3 tw-pl-10 tw-pr-10 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-shadow-sm tw-bg-white tw-text-gray-900 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 focus:tw-outline-none tw-transition-all tw-duration-200 tw-ease-in-out hover:tw-border-gray-300 tw-appearance-none">
                            <option value="">Select order (optional)</option>
                            <!-- Will be populated dynamically -->
                        </select>
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="shopping-bag" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                        </div>
                    </div>
                    <p class="tw-text-xs tw-text-gray-500 tw-mt-2 tw-flex tw-items-center">
                        <i data-feather="info" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                        Link message to a specific order for better context
                    </p>
                </div>

                <div class="tw-space-y-1 tw-transition-all tw-duration-300 tw-ease-in-out" id="restaurantSelect" style="display: none;">
                    <label for="restaurant_id" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="home" class="tw-inline tw-h-4 tw-w-4 tw-mr-2"></i>
                        Restaurant
                    </label>
                    <div class="tw-relative">
                    <select id="restaurant_id" name="restaurant_id"
                                class="tw-block tw-w-full tw-px-4 tw-py-3 tw-pl-10 tw-pr-10 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-shadow-sm tw-bg-white tw-text-gray-900 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 focus:tw-outline-none tw-transition-all tw-duration-200 tw-ease-in-out hover:tw-border-gray-300 tw-appearance-none">
                            <option value="">Select a restaurant...</option>
                        <!-- Will be populated dynamically -->
                    </select>
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="map-pin" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div class="tw-space-y-1 tw-transition-all tw-duration-300 tw-ease-in-out" id="orderSelect" style="display: none;">
                    <label for="order_id" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="file-text" class="tw-inline tw-h-4 tw-w-4 tw-mr-2"></i>
                        Order
                    </label>
                    <div class="tw-relative">
                    <select id="order_id" name="order_id"
                                class="tw-block tw-w-full tw-px-4 tw-py-3 tw-pl-10 tw-pr-10 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-shadow-sm tw-bg-white tw-text-gray-900 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 focus:tw-outline-none tw-transition-all tw-duration-200 tw-ease-in-out hover:tw-border-gray-300 tw-appearance-none">
                            <option value="">Select an order...</option>
                        <!-- Will be populated dynamically -->
                    </select>
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="list" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <div class="tw-space-y-1">
                    <label for="subject" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="edit-3" class="tw-inline tw-h-4 tw-w-4 tw-mr-2"></i>
                        Subject
                    </label>
                    <div class="tw-relative">
                    <input type="text" id="subject" name="subject" required
                               class="tw-block tw-w-full tw-px-4 tw-py-3 tw-pl-10 tw-pr-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-shadow-sm tw-bg-white tw-text-gray-900 tw-placeholder-gray-500 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 focus:tw-outline-none tw-transition-all tw-duration-200 tw-ease-in-out hover:tw-border-gray-300"
                               placeholder="Enter a subject for your message...">
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i data-feather="tag" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <div class="tw-space-y-1">
                    <label for="composeMessage" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">
                        <i data-feather="message-square" class="tw-inline tw-h-4 tw-w-4 tw-mr-2"></i>
                        Message
                    </label>
                    <div class="tw-relative">
                    <textarea id="composeMessage" name="message" rows="4" required
                                  class="tw-block tw-w-full tw-px-4 tw-py-3 tw-pl-10 tw-pr-4 tw-border-2 tw-border-gray-200 tw-rounded-xl tw-shadow-sm tw-bg-white tw-text-gray-900 tw-placeholder-gray-500 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 focus:tw-outline-none tw-transition-all tw-duration-200 tw-ease-in-out hover:tw-border-gray-300 tw-resize-none"
                                  placeholder="Type your message here..."></textarea>
                        <div class="tw-absolute tw-top-3 tw-left-0 tw-pl-3 tw-flex tw-items-start tw-pointer-events-none">
                            <i data-feather="edit" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                        </div>
                    </div>
                    <div class="tw-flex tw-justify-between tw-items-center tw-mt-2">
                        <p class="tw-text-xs tw-text-gray-500 tw-flex tw-items-center">
                            <i data-feather="info" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                            Be specific about your inquiry for faster response
                        </p>
                        <span id="charCount" class="tw-text-xs tw-text-gray-400">0/500</span>
                    </div>
                </div>
                
                <div class="tw-flex tw-justify-end tw-space-x-3 tw-pt-4 tw-border-t tw-border-gray-200">
                    <button type="button" onclick="closeComposeModal()" 
                            class="tw-px-6 tw-py-3 tw-border-2 tw-border-gray-300 tw-rounded-xl tw-text-sm tw-font-semibold tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50 hover:tw-border-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-gray-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 tw-ease-in-out tw-transform hover:tw-scale-105 active:tw-scale-95">
                        <i data-feather="x" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Cancel
                    </button>
                    <button type="submit" 
                            class="tw-px-6 tw-py-3 tw-border-2 tw-border-transparent tw-rounded-xl tw-shadow-lg tw-text-sm tw-font-semibold tw-text-white tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600 hover:tw-from-orange-600 hover:tw-to-orange-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 tw-ease-in-out tw-transform hover:tw-scale-105 active:tw-scale-95 tw-flex tw-items-center">
                        <i data-feather="send" class="tw-h-4 tw-w-4 tw-mr-2"></i>
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
let currentConversationData = null;

// Character counter for message textarea
document.addEventListener('DOMContentLoaded', function() {
    const messageTextarea = document.getElementById('composeMessage');
    const charCount = document.getElementById('charCount');
    
    if (messageTextarea && charCount) {
        messageTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length}/500`;
            
            if (length > 450) {
                charCount.classList.add('tw-text-orange-500');
                charCount.classList.remove('tw-text-gray-400');
            } else if (length > 400) {
                charCount.classList.add('tw-text-yellow-500');
                charCount.classList.remove('tw-text-gray-400', 'tw-text-orange-500');
            } else {
                charCount.classList.remove('tw-text-orange-500', 'tw-text-yellow-500');
                charCount.classList.add('tw-text-gray-400');
            }
        });
    }
    
    // Add focus effects to form inputs
    const formInputs = document.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('tw-ring-2', 'tw-ring-orange-200');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('tw-ring-2', 'tw-ring-orange-200');
        });
    });
});

// Filter conversations
function filterConversations(type) {
    // Update active tab
    document.querySelectorAll('.conversation-filter').forEach(tab => {
        tab.classList.remove('tw-border-orange-500', 'tw-text-orange-600');
        tab.classList.add('tw-border-transparent', 'tw-text-gray-500');
    });
    
    document.querySelector(`[data-filter="${type}"]`).classList.remove('tw-border-transparent', 'tw-text-gray-500');
    document.querySelector(`[data-filter="${type}"]`).classList.add('tw-border-orange-500', 'tw-text-orange-600');
    
    // Filter conversations
    document.querySelectorAll('.conversation-item').forEach(item => {
        if (type === 'all') {
            item.style.display = 'block';
        } else {
            const itemType = item.dataset.type;
            item.style.display = (itemType === type || (type === 'restaurants' && itemType === 'restaurant')) ? 'block' : 'none';
        }
    });
}

// Open conversation
function openConversation(conversationId) {
    currentConversationId = conversationId;
    
    fetch(`<?= url('/customer/messages') ?>/${conversationId}`)
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
    
    if (conversation.type === 'restaurant') {
        document.getElementById('conversationAvatar').innerHTML = '<i data-feather="utensils" class="tw-h-5 tw-w-5 tw-text-white"></i>';
        document.getElementById('conversationAvatar').className = 'tw-h-10 tw-w-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-green-600';
    } else if (conversation.type === 'rider') {
        document.getElementById('conversationAvatar').innerHTML = '<i data-feather="truck" class="tw-h-5 tw-w-5 tw-text-white"></i>';
        document.getElementById('conversationAvatar').className = 'tw-h-10 tw-w-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-blue-600';
    } else {
        avatarElement.textContent = conversation.other_party_name.charAt(0).toUpperCase();
        document.getElementById('conversationAvatar').className = 'tw-h-10 tw-w-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-bg-gray-400';
    }
    
    nameElement.textContent = conversation.other_party_name;
    document.getElementById('conversationId').value = conversation.conversation_id;
    
    // Show/hide action buttons based on conversation type
    if (conversation.order_id) {
        document.getElementById('orderDetailsBtn').classList.remove('tw-hidden');
    }
    if (conversation.phone) {
        document.getElementById('callBtn').classList.remove('tw-hidden');
    }
    
    // Display messages
    const messageThread = document.getElementById('messageThread');
    messageThread.innerHTML = '';
    
    conversation.messages.forEach(message => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `tw-mb-6 tw-flex tw-items-end ${message.sender_type === 'customer' ? 'tw-justify-end' : 'tw-justify-start'}`;
        
        const isCustomer = message.sender_type === 'customer';
        const bgColor = isCustomer ? 'tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600 tw-text-white tw-shadow-lg' : 'tw-bg-gray-100 tw-text-gray-900 tw-border tw-border-gray-200';
        const textColor = isCustomer ? 'tw-text-orange-100' : 'tw-text-gray-500';
        const bubbleClass = isCustomer ? 'tw-rounded-2xl tw-rounded-br-md' : 'tw-rounded-2xl tw-rounded-bl-md';
        
        messageDiv.innerHTML = `
            <div class="tw-max-w-xs lg:tw-max-w-md tw-px-4 tw-py-3 ${bubbleClass} ${bgColor} tw-shadow-sm">
                <p class="tw-text-sm tw-leading-relaxed">${escapeHtml(message.message)}</p>
                <div class="tw-flex tw-items-center tw-justify-between tw-mt-2">
                    <p class="tw-text-xs ${textColor}">${formatMessageTime(message.created_at)}</p>
                    ${isCustomer ? '<i data-feather="check" class="tw-h-3 tw-w-3 tw-text-orange-200"></i>' : ''}
                </div>
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
    
    fetch('<?= url('/customer/messages/send') ?>', {
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
    loadComposeOptions();
    const modal = document.getElementById('composeModal');
    const modalContent = document.getElementById('modalContent');
    
    modal.classList.remove('tw-hidden');
    
    // Trigger animation
    setTimeout(() => {
        modal.classList.add('tw-opacity-100');
        modalContent.classList.remove('tw-scale-95', 'tw-opacity-0');
        modalContent.classList.add('tw-scale-100', 'tw-opacity-100');
    }, 10);
}

function closeComposeModal() {
    const modal = document.getElementById('composeModal');
    const modalContent = document.getElementById('modalContent');
    
    // Trigger close animation
    modal.classList.remove('tw-opacity-100');
    modalContent.classList.remove('tw-scale-100', 'tw-opacity-100');
    modalContent.classList.add('tw-scale-95', 'tw-opacity-0');
    
    // Hide modal after animation
    setTimeout(() => {
        modal.classList.add('tw-hidden');
        document.getElementById('composeForm').reset();
        document.getElementById('riderSelect').style.display = 'none';
        document.getElementById('riderOrderSelect').style.display = 'none';
        document.getElementById('restaurantSelect').style.display = 'none';
        document.getElementById('orderSelect').style.display = 'none';
        
        // Reset character counter
        const charCount = document.getElementById('charCount');
        if (charCount) {
            charCount.textContent = '0/500';
            charCount.classList.remove('tw-text-orange-500', 'tw-text-yellow-500');
            charCount.classList.add('tw-text-gray-400');
        }
    }, 300);
}

function updateRecipientOptions() {
    const type = document.getElementById('recipient_type').value;
    const riderSelect = document.getElementById('riderSelect');
    const riderOrderSelect = document.getElementById('riderOrderSelect');
    const restaurantSelect = document.getElementById('restaurantSelect');
    const orderSelect = document.getElementById('orderSelect');
    
    // Hide all options first
    riderSelect.style.display = 'none';
    riderOrderSelect.style.display = 'none';
    restaurantSelect.style.display = 'none';
    orderSelect.style.display = 'none';
    
    if (type === 'rider') {
        riderSelect.style.display = 'block';
        riderOrderSelect.style.display = 'block';
        loadRiders();
        loadRiderOrders();
    } else if (type === 'restaurant') {
        restaurantSelect.style.display = 'block';
        loadRestaurants();
    } else if (type === 'order_inquiry') {
        orderSelect.style.display = 'block';
        loadOrders();
    }
}

function loadComposeOptions() {
    // Load restaurants and orders for compose modal
    loadRestaurants();
    loadOrders();
    loadRiders();
    loadRiderOrders();
}

function loadRiders() {
    fetch('<?= url('/customer/messages/riders') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('rider_id');
                select.innerHTML = '<option value="">Select rider</option>';
                data.riders.forEach(rider => {
                    select.innerHTML += `<option value="${rider.id}">${escapeHtml(rider.full_name)} (${rider.delivery_count} deliveries)</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading riders:', error));
}

function loadRiderOrders() {
    fetch('<?= url('/customer/messages/orders-with-riders') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('rider_order_id');
                select.innerHTML = '<option value="">Select order (optional)</option>';
                data.orders.forEach(order => {
                    select.innerHTML += `<option value="${order.id}">Order #${order.order_number} - ${escapeHtml(order.rider_name)} (${order.order_status})</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading rider orders:', error));
}

function loadRestaurants() {
    fetch('<?= url('/customer/messages/restaurants') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('restaurant_id');
                select.innerHTML = '<option value="">Select restaurant</option>';
                data.restaurants.forEach(restaurant => {
                    select.innerHTML += `<option value="${restaurant.id}">${escapeHtml(restaurant.name)}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading restaurants:', error));
}

function loadOrders() {
    fetch('<?= url('/customer/messages/orders') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('order_id');
                select.innerHTML = '<option value="">Select order</option>';
                data.orders.forEach(order => {
                    select.innerHTML += `<option value="${order.id}">Order #${order.order_number} - ${escapeHtml(order.restaurant_name)}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading orders:', error));
}

// Compose form submission
document.getElementById('composeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const recipientType = formData.get('recipient_type');
    
    // Determine the correct endpoint based on recipient type
    let endpoint = '/customer/messages/compose';
    if (recipientType === 'rider') {
        endpoint = '/customer/messages/compose-to-rider';
    } else if (recipientType === 'restaurant') {
        endpoint = '/customer/messages/compose-to-restaurant';
    } else if (recipientType === 'support') {
        endpoint = '/customer/messages/compose-to-support';
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
        window.open(`/customer/orders/${currentConversationData.order_id}/track`, '_blank');
    }
}

function callContact() {
    if (currentConversationData && currentConversationData.phone) {
        window.location.href = `tel:${currentConversationData.phone}`;
    }
}

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
// timeAgo function is already defined in src/helpers/functions.php
?>



