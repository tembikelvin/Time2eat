<?php
/**
 * About Us Page - Redesigned
 * Modern, engaging about page with animations and interactive elements
 * Enhanced with Cameroonian & Bamenda African Art
 */

// Include African patterns component
require_once __DIR__ . '/../components/african-patterns.php';
?>

<!-- Hero Section with Gradient Background and African Art -->
<section class="tw-relative tw-bg-gradient-to-br tw-from-primary-600 tw-via-primary-700 tw-to-secondary-600 tw-py-24 tw-overflow-hidden">
    <!-- Toghu-inspired Colorful Pattern Overlay -->
    <div class="tw-absolute tw-inset-0 tw-opacity-20 african-pattern-toghu"></div>

    <!-- African Decorative Corners -->
    <div class="tw-absolute tw-inset-0 tw-z-5" aria-hidden="true">
        <div class="african-corner-tl" style="opacity: 0.3;"></div>
        <div class="african-corner-tr" style="opacity: 0.3;"></div>
        <div class="african-corner-bl" style="opacity: 0.3;"></div>
        <div class="african-corner-br" style="opacity: 0.3;"></div>
    </div>

    <!-- Floating African Symbols -->
    <div class="tw-absolute tw-inset-0 tw-z-5" aria-hidden="true">
        <div class="tw-absolute tw-top-32 tw-left-20 tw-w-20 tw-h-20 tw-text-yellow-300 african-symbol-float" style="opacity: 0.25;">
            <svg class="tw-w-full tw-h-full"><use href="#african-diamond"/></svg>
        </div>
        <div class="tw-absolute tw-top-48 tw-right-32 tw-w-24 tw-h-24 tw-text-white african-symbol-float" style="animation-delay: 1.5s; opacity: 0.25;">
            <svg class="tw-w-full tw-h-full"><use href="#african-gong"/></svg>
        </div>
        <div class="tw-absolute tw-bottom-40 tw-left-1/4 tw-w-16 tw-h-16 tw-text-green-300 african-symbol-float" style="animation-delay: 2.5s; opacity: 0.25;">
            <svg class="tw-w-full tw-h-full"><use href="#african-frog"/></svg>
        </div>
    </div>

    <div class="tw-container tw-mx-auto tw-px-4 tw-relative tw-z-10">
        <div class="tw-max-w-4xl tw-mx-auto tw-text-center">
            <div class="tw-inline-block tw-mb-6 tw-px-6 tw-py-2 tw-bg-white/20 tw-backdrop-blur-sm tw-rounded-full tw-text-white tw-font-semibold tw-text-sm">
                🎉 Bamenda's #1 Food Delivery Platform
            </div>
            <h1 class="tw-text-5xl md:tw-text-6xl tw-font-bold tw-text-white tw-mb-6 tw-leading-tight">
                Bringing Bamenda's<br>
                <span class="tw-text-yellow-300">Flavors to Your Door</span>
            </h1>
            <p class="tw-text-xl md:tw-text-2xl tw-text-white/90 tw-mb-10 tw-leading-relaxed">
                We're on a mission to connect food lovers with the best local restaurants,
                one delicious meal at a time.
            </p>
            <div class="tw-flex tw-flex-wrap tw-gap-4 tw-justify-center">
                <a href="<?= url('/browse') ?>" class="tw-inline-flex tw-items-center tw-px-8 tw-py-4 tw-bg-white tw-text-primary-600 tw-rounded-xl tw-font-bold tw-text-lg hover:tw-bg-gray-100 tw-transition-all tw-transform hover:tw-scale-105 tw-shadow-xl">
                    <svg class="tw-w-6 tw-h-6 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Order Now
                </a>
                <a href="#story" class="tw-inline-flex tw-items-center tw-px-8 tw-py-4 tw-bg-white/10 tw-backdrop-blur-sm tw-text-white tw-rounded-xl tw-font-bold tw-text-lg hover:tw-bg-white/20 tw-transition-all tw-border-2 tw-border-white/30">
                    <svg class="tw-w-6 tw-h-6 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Our Story
                </a>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="tw-absolute tw-bottom-8 tw-left-1/2 tw-transform -tw-translate-x-1/2 tw-animate-bounce">
        <svg class="tw-w-6 tw-h-6 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
        </svg>
    </div>
