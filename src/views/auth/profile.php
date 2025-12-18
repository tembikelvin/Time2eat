<?php
$title = $title ?? 'Profile - Time2Eat';
$user = $user ?? null;
?>

<!DOCTYPE html>
<html lang="en" class="tw-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            prefix: 'tw-',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fff7ed',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="tw-min-h-full tw-bg-gray-50">
    <!-- Header -->
    <header class="tw-bg-white tw-shadow-sm tw-border-b tw-border-gray-200">
        <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <div class="tw-flex tw-justify-between tw-items-center tw-py-4">
                <div class="tw-flex tw-items-center">
                    <a href="<?= url('/') ?>" class="tw-flex tw-items-center">
                        <div class="tw-h-8 tw-w-8 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                            <i data-feather="zap" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <h1 class="tw-ml-3 tw-text-xl tw-font-bold tw-text-gray-900">Time2Eat</h1>
                    </a>
                </div>
                <nav class="tw-flex tw-items-center tw-space-x-4">
                    <a href="<?= url('/browse') ?>" class="tw-text-gray-600 hover:tw-text-gray-900 tw-transition-colors">Browse</a>
                    <a href="<?= url('/cart') ?>" class="tw-text-gray-600 hover:tw-text-gray-900 tw-transition-colors">Cart</a>
                    <a href="<?= url('/logout') ?>" class="tw-text-gray-600 hover:tw-text-gray-900 tw-transition-colors">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="tw-max-w-4xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-8">
        <!-- Page Header -->
        <div class="tw-mb-8">
            <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900">Profile</h1>
            <p class="tw-mt-2 tw-text-gray-600">Manage your account settings and preferences</p>
        </div>

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">
            <!-- Profile Info -->
            <div class="lg:tw-col-span-1">
                <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                    <div class="tw-text-center">
                        <div class="tw-mx-auto tw-h-20 tw-w-20 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-500 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mb-4">
                            <i data-feather="user" class="tw-h-10 tw-w-10 tw-text-white"></i>
                        </div>
                        <h2 class="tw-text-xl tw-font-semibold tw-text-gray-900"><?= e($user['name']) ?></h2>
                        <p class="tw-text-gray-600"><?= e($user['email']) ?></p>
                        <span class="tw-inline-block tw-mt-2 tw-px-3 tw-py-1 tw-bg-green-100 tw-text-green-800 tw-text-sm tw-font-medium tw-rounded-full">
                            <?= e(ucfirst($user['role'])) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Settings -->
            <div class="lg:tw-col-span-2 tw-space-y-6">
                <!-- Delivery Addresses -->
                <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
                            <i data-feather="map-pin" class="tw-h-5 tw-w-5 tw-mr-2"></i>
                            Delivery Addresses
                        </h3>
                        <button onclick="addAddress()" class="tw-bg-primary-500 hover:tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-colors tw-inline-flex tw-items-center tw-space-x-2">
                            <i data-feather="plus" class="tw-w-4 tw-h-4"></i>
                            <span class="tw-whitespace-nowrap">Add Address</span>
                        </button>
                    </div>
                    
                    <div id="addresses-list" class="tw-space-y-3">
                        <!-- Sample addresses -->
                        <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4">
                            <div class="tw-flex tw-items-start tw-justify-between">
                                <div class="tw-flex-1">
                                    <div class="tw-flex tw-items-center tw-mb-2">
                                        <h4 class="tw-font-medium tw-text-gray-900">Home</h4>
                                        <span class="tw-ml-2 tw-px-2 tw-py-1 tw-bg-green-100 tw-text-green-800 tw-text-xs tw-font-medium tw-rounded">Default</span>
                                    </div>
                                    <p class="tw-text-sm tw-text-gray-600">123 Main Street, Bamenda, Northwest Region</p>
                                    <p class="tw-text-sm tw-text-gray-600">Phone: +237 6XX XXX XXX</p>
                                </div>
                                <div class="tw-flex tw-space-x-2">
                                    <button class="tw-text-primary-600 hover:tw-text-primary-700 tw-text-sm tw-font-medium">Edit</button>
                                    <button class="tw-text-red-600 hover:tw-text-red-700 tw-text-sm tw-font-medium">Delete</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4">
                            <div class="tw-flex tw-items-start tw-justify-between">
                                <div class="tw-flex-1">
                                    <div class="tw-flex tw-items-center tw-mb-2">
                                        <h4 class="tw-font-medium tw-text-gray-900">Office</h4>
                                    </div>
                                    <p class="tw-text-sm tw-text-gray-600">456 Business District, Bamenda, Northwest Region</p>
                                    <p class="tw-text-sm tw-text-gray-600">Phone: +237 6XX XXX XXX</p>
                                </div>
                                <div class="tw-flex tw-space-x-2">
                                    <button class="tw-text-primary-600 hover:tw-text-primary-700 tw-text-sm tw-font-medium">Edit</button>
                                    <button class="tw-text-red-600 hover:tw-text-red-700 tw-text-sm tw-font-medium">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
                            <i data-feather="credit-card" class="tw-h-5 tw-w-5 tw-mr-2"></i>
                            Payment Methods
                        </h3>
                        <button onclick="addPaymentMethod()" class="tw-bg-primary-500 hover:tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-colors tw-inline-flex tw-items-center tw-space-x-2">
                            <i data-feather="plus" class="tw-w-4 tw-h-4"></i>
                            <span class="tw-whitespace-nowrap">Add Payment</span>
                        </button>
                    </div>
                    
                    <div id="payment-methods-list" class="tw-space-y-3">
                        <!-- Sample payment methods -->
                        <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4">
                            <div class="tw-flex tw-items-center tw-justify-between">
                                <div class="tw-flex tw-items-center">
                                    <i data-feather="credit-card" class="tw-h-6 tw-w-6 tw-text-gray-400 tw-mr-3"></i>
                                    <div>
                                        <h4 class="tw-font-medium tw-text-gray-900">Visa ending in 1234</h4>
                                        <p class="tw-text-sm tw-text-gray-600">Expires 12/25</p>
                                    </div>
                                </div>
                                <div class="tw-flex tw-space-x-2">
                                    <span class="tw-px-2 tw-py-1 tw-bg-green-100 tw-text-green-800 tw-text-xs tw-font-medium tw-rounded">Default</span>
                                    <button class="tw-text-primary-600 hover:tw-text-primary-700 tw-text-sm tw-font-medium">Edit</button>
                                    <button class="tw-text-red-600 hover:tw-text-red-700 tw-text-sm tw-font-medium">Delete</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4">
                            <div class="tw-flex tw-items-center tw-justify-between">
                                <div class="tw-flex tw-items-center">
                                    <i data-feather="smartphone" class="tw-h-6 tw-w-6 tw-text-gray-400 tw-mr-3"></i>
                                    <div>
                                        <h4 class="tw-font-medium tw-text-gray-900">Mobile Money</h4>
                                        <p class="tw-text-sm tw-text-gray-600">Orange Money - +237 6XX XXX XXX</p>
                                    </div>
                                </div>
                                <div class="tw-flex tw-space-x-2">
                                    <button class="tw-text-primary-600 hover:tw-text-primary-700 tw-text-sm tw-font-medium">Edit</button>
                                    <button class="tw-text-red-600 hover:tw-text-red-700 tw-text-sm tw-font-medium">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order History -->
                <div class="tw-bg-white tw-rounded-lg tw-shadow-md tw-p-6">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                        <i data-feather="package" class="tw-h-5 tw-w-5 tw-mr-2"></i>
                        Recent Orders
                    </h3>
                    
                    <div class="tw-space-y-3">
                        <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4">
                            <div class="tw-flex tw-items-center tw-justify-between">
                                <div>
                                    <h4 class="tw-font-medium tw-text-gray-900">Order #12345</h4>
                                    <p class="tw-text-sm tw-text-gray-600">Mama Grace Kitchen • 2 items</p>
                                    <p class="tw-text-sm tw-text-gray-500">Ordered on Dec 15, 2024</p>
                                </div>
                                <div class="tw-text-right">
                                    <p class="tw-font-semibold tw-text-gray-900">XAF 4,500</p>
                                    <span class="tw-px-2 tw-py-1 tw-bg-green-100 tw-text-green-800 tw-text-xs tw-font-medium tw-rounded">Delivered</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4">
                            <div class="tw-flex tw-items-center tw-justify-between">
                                <div>
                                    <h4 class="tw-font-medium tw-text-gray-900">Order #12344</h4>
                                    <p class="tw-text-sm tw-text-gray-600">Quick Bites Express • 1 item</p>
                                    <p class="tw-text-sm tw-text-gray-500">Ordered on Dec 14, 2024</p>
                                </div>
                                <div class="tw-text-right">
                                    <p class="tw-font-semibold tw-text-gray-900">XAF 2,200</p>
                                    <span class="tw-px-2 tw-py-1 tw-bg-yellow-100 tw-text-yellow-800 tw-text-xs tw-font-medium tw-rounded">In Progress</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tw-mt-4 tw-text-center">
                        <a href="<?= url('/orders') ?>" class="tw-text-primary-600 hover:tw-text-primary-700 tw-font-medium tw-transition-colors">
                            View All Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Address Modal -->
    <div id="add-address-modal" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-z-50 tw-hidden">
        <div class="tw-flex tw-items-center tw-justify-center tw-min-h-screen tw-p-4">
            <div class="tw-bg-white tw-rounded-lg tw-shadow-xl tw-w-full tw-max-w-md">
                <div class="tw-p-6">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Add New Address</h3>
                        <button onclick="closeAddressModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                            <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                        </button>
                    </div>
                    
                    <form id="add-address-form" class="tw-space-y-4">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Label</label>
                            <input type="text" name="label" placeholder="Home, Office, etc." class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Address Line 1</label>
                            <input type="text" name="address_line_1" placeholder="Street address" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Address Line 2</label>
                            <input type="text" name="address_line_2" placeholder="Apartment, suite, etc." class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                        <div class="tw-grid tw-grid-cols-2 tw-gap-4">
                            <div>
                                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">City</label>
                                <input type="text" name="city" placeholder="Bamenda" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                            </div>
                            <div>
                                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">State</label>
                                <input type="text" name="state" placeholder="Northwest Region" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                            </div>
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Phone Number</label>
                            <input type="tel" name="phone" placeholder="+237 6XX XXX XXX" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                        <div class="tw-flex tw-items-center">
                            <input type="checkbox" name="is_default" id="is_default" class="tw-mr-2">
                            <label for="is_default" class="tw-text-sm tw-text-gray-700">Set as default address</label>
                        </div>
                        
                        <div class="tw-flex tw-space-x-3 tw-pt-4">
                            <button type="button" onclick="closeAddressModal()" class="tw-flex-1 tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-text-gray-700 tw-rounded-lg hover:tw-bg-gray-50 tw-transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-primary-500 tw-text-white tw-rounded-lg hover:tw-bg-primary-600 tw-transition-colors">
                                Add Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Feather icons
        feather.replace();

        // Address management functions
        function addAddress() {
            document.getElementById('add-address-modal').classList.remove('tw-hidden');
        }

        function closeAddressModal() {
            document.getElementById('add-address-modal').classList.add('tw-hidden');
        }

        function addPaymentMethod() {
            alert('Payment method management will be implemented soon!');
        }

        // Handle add address form
        document.getElementById('add-address-form').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Address added successfully! (Demo functionality)');
            closeAddressModal();
        });
    </script>
</body>
</html>
