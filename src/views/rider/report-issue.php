<?php
$title = $title ?? 'Report Issue - Time2Eat';
$user = $user ?? null;
$currentPage = $currentPage ?? 'reports';
?>

<!-- Mobile-First Header -->
<div class="tw-bg-gradient-to-r tw-from-red-600 tw-to-orange-600 tw-rounded-2xl tw-p-6 tw-mb-6 tw-text-white">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-mb-1">Report Issue</h1>
            <p class="tw-text-red-100 tw-text-sm">Help us improve by reporting problems</p>
        </div>
        <div class="tw-p-3 tw-bg-white tw-bg-opacity-20 tw-backdrop-blur-sm tw-rounded-xl">
            <i data-feather="alert-triangle" class="tw-h-8 tw-w-8"></i>
        </div>
    </div>
</div>

<!-- Report Issue Form -->
<div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-p-6">
    <form id="reportIssueForm" class="tw-space-y-6">
        <!-- Issue Type -->
        <div>
            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3 tw-flex tw-items-center">
                <i data-feather="tag" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-red-500"></i>
                Issue Type <span class="tw-text-red-500">*</span>
            </label>
            <select id="issueType" name="issue_type" required class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-text-sm tw-focus:tw-ring-2 tw-focus:tw-ring-red-500 tw-focus:tw-border-red-500 tw-transition-colors tw-bg-white">
                <option value="">Select issue type</option>
                <option value="delivery_problem">🚚 Delivery Problem</option>
                <option value="app_technical">📱 App Technical Issue</option>
                <option value="payment_issue">💳 Payment Issue</option>
                <option value="customer_issue">👤 Customer Issue</option>
                <option value="restaurant_issue">🏪 Restaurant Issue</option>
                <option value="safety_concern">⚠️ Safety Concern</option>
                <option value="other">❓ Other</option>
            </select>
        </div>

        <!-- Priority Level -->
        <div>
            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3 tw-flex tw-items-center">
                <i data-feather="flag" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-500"></i>
                Priority Level
            </label>
            <div class="tw-grid tw-grid-cols-3 tw-gap-3">
                <label class="tw-flex tw-items-center tw-justify-center tw-p-3 tw-border tw-border-gray-200 tw-rounded-xl tw-cursor-pointer hover:tw-border-green-300 tw-transition-colors">
                    <input type="radio" name="priority" value="low" class="tw-sr-only tw-peer">
                    <div class="tw-peer-checked:tw-bg-green-50 tw-peer-checked:tw-border-green-300 tw-peer-checked:tw-text-green-700 tw-p-2 tw-rounded-lg tw-transition-colors">
                        <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-500 tw-mb-1"></i>
                        <div class="tw-text-xs tw-font-medium">Low</div>
                    </div>
                </label>
                <label class="tw-flex tw-items-center tw-justify-center tw-p-3 tw-border tw-border-gray-200 tw-rounded-xl tw-cursor-pointer hover:tw-border-yellow-300 tw-transition-colors">
                    <input type="radio" name="priority" value="medium" checked class="tw-sr-only tw-peer">
                    <div class="tw-peer-checked:tw-bg-yellow-50 tw-peer-checked:tw-border-yellow-300 tw-peer-checked:tw-text-yellow-700 tw-p-2 tw-rounded-lg tw-transition-colors">
                        <i data-feather="alert-circle" class="tw-h-5 tw-w-5 tw-text-yellow-500 tw-mb-1"></i>
                        <div class="tw-text-xs tw-font-medium">Medium</div>
                    </div>
                </label>
                <label class="tw-flex tw-items-center tw-justify-center tw-p-3 tw-border tw-border-gray-200 tw-rounded-xl tw-cursor-pointer hover:tw-border-red-300 tw-transition-colors">
                    <input type="radio" name="priority" value="high" class="tw-sr-only tw-peer">
                    <div class="tw-peer-checked:tw-bg-red-50 tw-peer-checked:tw-border-red-300 tw-peer-checked:tw-text-red-700 tw-p-2 tw-rounded-lg tw-transition-colors">
                        <i data-feather="x-circle" class="tw-h-5 tw-w-5 tw-text-red-500 tw-mb-1"></i>
                        <div class="tw-text-xs tw-font-medium">High</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Description -->
        <div>
            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3 tw-flex tw-items-center">
                <i data-feather="file-text" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-blue-500"></i>
                Description <span class="tw-text-red-500">*</span>
            </label>
            <textarea id="description" name="description" rows="5" required 
                      placeholder="Please describe the issue in detail. Include any relevant information such as time, location, order number, etc."
                      class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-text-sm tw-focus:tw-ring-2 tw-focus:tw-ring-red-500 tw-focus:tw-border-red-500 tw-transition-colors tw-resize-none tw-bg-white"></textarea>
            <div class="tw-text-xs tw-text-gray-500 tw-mt-2">
                <span id="charCount">0</span> / 1000 characters
            </div>
        </div>

        <!-- Optional: Order/Delivery Reference -->
        <div>
            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3 tw-flex tw-items-center">
                <i data-feather="hash" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-400"></i>
                Order/Delivery ID (Optional)
            </label>
            <input type="number" id="orderId" name="order_id" placeholder="Enter order or delivery ID if applicable"
                   class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-text-sm tw-focus:tw-ring-2 tw-focus:tw-ring-red-500 tw-focus:tw-border-red-500 tw-transition-colors tw-bg-white">
        </div>

        <!-- Contact Information -->
        <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4">
            <h3 class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-3 tw-flex tw-items-center">
                <i data-feather="phone" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-green-500"></i>
                Contact Information
            </h3>
            <div class="tw-text-sm tw-text-gray-600">
                <p class="tw-mb-2">We may need to contact you for more details about this issue.</p>
                <div class="tw-flex tw-items-center tw-space-x-4">
                    <div class="tw-flex tw-items-center tw-space-x-2">
                        <i data-feather="mail" class="tw-h-4 tw-w-4 tw-text-gray-400"></i>
                        <span><?= e($user->email ?? '') ?></span>
                    </div>
                    <div class="tw-flex tw-items-center tw-space-x-2">
                        <i data-feather="phone" class="tw-h-4 tw-w-4 tw-text-gray-400"></i>
                        <span><?= e($user->phone ?? 'Not provided') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="tw-pt-4 tw-border-t tw-border-gray-200">
            <button type="submit" id="submitBtn" 
                    class="tw-w-full tw-px-6 tw-py-4 tw-bg-gradient-to-r tw-from-red-600 tw-to-orange-600 tw-text-white tw-rounded-xl tw-text-sm tw-font-medium hover:tw-from-red-700 hover:tw-to-orange-700 tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                <i data-feather="send" class="tw-h-5 tw-w-5 tw-mr-2"></i>
                Submit Issue Report
            </button>
        </div>
    </form>