</section>

<!-- Stats Section with Animated Counters and African Border -->
<section class="tw-py-16 tw-bg-white tw-border-b tw-border-gray-100 tw-relative">
    <!-- Cameroon Flag Colors Top Border -->
    <div class="tw-absolute tw-top-0 tw-left-0 tw-right-0 tw-h-1 african-pattern-flag"></div>

    <div class="tw-container tw-mx-auto tw-px-4">
        <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-8 tw-max-w-5xl tw-mx-auto">
            <div class="tw-text-center tw-group">
                <div class="tw-text-5xl tw-font-bold tw-text-primary-600 tw-mb-2 tw-transition-transform tw-duration-300 group-hover:tw-scale-110">
                    <?= number_format($stats['restaurants'] ?? 50) ?>+
                </div>
                <div class="tw-text-gray-600 tw-font-medium">Restaurants</div>
            </div>
            <div class="tw-text-center tw-group">
                <div class="tw-text-5xl tw-font-bold tw-text-primary-600 tw-mb-2 tw-transition-transform tw-duration-300 group-hover:tw-scale-110">
                    <?= number_format($stats['orders'] ?? 10000) ?>+
                </div>
                <div class="tw-text-gray-600 tw-font-medium">Orders Delivered</div>
            </div>
            <div class="tw-text-center tw-group">
                <div class="tw-text-5xl tw-font-bold tw-text-primary-600 tw-mb-2 tw-transition-transform tw-duration-300 group-hover:tw-scale-110">
                    <?= number_format($stats['customers'] ?? 5000) ?>+
                </div>
                <div class="tw-text-gray-600 tw-font-medium">Happy Customers</div>
            </div>
            <div class="tw-text-center tw-group">
                <div class="tw-text-5xl tw-font-bold tw-text-primary-600 tw-mb-2 tw-transition-transform tw-duration-300 group-hover:tw-scale-110">
                    4.8★
                </div>
                <div class="tw-text-gray-600 tw-font-medium">Average Rating</div>
            </div>
        </div>
    </div>
</section>

