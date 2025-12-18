<?php
/**
 * Customer Dispute Creation Form
 */

$currentPage = 'disputes';
?>

<div class="tw-max-w-4xl tw-mx-auto tw-p-6">
    <!-- Header -->
    <div class="tw-mb-8">
        <div class="tw-flex tw-items-center tw-mb-4">
            <a href="/customer/orders" class="tw-text-blue-600 hover:tw-text-blue-800 tw-mr-4">
                <i data-feather="arrow-left" class="tw-h-5 tw-w-5"></i>
            </a>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Report an Issue</h1>
        </div>
        <p class="tw-text-gray-600">
            We're sorry to hear you're experiencing an issue with your order. Please provide details below and we'll work to resolve it quickly.
        </p>
    </div>

    <!-- Order Information -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6 tw-mb-6">
        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Order Information</h2>
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
            <div>
                <p class="tw-text-sm tw-text-gray-600">Order Number</p>
                <p class="tw-font-medium"><?= e($order['order_number'] ?? 'N/A') ?></p>
            </div>
            <div>
                <p class="tw-text-sm tw-text-gray-600">Order Date</p>
                <p class="tw-font-medium"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
            </div>
            <div>
                <p class="tw-text-sm tw-text-gray-600">Restaurant</p>
                <p class="tw-font-medium"><?= e($order['restaurant_name'] ?? 'N/A') ?></p>
            </div>
            <div>
                <p class="tw-text-sm tw-text-gray-600">Total Amount</p>
                <p class="tw-font-medium"><?= number_format($order['total_amount'] ?? 0, 0) ?> XAF</p>
            </div>
        </div>
    </div>

    <!-- Dispute Form -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <form id="disputeForm" enctype="multipart/form-data">
            <input type="hidden" name="order_id" value="<?= e($order['id']) ?>">

            <!-- Issue Type -->
            <div class="tw-mb-6">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    What type of issue are you experiencing? *
                </label>
                <select name="type" required class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    <option value="">Select issue type</option>
                    <option value="order_issue">Order Issue (Wrong items, missing items)</option>
                    <option value="quality_issue">Food Quality Issue</option>
                    <option value="delivery_issue">Delivery Issue (Late, damaged, not delivered)</option>
                    <option value="payment_issue">Payment Issue</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <!-- Priority -->
            <div class="tw-mb-6">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    Priority Level
                </label>
                <select name="priority" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    <option value="medium">Medium (Normal response time)</option>
                    <option value="low">Low (Non-urgent)</option>
                    <option value="high">High (Needs quick attention)</option>
                    <option value="urgent">Urgent (Immediate attention required)</option>
                </select>
            </div>

            <!-- Subject -->
            <div class="tw-mb-6">
                <label for="subject" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    Issue Summary *
                </label>
                <input 
                    type="text" 
                    id="subject" 
                    name="subject" 
                    required 
                    maxlength="200"
                    placeholder="Brief description of the issue"
                    class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500"
                >
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Maximum 200 characters</p>
            </div>

            <!-- Description -->
            <div class="tw-mb-6">
                <label for="description" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    Detailed Description *
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    required 
                    rows="6"
                    maxlength="2000"
                    placeholder="Please provide as much detail as possible about the issue you experienced. Include what happened, when it happened, and how it affected your order."
                    class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-shadow-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500"
                ></textarea>
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Maximum 2000 characters</p>
            </div>

            <!-- Evidence Upload -->
            <div class="tw-mb-6">
                <label for="evidence" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                    Evidence (Optional)
                </label>
                <div class="tw-border-2 tw-border-dashed tw-border-gray-300 tw-rounded-lg tw-p-6 tw-text-center">
                    <input 
                        type="file" 
                        id="evidence" 
                        name="evidence" 
                        accept="image/*,.pdf"
                        class="tw-hidden"
                        onchange="handleFileSelect(this)"
                    >
                    <div id="uploadArea" class="tw-cursor-pointer" onclick="document.getElementById('evidence').click()">
                        <i data-feather="upload" class="tw-h-8 tw-w-8 tw-text-gray-400 tw-mx-auto tw-mb-2"></i>
                        <p class="tw-text-sm tw-text-gray-600">Click to upload photos or documents</p>
                        <p class="tw-text-xs tw-text-gray-500">PNG, JPG, GIF or PDF up to 5MB</p>
                    </div>
                    <div id="fileInfo" class="tw-hidden tw-mt-4">
                        <div class="tw-flex tw-items-center tw-justify-center tw-text-sm tw-text-green-600">
                            <i data-feather="check-circle" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                            <span id="fileName"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="tw-flex tw-justify-end tw-space-x-4">
                <a href="/customer/orders" class="tw-px-6 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                    Cancel
                </a>
                <button 
                    type="submit" 
                    id="submitBtn"
                    class="tw-px-6 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-blue-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed"
                >
                    <span id="submitText">Submit Dispute</span>
                    <i id="submitSpinner" data-feather="loader" class="tw-h-4 tw-w-4 tw-ml-2 tw-animate-spin tw-hidden"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function handleFileSelect(input) {
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const uploadArea = document.getElementById('uploadArea');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (file.size > maxSize) {
            alert('File size must be less than 5MB');
            input.value = '';
            return;
        }
        
        fileName.textContent = file.name;
        fileInfo.classList.remove('tw-hidden');
        uploadArea.classList.add('tw-hidden');
    }
}

document.getElementById('disputeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitText.textContent = 'Submitting...';
    submitSpinner.classList.remove('tw-hidden');
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('/disputes/store', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            alert('Dispute submitted successfully! We will review it and get back to you soon.');
            window.location.href = '/customer/disputes';
        } else {
            // Show error message
            alert(result.message || 'Failed to submit dispute. Please try again.');
        }
        
    } catch (error) {
        console.error('Error submitting dispute:', error);
        alert('An error occurred. Please try again.');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitText.textContent = 'Submit Dispute';
        submitSpinner.classList.add('tw-hidden');
    }
});

// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>
