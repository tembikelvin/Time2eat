<?php
$title = $title ?? 'Go Online - Time2Eat';
$currentPage = 'unavailable';
$user = $user ?? null;
?>

<!-- Page header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">You're Currently Offline</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">
                Go online to start receiving delivery orders.
            </p>
        </div>
    </div>
</div>

<!-- Offline Status Card -->
<div class="tw-max-w-2xl tw-mx-auto">
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-overflow-hidden">
        <div class="tw-p-8 tw-text-center">
            <!-- Status Icon -->
            <div class="tw-mx-auto tw-h-24 tw-w-24 tw-bg-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-6">
                <i data-feather="power" class="tw-h-12 tw-w-12 tw-text-red-600"></i>
            </div>
            
            <!-- Status Message -->
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-4">You're Offline</h2>
            <p class="tw-text-gray-600 tw-mb-8 tw-max-w-md tw-mx-auto">
                You won't receive any delivery requests while you're offline. 
                Go online to start earning and help customers get their food delivered.
            </p>
            
            <!-- Go Online Button -->
            <button onclick="goOnline()" class="tw-inline-flex tw-items-center tw-px-8 tw-py-4 tw-bg-green-600 tw-text-white tw-font-semibold tw-rounded-lg tw-shadow-lg hover:tw-bg-green-700 tw-transition-all tw-duration-200 tw-transform hover:tw-scale-105">
                <i data-feather="power" class="tw-h-5 tw-w-5 tw-mr-3"></i>
                Go Online Now
            </button>
        </div>
        
        <!-- Benefits Section -->
        <div class="tw-bg-gray-50 tw-p-6">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4 tw-text-center">
                When you go online, you can:
            </h3>
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-p-2 tw-bg-blue-100 tw-rounded-full">
                        <i data-feather="dollar-sign" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                    </div>
                    <span class="tw-text-sm tw-text-gray-700">Start earning money</span>
                </div>
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-p-2 tw-bg-green-100 tw-rounded-full">
                        <i data-feather="map" class="tw-h-4 tw-w-4 tw-text-green-600"></i>
                    </div>
                    <span class="tw-text-sm tw-text-gray-700">See available orders</span>
                </div>
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-p-2 tw-bg-purple-100 tw-rounded-full">
                        <i data-feather="truck" class="tw-h-4 tw-w-4 tw-text-purple-600"></i>
                    </div>
                    <span class="tw-text-sm tw-text-gray-700">Accept delivery requests</span>
                </div>
                <div class="tw-flex tw-items-center tw-space-x-3">
                    <div class="tw-p-2 tw-bg-yellow-100 tw-rounded-full">
                        <i data-feather="users" class="tw-h-4 tw-w-4 tw-text-yellow-600"></i>
                    </div>
                    <span class="tw-text-sm tw-text-gray-700">Help customers</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="tw-mt-8 tw-max-w-4xl tw-mx-auto">
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-text-center">
            <div class="tw-h-12 tw-w-12 tw-bg-blue-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Flexible Hours</h3>
            <p class="tw-text-sm tw-text-gray-600">
                Work whenever you want. Set your own schedule and go online when it's convenient for you.
            </p>
        </div>
        
        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-text-center">
            <div class="tw-h-12 tw-w-12 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                <i data-feather="trending-up" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Earn More</h3>
            <p class="tw-text-sm tw-text-gray-600">
                Peak hours and busy areas offer higher earnings. The more you deliver, the more you earn.
            </p>
        </div>
        
        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-text-center">
            <div class="tw-h-12 tw-w-12 tw-bg-purple-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                <i data-feather="award" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">Build Reputation</h3>
            <p class="tw-text-sm tw-text-gray-600">
                Great service leads to better ratings and more delivery opportunities.
            </p>
        </div>
    </div>
</div>

<!-- Tips Section -->
<div class="tw-mt-8 tw-max-w-2xl tw-mx-auto">
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Tips for Success</h3>
        </div>
        <div class="tw-p-6">
            <div class="tw-space-y-4">
                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-p-1 tw-bg-blue-100 tw-rounded-full tw-mt-1">
                        <i data-feather="smartphone" class="tw-h-3 tw-w-3 tw-text-blue-600"></i>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-900">Keep your phone charged</p>
                        <p class="tw-text-xs tw-text-gray-600">
                            Make sure your phone has enough battery to receive orders and navigate.
                        </p>
                    </div>
                </div>
                
                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-p-1 tw-bg-green-100 tw-rounded-full tw-mt-1">
                        <i data-feather="map-pin" class="tw-h-3 tw-w-3 tw-text-green-600"></i>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-900">Stay in busy areas</p>
                        <p class="tw-text-xs tw-text-gray-600">
                            Position yourself near restaurants and busy areas to get more orders.
                        </p>
                    </div>
                </div>
                
                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-p-1 tw-bg-yellow-100 tw-rounded-full tw-mt-1">
                        <i data-feather="message-circle" class="tw-h-3 tw-w-3 tw-text-yellow-600"></i>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-900">Communicate with customers</p>
                        <p class="tw-text-xs tw-text-gray-600">
                            Keep customers informed about delays and be polite in all interactions.
                        </p>
                    </div>
                </div>
                
                <div class="tw-flex tw-items-start tw-space-x-3">
                    <div class="tw-p-1 tw-bg-purple-100 tw-rounded-full tw-mt-1">
                        <i data-feather="shield" class="tw-h-3 tw-w-3 tw-text-purple-600"></i>
                    </div>
                    <div>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-900">Handle food with care</p>
                        <p class="tw-text-xs tw-text-gray-600">
                            Keep food secure and at the right temperature during delivery.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function goOnline() {
    // Get CSRF token
    const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    
    fetch('<?= url('/rider/toggle-availability') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to available orders page
            window.location.href = '<?= url('/rider/available') ?>';
        } else {
            alert('Failed to go online: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error going online:', error);
        alert('Failed to go online. Please try again.');
    });
}

// Auto-redirect if user becomes available
setInterval(function() {
    fetch('<?= url('/api/rider/status') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.is_available) {
                window.location.href = '<?= url('/rider/dashboard') ?>';
            }
        })
        .catch(error => {
            // Ignore errors in status check
        });
}, 5000);
</script>