<!-- Our Story Section -->
<section id="story" class="tw-py-20 tw-bg-gray-50">
    <div class="tw-container tw-mx-auto tw-px-4">
        <div class="tw-max-w-6xl tw-mx-auto">
            <div class="tw-text-center tw-mb-16">
                <h2 class="tw-text-4xl md:tw-text-5xl tw-font-bold tw-text-gray-900 tw-mb-4">Our Story</h2>
                <p class="tw-text-xl tw-text-gray-600 tw-max-w-3xl tw-mx-auto">
                    From a simple idea to Bamenda's favorite food delivery platform
                </p>
            </div>

            <div class="tw-grid md:tw-grid-cols-2 tw-gap-12 tw-items-center tw-mb-16">
                <div class="tw-space-y-6">
                    <div class="tw-bg-white tw-rounded-2xl tw-p-8 tw-shadow-lg tw-border tw-border-gray-100 hover:tw-shadow-xl tw-transition-shadow">
                        <div class="tw-flex tw-items-start tw-space-x-4">
                            <div class="tw-flex-shrink-0 tw-w-12 tw-h-12 tw-bg-primary-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center">
                                <svg class="tw-w-6 tw-h-6 tw-text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-2">The Beginning</h3>
                                <p class="tw-text-gray-600 tw-leading-relaxed">
                                    Founded in 2024, Time2Eat started with a simple vision: make it easier for people in Bamenda
                                    to enjoy their favorite local dishes without leaving home.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="tw-bg-white tw-rounded-2xl tw-p-8 tw-shadow-lg tw-border tw-border-gray-100 hover:tw-shadow-xl tw-transition-shadow">
                        <div class="tw-flex tw-items-start tw-space-x-4">
                            <div class="tw-flex-shrink-0 tw-w-12 tw-h-12 tw-bg-secondary-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center">
                                <svg class="tw-w-6 tw-h-6 tw-text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-2">Community First</h3>
                                <p class="tw-text-gray-600 tw-leading-relaxed">
                                    We're committed to supporting local restaurants and creating opportunities for riders,
                                    while delivering exceptional service to our customers.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="tw-bg-white tw-rounded-2xl tw-p-8 tw-shadow-lg tw-border tw-border-gray-100 hover:tw-shadow-xl tw-transition-shadow">
                        <div class="tw-flex tw-items-start tw-space-x-4">
                            <div class="tw-flex-shrink-0 tw-w-12 tw-h-12 tw-bg-green-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center">
                                <svg class="tw-w-6 tw-h-6 tw-text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-2">Innovation</h3>
                                <p class="tw-text-gray-600 tw-leading-relaxed">
                                    With real-time tracking, mobile payments, and a progressive web app,
                                    we're bringing cutting-edge technology to Bamenda's food scene.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw-relative">
                    <div class="tw-relative tw-rounded-2xl tw-overflow-hidden tw-shadow-2xl">
                        <img
                            src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&h=600&fit=crop"
                            alt="Local Cameroonian restaurant"
                            class="tw-w-full tw-h-[500px] tw-object-cover"
                        >
                        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-t tw-from-black/50 tw-to-transparent"></div>
                        <div class="tw-absolute tw-bottom-8 tw-left-8 tw-right-8 tw-text-white">
                            <p class="tw-text-2xl tw-font-bold tw-mb-2">Celebrating Local Flavors</p>
                            <p class="tw-text-white/90">Supporting Bamenda's vibrant food culture</p>
                        </div>
                    </div>

                    <!-- Floating Card -->
                    <div class="tw-absolute -tw-bottom-6 -tw-right-6 tw-bg-white tw-rounded-xl tw-shadow-2xl tw-p-6 tw-max-w-xs">
                        <div class="tw-flex tw-items-center tw-space-x-4">
                            <div class="tw-w-16 tw-h-16 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                                <svg class="tw-w-8 tw-h-8 tw-text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="tw-text-2xl tw-font-bold tw-text-gray-900">100%</div>
                                <div class="tw-text-sm tw-text-gray-600">Quality Assured</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Mission & Values Section -->
