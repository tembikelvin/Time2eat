<?php
/**
 * Minimal Footer Component - Mobile First Design
 */
?>

<!-- Footer -->
<footer class="tw-bg-gray-900 tw-text-white tw-py-6">
    <div class="tw-container tw-mx-auto tw-px-4">
        <!-- Main Footer Content -->
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-justify-between tw-gap-4 md:tw-gap-6">

            <!-- Logo & Copyright -->
            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-gap-3 sm:tw-gap-4">
                <div class="tw-flex tw-items-center tw-gap-2">
                    <div class="tw-w-8 tw-h-8 tw-bg-gradient-to-br tw-from-orange-500 tw-to-red-600 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
                        <svg class="tw-w-5 tw-h-5 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <span class="tw-text-lg tw-font-black">
                        Time<span class="tw-text-orange-500">2</span>Eat
                    </span>
                </div>
                <span class="tw-text-gray-500 tw-text-xs">
                    © <?= date('Y') ?> All rights reserved.
                </span>
            </div>

            <!-- Quick Links -->
            <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-center tw-gap-4 sm:tw-gap-6">
                <a href="<?= url('/') ?>" class="tw-text-gray-400 hover:tw-text-orange-500 tw-transition-colors tw-text-sm">
                    Home
                </a>
                <a href="<?= url('/browse') ?>" class="tw-text-gray-400 hover:tw-text-orange-500 tw-transition-colors tw-text-sm">
                    Browse
                </a>
                <a href="<?= url('/about') ?>" class="tw-text-gray-400 hover:tw-text-orange-500 tw-transition-colors tw-text-sm">
                    About
                </a>
                <a href="<?= url('/contact') ?>" class="tw-text-gray-400 hover:tw-text-orange-500 tw-transition-colors tw-text-sm">
                    Contact
                </a>
            </div>

            <!-- Social Links & PWA -->
            <div class="tw-flex tw-items-center tw-gap-3">
                <a
                    href="#"
                    class="tw-w-8 tw-h-8 tw-bg-gray-800 hover:tw-bg-orange-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-transition-all"
                    aria-label="Facebook"
                >
                    <i data-feather="facebook" class="tw-w-4 tw-h-4"></i>
                </a>
                <a
                    href="#"
                    class="tw-w-8 tw-h-8 tw-bg-gray-800 hover:tw-bg-orange-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-transition-all"
                    aria-label="Instagram"
                >
                    <i data-feather="instagram" class="tw-w-4 tw-h-4"></i>
                </a>
                <a
                    href="#"
                    class="tw-w-8 tw-h-8 tw-bg-gray-800 hover:tw-bg-orange-500 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-transition-all"
                    aria-label="Twitter"
                >
                    <i data-feather="twitter" class="tw-w-4 tw-h-4"></i>
                </a>

                <!-- PWA Install Button -->
                <button
                    id="footer-install-btn"
                    class="tw-hidden tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-xs tw-font-bold tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-flex tw-items-center tw-gap-1.5"
                    aria-label="Install Time2Eat app"
                >
                    <i data-feather="download" class="tw-w-3.5 tw-h-3.5"></i>
                    Install
                </button>
            </div>
        </div>
    </div>
</footer>

