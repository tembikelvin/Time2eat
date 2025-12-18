<?php
/**
 * Icon Helper Class
 * Centralized icon management with accessibility
 */

class IconHelper
{
    /**
     * Common icon mappings for consistent usage
     */
    private static array $iconMap = [
        // Navigation
        'home' => 'home',
        'browse' => 'search',
        'menu' => 'menu',
        'close' => 'x',
        'back' => 'arrow-left',
        'forward' => 'arrow-right',
        'up' => 'chevron-up',
        'down' => 'chevron-down',
        'left' => 'chevron-left',
        'right' => 'chevron-right',
        
        // User & Auth
        'user' => 'user',
        'users' => 'users',
        'login' => 'log-in',
        'logout' => 'log-out',
        'register' => 'user-plus',
        'profile' => 'user',
        'settings' => 'settings',
        
        // Food & Restaurant
        'restaurant' => 'home',
        'food' => 'coffee',
        'cart' => 'shopping-cart',
        'order' => 'shopping-bag',
        'delivery' => 'truck',
        'pickup' => 'package',
        'kitchen' => 'chef-hat',
        'plate' => 'circle',
        
        // Actions
        'add' => 'plus',
        'remove' => 'minus',
        'edit' => 'edit-2',
        'delete' => 'trash-2',
        'save' => 'save',
        'cancel' => 'x',
        'confirm' => 'check',
        'view' => 'eye',
        'hide' => 'eye-off',
        
        // Status & Feedback
        'success' => 'check-circle',
        'error' => 'alert-circle',
        'warning' => 'alert-triangle',
        'info' => 'info',
        'loading' => 'loader',
        'star' => 'star',
        'heart' => 'heart',
        'like' => 'thumbs-up',
        'dislike' => 'thumbs-down',
        
        // Communication
        'phone' => 'phone',
        'email' => 'mail',
        'message' => 'message-circle',
        'chat' => 'message-square',
        'notification' => 'bell',
        'whatsapp' => 'message-circle',
        
        // Location & Maps
        'location' => 'map-pin',
        'map' => 'map',
        'directions' => 'navigation',
        'gps' => 'navigation-2',
        
        // Time & Calendar
        'time' => 'clock',
        'calendar' => 'calendar',
        'date' => 'calendar',
        'schedule' => 'clock',
        
        // Payment & Money
        'payment' => 'credit-card',
        'money' => 'dollar-sign',
        'wallet' => 'wallet',
        'cash' => 'banknote',
        
        // Media & Files
        'image' => 'image',
        'camera' => 'camera',
        'upload' => 'upload',
        'download' => 'download',
        'file' => 'file',
        'pdf' => 'file-text',
        
        // Social
        'facebook' => 'facebook',
        'twitter' => 'twitter',
        'instagram' => 'instagram',
        'share' => 'share-2',
        
        // System
        'dashboard' => 'grid',
        'analytics' => 'bar-chart-2',
        'reports' => 'file-text',
        'help' => 'help-circle',
        'support' => 'headphones',
        'security' => 'shield',
        'privacy' => 'lock',
        'terms' => 'file-text'
    ];
    
    /**
     * Material Icons mapping for specific use cases
     */
    private static array $materialIconMap = [
        'restaurant_menu' => 'restaurant_menu',
        'local_dining' => 'local_dining',
        'delivery_dining' => 'delivery_dining',
        'takeout_dining' => 'takeout_dining',
        'fastfood' => 'fastfood',
        'local_pizza' => 'local_pizza',
        'local_cafe' => 'local_cafe',
        'local_bar' => 'local_bar',
        'shopping_cart' => 'shopping_cart',
        'add_shopping_cart' => 'add_shopping_cart',
        'remove_shopping_cart' => 'remove_shopping_cart',
        'payment' => 'payment',
        'credit_card' => 'credit_card',
        'account_balance_wallet' => 'account_balance_wallet',
        'location_on' => 'location_on',
        'my_location' => 'my_location',
        'directions' => 'directions',
        'local_shipping' => 'local_shipping',
        'motorcycle' => 'motorcycle',
        'schedule' => 'schedule',
        'access_time' => 'access_time',
        'star' => 'star',
        'star_border' => 'star_border',
        'favorite' => 'favorite',
        'favorite_border' => 'favorite_border'
    ];
    