<section class="tw-py-20 tw-bg-white">
    <div class="tw-container tw-mx-auto tw-px-4">
        <div class="tw-max-w-6xl tw-mx-auto">
            <div class="tw-text-center tw-mb-16">
                <h2 class="tw-text-4xl md:tw-text-5xl tw-font-bold tw-text-gray-900 tw-mb-4">Our Mission & Values</h2>
                <p class="tw-text-xl tw-text-gray-600">The principles that guide everything we do</p>
            </div>

            <div class="tw-grid md:tw-grid-cols-3 tw-gap-8">
                <!-- Mission -->
                <div class="tw-group tw-relative tw-bg-gradient-to-br tw-from-primary-50 tw-to-primary-100 tw-rounded-2xl tw-p-8 tw-transition-all tw-duration-300 hover:tw-shadow-2xl hover:-tw-translate-y-2">
                    <div class="tw-absolute tw-top-0 tw-right-0 tw-w-32 tw-h-32 tw-bg-primary-200 tw-rounded-full tw-blur-3xl tw-opacity-50 group-hover:tw-opacity-70 tw-transition-opacity"></div>
                    <div class="tw-relative">
                        <div class="tw-w-16 tw-h-16 tw-bg-primary-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mb-6 tw-transform group-hover:tw-rotate-6 tw-transition-transform">
                            <svg class="tw-w-8 tw-h-8 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                        <h3 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-4">Our Mission</h3>
                        <p class="tw-text-gray-700 tw-leading-relaxed">
                            To make delicious, authentic Cameroonian food accessible to everyone in Bamenda through
                            innovative technology and exceptional service.
                        </p>
                    </div>
                </div>

                <!-- Quality -->
                <div class="tw-group tw-relative tw-bg-gradient-to-br tw-from-secondary-50 tw-to-secondary-100 tw-rounded-2xl tw-p-8 tw-transition-all tw-duration-300 hover:tw-shadow-2xl hover:-tw-translate-y-2">
                    <div class="tw-absolute tw-top-0 tw-right-0 tw-w-32 tw-h-32 tw-bg-secondary-200 tw-rounded-full tw-blur-3xl tw-opacity-50 group-hover:tw-opacity-70 tw-transition-opacity"></div>
                    <div class="tw-relative">
                        <div class="tw-w-16 tw-h-16 tw-bg-secondary-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mb-6 tw-transform group-hover:tw-rotate-6 tw-transition-transform">
                            <svg class="tw-w-8 tw-h-8 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                        </div>
                        <h3 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-4">Quality First</h3>
                        <p class="tw-text-gray-700 tw-leading-relaxed">
                            We partner only with restaurants that meet our high standards for food quality,
                            hygiene, and customer service excellence.
                        </p>
                    </div>
                </div>

                <!-- Community -->
                <div class="tw-group tw-relative tw-bg-gradient-to-br tw-from-green-50 tw-to-green-100 tw-rounded-2xl tw-p-8 tw-transition-all tw-duration-300 hover:tw-shadow-2xl hover:-tw-translate-y-2">
                    <div class="tw-absolute tw-top-0 tw-right-0 tw-w-32 tw-h-32 tw-bg-green-200 tw-rounded-full tw-blur-3xl tw-opacity-50 group-hover:tw-opacity-70 tw-transition-opacity"></div>
                    <div class="tw-relative">
                        <div class="tw-w-16 tw-h-16 tw-bg-green-600 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mb-6 tw-transform group-hover:tw-rotate-6 tw-transition-transform">
                            <svg class="tw-w-8 tw-h-8 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <h3 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-4">Community Focus</h3>
                        <p class="tw-text-gray-700 tw-leading-relaxed">
                            We're committed to supporting local businesses, creating jobs, and strengthening
                            the Bamenda food community.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="tw-py-20 tw-bg-gray-50">
    <div class="tw-container tw-mx-auto tw-px-4">
        <div class="tw-max-w-6xl tw-mx-auto">
            <div class="tw-text-center tw-mb-16">
                <h2 class="tw-text-4xl md:tw-text-5xl tw-font-bold tw-text-gray-900 tw-mb-4">Why Choose Time2Eat?</h2>
                <p class="tw-text-xl tw-text-gray-600">Experience the difference with our platform</p>
            </div>

            <div class="tw-grid md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-8">
                <!-- Feature 1 -->
                <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-md hover:tw-shadow-xl tw-transition-all tw-border tw-border-gray-100">
                    <div class="tw-w-14 tw-h-14 tw-bg-blue-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-mb-4">
                        <svg class="tw-w-7 tw-h-7 tw-text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-3">Fast Delivery</h3>
                    <p class="tw-text-gray-600 tw-leading-relaxed">
                        Average delivery time of 25 minutes. Track your order in real-time from kitchen to your door.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-md hover:tw-shadow-xl tw-transition-all tw-border tw-border-gray-100">
                    <div class="tw-w-14 tw-h-14 tw-bg-purple-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-mb-4">
                        <svg class="tw-w-7 tw-h-7 tw-text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-3">Safe & Secure</h3>
                    <p class="tw-text-gray-600 tw-leading-relaxed">
                        Secure payment options and verified restaurants. Your safety is our top priority.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-md hover:tw-shadow-xl tw-transition-all tw-border tw-border-gray-100">
                    <div class="tw-w-14 tw-h-14 tw-bg-yellow-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-mb-4">
                        <svg class="tw-w-7 tw-h-7 tw-text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-3">Best Prices</h3>
                    <p class="tw-text-gray-600 tw-leading-relaxed">
                        Competitive pricing with regular deals and discounts. Great food at great prices.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-md hover:tw-shadow-xl tw-transition-all tw-border tw-border-gray-100">
                    <div class="tw-w-14 tw-h-14 tw-bg-red-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-mb-4">
                        <svg class="tw-w-7 tw-h-7 tw-text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-3">Wide Selection</h3>
                    <p class="tw-text-gray-600 tw-leading-relaxed">
                        Choose from dozens of local restaurants and hundreds of delicious dishes.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-md hover:tw-shadow-xl tw-transition-all tw-border tw-border-gray-100">
                    <div class="tw-w-14 tw-h-14 tw-bg-indigo-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-mb-4">
                        <svg class="tw-w-7 tw-h-7 tw-text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-3">24/7 Support</h3>
                    <p class="tw-text-gray-600 tw-leading-relaxed">
                        Our customer support team is always ready to help you with any questions.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-md hover:tw-shadow-xl tw-transition-all tw-border tw-border-gray-100">
                    <div class="tw-w-14 tw-h-14 tw-bg-green-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-mb-4">
                        <svg class="tw-w-7 tw-h-7 tw-text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-mb-3">Mobile App</h3>
                    <p class="tw-text-gray-600 tw-leading-relaxed">
                        Progressive web app works on any device. Order from anywhere, anytime.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="tw-py-20 tw-bg-white">
    <div class="tw-container tw-mx-auto tw-px-4">
        <div class="tw-max-w-6xl tw-mx-auto">
            <div class="tw-text-center tw-mb-16">
                <h2 class="tw-text-4xl md:tw-text-5xl tw-font-bold tw-text-gray-900 tw-mb-4">What Our Customers Say</h2>
                <p class="tw-text-xl tw-text-gray-600">Real feedback from real people</p>
            </div>

            <div class="tw-grid md:tw-grid-cols-3 tw-gap-8">
                <!-- Testimonial 1 -->
                <div class="tw-bg-gray-50 tw-rounded-2xl tw-p-8 tw-border tw-border-gray-100">
                    <div class="tw-flex tw-items-center tw-mb-4">
                        <div class="tw-flex tw-text-yellow-400">
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                    </div>
                    <p class="tw-text-gray-700 tw-leading-relaxed tw-mb-6">
                        "Time2Eat has made my life so much easier! The delivery is always fast and the food arrives hot.
                        I love supporting local restaurants through this platform."
                    </p>
                    <div class="tw-flex tw-items-center">
                        <div class="tw-w-12 tw-h-12 tw-bg-primary-200 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-primary-700">
                            AM
                        </div>
                        <div class="tw-ml-4">
                            <div class="tw-font-bold tw-text-gray-900">Amina Mbah</div>
                            <div class="tw-text-sm tw-text-gray-600">Regular Customer</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="tw-bg-gray-50 tw-rounded-2xl tw-p-8 tw-border tw-border-gray-100">
                    <div class="tw-flex tw-items-center tw-mb-4">
                        <div class="tw-flex tw-text-yellow-400">
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                    </div>
                    <p class="tw-text-gray-700 tw-leading-relaxed tw-mb-6">
                        "As a restaurant owner, partnering with Time2Eat was the best decision.
                        We've reached so many new customers and the platform is easy to use."
                    </p>
                    <div class="tw-flex tw-items-center">
                        <div class="tw-w-12 tw-h-12 tw-bg-secondary-200 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-secondary-700">
                            PK
                        </div>
                        <div class="tw-ml-4">
                            <div class="tw-font-bold tw-text-gray-900">Peter Kum</div>
                            <div class="tw-text-sm tw-text-gray-600">Restaurant Owner</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="tw-bg-gray-50 tw-rounded-2xl tw-p-8 tw-border tw-border-gray-100">
                    <div class="tw-flex tw-items-center tw-mb-4">
                        <div class="tw-flex tw-text-yellow-400">
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                    </div>
                    <p class="tw-text-gray-700 tw-leading-relaxed tw-mb-6">
                        "The real-time tracking feature is amazing! I always know exactly when my food will arrive.
                        Customer service is also very responsive."
                    </p>
                    <div class="tw-flex tw-items-center">
                        <div class="tw-w-12 tw-h-12 tw-bg-green-200 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-font-bold tw-text-green-700">
                            SF
                        </div>
                        <div class="tw-ml-4">
                            <div class="tw-font-bold tw-text-gray-900">Sarah Fon</div>
                            <div class="tw-text-sm tw-text-gray-600">Food Enthusiast</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="tw-py-20 tw-bg-gradient-to-br tw-from-primary-600 tw-to-secondary-600 tw-relative tw-overflow-hidden">
    <!-- Background Pattern -->
    <div class="tw-absolute tw-inset-0 tw-opacity-10">
        <div class="tw-absolute tw-top-0 tw-left-0 tw-w-full tw-h-full" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>

    <div class="tw-container tw-mx-auto tw-px-4 tw-relative tw-z-10">
        <div class="tw-max-w-4xl tw-mx-auto tw-text-center">
            <h2 class="tw-text-4xl md:tw-text-5xl tw-font-bold tw-text-white tw-mb-6">
                Ready to Experience the Best Food Delivery in Bamenda?
            </h2>
            <p class="tw-text-xl tw-text-white/90 tw-mb-10 tw-leading-relaxed">
                Join thousands of satisfied customers and discover amazing local restaurants.
                Your next delicious meal is just a few clicks away!
            </p>

            <div class="tw-flex tw-flex-wrap tw-gap-4 tw-justify-center tw-mb-12">
                <a href="<?= url('/browse') ?>" class="tw-inline-flex tw-items-center tw-px-10 tw-py-5 tw-bg-white tw-text-primary-600 tw-rounded-xl tw-font-bold tw-text-lg hover:tw-bg-gray-100 tw-transition-all tw-transform hover:tw-scale-105 tw-shadow-2xl">
                    <svg class="tw-w-6 tw-h-6 tw-mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Start Ordering Now
                </a>
                <a href="<?= url('/contact') ?>" class="tw-inline-flex tw-items-center tw-px-10 tw-py-5 tw-bg-white/10 tw-backdrop-blur-sm tw-text-white tw-rounded-xl tw-font-bold tw-text-lg hover:tw-bg-white/20 tw-transition-all tw-border-2 tw-border-white/30">
                    <svg class="tw-w-6 tw-h-6 tw-mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Contact Us
                </a>
            </div>

            <!-- App Download Badges (Optional) -->
            <div class="tw-flex tw-flex-wrap tw-gap-4 tw-justify-center tw-items-center">
                <div class="tw-text-white/80 tw-text-sm tw-font-medium">Available on:</div>
                <div class="tw-flex tw-gap-3">
                    <div class="tw-bg-white/10 tw-backdrop-blur-sm tw-px-6 tw-py-3 tw-rounded-lg tw-border tw-border-white/20">
                        <div class="tw-flex tw-items-center tw-space-x-2">
                            <svg class="tw-w-6 tw-h-6 tw-text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                            </svg>
                            <span class="tw-text-white tw-font-semibold">iOS</span>
                        </div>
                    </div>
                    <div class="tw-bg-white/10 tw-backdrop-blur-sm tw-px-6 tw-py-3 tw-rounded-lg tw-border tw-border-white/20">
                        <div class="tw-flex tw-items-center tw-space-x-2">
                            <svg class="tw-w-6 tw-h-6 tw-text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 01-.61-.92V2.734a1 1 0 01.609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.198l2.807 1.626a1 1 0 010 1.73l-2.808 1.626L15.206 12l2.492-2.491zM5.864 2.658L16.802 8.99l-2.303 2.303-8.635-8.635z"/>
                            </svg>
                            <span class="tw-text-white tw-font-semibold">Android</span>
                        </div>
                    </div>
                    <div class="tw-bg-white/10 tw-backdrop-blur-sm tw-px-6 tw-py-3 tw-rounded-lg tw-border tw-border-white/20">
                        <div class="tw-flex tw-items-center tw-space-x-2">
                            <svg class="tw-w-6 tw-h-6 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                            </svg>
                            <span class="tw-text-white tw-font-semibold">Web</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer and closing tags are handled by the layout -->