</div>

<!-- Help Section -->
<div class="tw-bg-blue-50 tw-rounded-2xl tw-p-6 tw-mt-6">
    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
        <i data-feather="help-circle" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-blue-600"></i>
        Need Immediate Help?
    </h3>
    <div class="tw-space-y-3">
        <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-white tw-rounded-xl">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <i data-feather="phone" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                <div>
                    <div class="tw-text-sm tw-font-medium tw-text-gray-900">Emergency Hotline</div>
                    <div class="tw-text-xs tw-text-gray-500">For urgent safety issues</div>
                </div>
            </div>
            <a href="tel:+237123456789" class="tw-px-4 tw-py-2 tw-bg-green-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-green-700 tw-transition-colors">
                Call Now
            </a>
        </div>
        
        <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-white tw-rounded-xl">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <i data-feather="message-circle" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                <div>
                    <div class="tw-text-sm tw-font-medium tw-text-gray-900">Live Chat</div>
                    <div class="tw-text-xs tw-text-gray-500">Get instant support</div>
                </div>
            </div>
            <button onclick="openLiveChat()" class="tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-transition-colors">
                Chat Now
            </button>
        </div>
    </div>
</div>

<script>
// Initialize Feather icons
feather.replace();

// Character counter for description
document.getElementById('description').addEventListener('input', function() {
    const charCount = this.value.length;
    document.getElementById('charCount').textContent = charCount;
    
    if (charCount > 1000) {
        this.value = this.value.substring(0, 1000);
        document.getElementById('charCount').textContent = '1000';
    }
});

// Form submission
document.getElementById('reportIssueForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i data-feather="loader" class="tw-h-5 tw-w-5 tw-mr-2 tw-animate-spin"></i>Submitting...';
    submitBtn.disabled = true;
    feather.replace();
    
    fetch('<?= url('/rider/report-issue') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Issue report submitted successfully!', 'success');
            // Reset form
            document.getElementById('reportIssueForm').reset();
            document.getElementById('charCount').textContent = '0';
        } else {
            showNotification(data.message || 'Failed to submit issue report', 'error');
        }
    })
    .catch(error => {
        console.error('Error submitting issue report:', error);
        showNotification('Failed to submit issue report. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        feather.replace();
    });
});

// Live chat function
function openLiveChat() {
    showNotification('Live chat feature coming soon!', 'info');
}

// Mobile-optimized notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-left-4 tw-right-4 tw-px-4 tw-py-3 tw-rounded-xl tw-shadow-lg tw-z-50 tw-transition-all tw-duration-300 tw-transform tw-translate-y-0 ${
        type === 'success' ? 'tw-bg-green-500 tw-text-white' : 
        type === 'error' ? 'tw-bg-red-500 tw-text-white' : 
        type === 'info' ? 'tw-bg-blue-500 tw-text-white' :
        'tw-bg-gray-500 tw-text-white'
    }`;
    
    notification.innerHTML = `
        <div class="tw-flex tw-items-center tw-space-x-3">
            <i data-feather="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="tw-w-5 tw-h-5 tw-flex-shrink-0"></i>
            <span class="tw-text-sm tw-font-medium tw-flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="tw-text-white tw-opacity-70 hover:tw-opacity-100">
                <i data-feather="x" class="tw-w-4 tw-h-4"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    feather.replace();
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.transform = 'translateY(-100%)';
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }, 4000);
}

// Add touch feedback to buttons
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('button, label');
    buttons.forEach(button => {
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        button.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>