    /**
     * Get Feather icon with accessibility
     */
    public static function feather(string $name, array $options = []): string
    {
        $iconName = self::$iconMap[$name] ?? $name;
        $size = $options['size'] ?? 'tw-w-5 tw-h-5';
        $class = $options['class'] ?? '';
        $ariaLabel = $options['aria-label'] ?? null;
        $ariaHidden = $options['aria-hidden'] ?? true;
        
        $attributes = [
            'data-feather' => $iconName,
            'class' => trim("$size $class")
        ];
        
        if ($ariaLabel) {
            $attributes['aria-label'] = $ariaLabel;
            $attributes['role'] = 'img';
            unset($attributes['aria-hidden']);
        } elseif ($ariaHidden) {
            $attributes['aria-hidden'] = 'true';
        }
        
        $attrs = [];
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) $attrs[] = $key;
            } else {
                $attrs[] = "$key=\"$value\"";
            }
        }
        
        return '<i ' . implode(' ', $attrs) . '></i>';
    }
    
    /**
     * Get Material icon with accessibility
     */
    public static function material(string $name, array $options = []): string
    {
        $iconName = self::$materialIconMap[$name] ?? $name;
        $size = $options['size'] ?? 'tw-text-xl';
        $class = $options['class'] ?? '';
        $ariaLabel = $options['aria-label'] ?? null;
        $ariaHidden = $options['aria-hidden'] ?? true;
        
        $attributes = [
            'class' => trim("material-symbols-outlined $size $class")
        ];
        
        if ($ariaLabel) {
            $attributes['aria-label'] = $ariaLabel;
            $attributes['role'] = 'img';
        } elseif ($ariaHidden) {
            $attributes['aria-hidden'] = 'true';
        }
        
        $attrs = [];
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) $attrs[] = $key;
            } else {
                $attrs[] = "$key=\"$value\"";
            }
        }
        
        return '<span ' . implode(' ', $attrs) . '>' . $iconName . '</span>';
    }
    
    /**
     * Get icon button with proper accessibility
     */
    public static function button(string $icon, array $options = []): string
    {
        $type = $options['type'] ?? 'feather';
        $buttonType = $options['button-type'] ?? 'button';
        $class = $options['class'] ?? '';
        $ariaLabel = $options['aria-label'] ?? ucfirst(str_replace(['_', '-'], ' ', $icon));
        $onclick = $options['onclick'] ?? '';
        $href = $options['href'] ?? null;
        $id = $options['id'] ?? '';
        $disabled = $options['disabled'] ?? false;
        
        $baseClass = 'tw-p-2 tw-rounded-lg tw-transition-colors tw-min-h-[44px] tw-min-w-[44px] tw-flex tw-items-center tw-justify-center';
        $fullClass = trim("$baseClass $class");
        
        $iconHtml = $type === 'material' 
            ? self::material($icon, ['aria-hidden' => true])
            : self::feather($icon, ['aria-hidden' => true]);
        
        $attributes = [
            'class' => $fullClass,
            'aria-label' => $ariaLabel
        ];
        
        if ($id) $attributes['id'] = $id;
        if ($onclick) $attributes['onclick'] = $onclick;
        if ($disabled) $attributes['disabled'] = true;
        
        if ($href) {
            $attributes['href'] = $href;
            $tag = 'a';
        } else {
            $attributes['type'] = $buttonType;
            $tag = 'button';
        }
        
        $attrs = [];
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) $attrs[] = $key;
            } else {
                $attrs[] = "$key=\"$value\"";
            }
        }
        
        return "<$tag " . implode(' ', $attrs) . ">$iconHtml</$tag>";
    }
    
    /**
     * Get cart icon with item count badge
     */
    public static function cartWithBadge(int $count = 0, array $options = []): string
    {
        $class = $options['class'] ?? '';
        $size = $options['size'] ?? 'tw-w-6 tw-h-6';
        
        $cartIcon = self::feather('cart', ['size' => $size, 'class' => $class]);
        
        if ($count > 0) {
            $badgeText = $count > 99 ? '99+' : (string)$count;
            $badge = "<span class=\"tw-absolute tw--top-2 tw--right-2 tw-bg-red-500 tw-text-white tw-text-xs tw-font-bold tw-rounded-full tw-min-w-[20px] tw-h-5 tw-flex tw-items-center tw-justify-center tw-px-1\" aria-label=\"$count items in cart\">$badgeText</span>";
            
            return "<div class=\"tw-relative tw-inline-block\">$cartIcon$badge</div>";
        }
        
        return $cartIcon;
    }
    
    /**
     * Get notification icon with unread count
     */
    public static function notificationWithBadge(int $count = 0, array $options = []): string
    {
        $class = $options['class'] ?? '';
        $size = $options['size'] ?? 'tw-w-6 tw-h-6';
        
        $bellIcon = self::feather('notification', ['size' => $size, 'class' => $class]);
        
        if ($count > 0) {
            $badgeText = $count > 99 ? '99+' : (string)$count;
            $badge = "<span class=\"tw-absolute tw--top-1 tw--right-1 tw-bg-red-500 tw-text-white tw-text-xs tw-font-bold tw-rounded-full tw-min-w-[18px] tw-h-4 tw-flex tw-items-center tw-justify-center tw-px-1\" aria-label=\"$count unread notifications\">$badgeText</span>";
            
            return "<div class=\"tw-relative tw-inline-block\">$bellIcon$badge</div>";
        }
        
        return $bellIcon;
    }
    
    /**
     * Get status icon with color
     */
    public static function status(string $status, array $options = []): string
    {
        $size = $options['size'] ?? 'tw-w-4 tw-h-4';
        $showText = $options['show-text'] ?? false;
        
        $statusConfig = [
            'online' => ['icon' => 'success', 'color' => 'tw-text-green-500', 'text' => 'Online'],
            'offline' => ['icon' => 'error', 'color' => 'tw-text-gray-400', 'text' => 'Offline'],
            'busy' => ['icon' => 'warning', 'color' => 'tw-text-yellow-500', 'text' => 'Busy'],
            'away' => ['icon' => 'info', 'color' => 'tw-text-orange-500', 'text' => 'Away'],
            'open' => ['icon' => 'success', 'color' => 'tw-text-green-500', 'text' => 'Open'],
            'closed' => ['icon' => 'error', 'color' => 'tw-text-red-500', 'text' => 'Closed'],
            'preparing' => ['icon' => 'loading', 'color' => 'tw-text-blue-500', 'text' => 'Preparing'],
            'ready' => ['icon' => 'success', 'color' => 'tw-text-green-500', 'text' => 'Ready'],
            'delivered' => ['icon' => 'confirm', 'color' => 'tw-text-green-600', 'text' => 'Delivered']
        ];
        
        $config = $statusConfig[$status] ?? $statusConfig['offline'];
        $icon = self::feather($config['icon'], [
            'size' => $size, 
            'class' => $config['color'],
            'aria-label' => $config['text']
        ]);
        
        if ($showText) {
            return "<div class=\"tw-flex tw-items-center tw-space-x-2\">$icon<span class=\"tw-text-sm {$config['color']}\">{$config['text']}</span></div>";
        }
        
        return $icon;
    }
    
    /**
     * Get loading animation
     */
    public static function loading(array $options = []): string
    {
        $size = $options['size'] ?? 'tw-w-6 tw-h-6';
        $class = $options['class'] ?? '';
        $text = $options['text'] ?? 'Loading...';
        
        return "
        <div class=\"tw-flex tw-items-center tw-justify-center tw-space-x-2\" role=\"status\" aria-live=\"polite\">
            <div class=\"tw-animate-spin $size $class\">
                " . self::feather('loading', ['size' => $size, 'aria-hidden' => true]) . "
            </div>
            <span class=\"tw-sr-only\">$text</span>
        </div>";
    }
}
