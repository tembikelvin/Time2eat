<?php
/**
 * UI Components Trait
 * Reusable UI components with icons and accessibility
 */

trait UIComponents
{
    /**
     * Render Feather icon with accessibility
     */
    protected function featherIcon(string $name, array $options = []): string
    {
        $size = $options['size'] ?? 'tw-w-5 tw-h-5';
        $class = $options['class'] ?? '';
        $ariaLabel = $options['aria-label'] ?? null;
        $ariaHidden = $options['aria-hidden'] ?? true;
        
        $attributes = [
            'data-feather' => $name,
            'class' => trim("$size $class")
        ];
        
        if ($ariaLabel) {
            $attributes['aria-label'] = $ariaLabel;
            $attributes['role'] = 'img';
        } elseif ($ariaHidden) {
            $attributes['aria-hidden'] = 'true';
        }
        
        $attrs = array_map(fn($k, $v) => is_bool($v) ? ($v ? $k : '') : "$k=\"$v\"", 
                          array_keys($attributes), $attributes);
        
        return '<i ' . implode(' ', array_filter($attrs)) . '></i>';
    }
    
    /**
     * Render Material Symbol icon
     */
    protected function materialIcon(string $name, array $options = []): string
    {
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
        
        $attrs = array_map(fn($k, $v) => is_bool($v) ? ($v ? $k : '') : "$k=\"$v\"", 
                          array_keys($attributes), $attributes);
        
        return '<span ' . implode(' ', array_filter($attrs)) . '>' . $name . '</span>';
    }
    
    /**
     * Primary button with icon
     */
    protected function primaryButton(string $text, array $options = []): string
    {
        $href = $options['href'] ?? null;
        $icon = $options['icon'] ?? null;
        $iconType = $options['icon-type'] ?? 'feather';
        $class = $options['class'] ?? '';
        $id = $options['id'] ?? '';
        $ariaLabel = $options['aria-label'] ?? null;
        $disabled = $options['disabled'] ?? false;
        $type = $options['type'] ?? 'button';
        
        $baseClass = 'tw-btn-primary tw-min-h-[44px] tw-space-x-2';
        $fullClass = trim("$baseClass $class");
        
        $iconHtml = '';
        if ($icon) {
            $iconHtml = $iconType === 'material' 
                ? $this->materialIcon($icon, ['class' => 'tw-mr-2'])
                : $this->featherIcon($icon, ['class' => 'tw-mr-2']);
        }
        
        $attributes = [
            'class' => $fullClass,
            'aria-label' => $ariaLabel
        ];
        
        if ($id) $attributes['id'] = $id;
        if ($disabled) $attributes['disabled'] = true;
        
        if ($href) {
            $attributes['href'] = $href;
            $tag = 'a';
        } else {
            $attributes['type'] = $type;
            $tag = 'button';
        }
        
        $attrs = array_map(fn($k, $v) => is_bool($v) ? ($v ? $k : '') : "$k=\"$v\"", 
                          array_keys($attributes), array_values($attributes));
        
        return "<$tag " . implode(' ', array_filter($attrs)) . ">$iconHtml<span>$text</span></$tag>";
    }
    
    /**
     * Secondary button with icon
     */
    protected function secondaryButton(string $text, array $options = []): string
    {
        $options['class'] = 'tw-btn-secondary ' . ($options['class'] ?? '');
        return $this->primaryButton($text, $options);
    }
    
    /**
     * Outline button with icon
     */
    protected function outlineButton(string $text, array $options = []): string
    {
        $options['class'] = 'tw-btn-outline ' . ($options['class'] ?? '');
        return $this->primaryButton($text, $options);
    }
    
    /**
     * Loading spinner component
     */
    protected function loadingSpinner(array $options = []): string
    {
        $size = $options['size'] ?? 'tw-w-6 tw-h-6';
        $class = $options['class'] ?? '';
        $text = $options['text'] ?? 'Loading...';
        $inline = $options['inline'] ?? false;
        
        $spinnerClass = trim("tw-loading $size $class");
        $containerClass = $inline ? 'tw-inline-flex tw-items-center tw-space-x-2' : 'tw-flex tw-items-center tw-justify-center tw-space-x-2';
        
        return "
        <div class=\"$containerClass\" role=\"status\" aria-live=\"polite\">
            <div class=\"$spinnerClass\"></div>
            <span class=\"tw-sr-only\">$text</span>
        </div>";
    }
    
    /**
     * Modal component
     */
    protected function modal(string $id, string $title, string $content, array $options = []): string
    {
        $size = $options['size'] ?? 'tw-max-w-md';
        $closable = $options['closable'] ?? true;
        $footer = $options['footer'] ?? '';
        
        $closeButton = $closable ? "
        <button 
            type=\"button\" 
            class=\"tw-absolute tw-top-4 tw-right-4 tw-text-gray-400 hover:tw-text-gray-600 tw-p-2 tw-rounded-lg tw-min-h-[44px] tw-min-w-[44px] tw-flex tw-items-center tw-justify-center\"
            onclick=\"closeModal('$id')\"
            aria-label=\"Close modal\"
        >
            {$this->featherIcon('x', ['size' => 'tw-w-6 tw-h-6'])}
        </button>" : '';
        
        return "
        <div 
            id=\"$id\" 
            class=\"tw-fixed tw-inset-0 tw-z-50 tw-hidden tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-p-4\"
            role=\"dialog\"
            aria-modal=\"true\"
            aria-labelledby=\"{$id}-title\"
            onclick=\"if(event.target === this) closeModal('$id')\">
            <div class=\"tw-bg-white tw-rounded-xl tw-shadow-2xl $size tw-w-full tw-max-h-[90vh] tw-overflow-y-auto tw-relative\">
                $closeButton
                <div class=\"tw-p-6\">
                    <h2 id=\"{$id}-title\" class=\"tw-text-2xl tw-font-bold tw-text-gray-800 tw-mb-4 tw-pr-8\">$title</h2>
                    <div class=\"tw-text-gray-700\">$content</div>
                    " . ($footer ? "<div class=\"tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-200\">$footer</div>" : '') . "
                </div>
            </div>
        </div>";
    }
    
    /**
     * Alert/notification component
     */
    protected function alert(string $message, string $type = 'info', array $options = []): string
    {
        $dismissible = $options['dismissible'] ?? true;
        $icon = $options['icon'] ?? null;
        
        $typeClasses = [
            'success' => 'tw-bg-green-50 tw-border-green-200 tw-text-green-800',
            'error' => 'tw-bg-red-50 tw-border-red-200 tw-text-red-800',
            'warning' => 'tw-bg-yellow-50 tw-border-yellow-200 tw-text-yellow-800',
            'info' => 'tw-bg-blue-50 tw-border-blue-200 tw-text-blue-800'
        ];
        
        $typeIcons = [
            'success' => 'check-circle',
            'error' => 'alert-circle',
            'warning' => 'alert-triangle',
            'info' => 'info'
        ];
        
        $class = $typeClasses[$type] ?? $typeClasses['info'];
        $iconName = $icon ?? $typeIcons[$type];
        
        $dismissButton = $dismissible ? "
        <button 
            type=\"button\" 
            class=\"tw-ml-auto tw-text-current tw-opacity-70 hover:tw-opacity-100 tw-p-1 tw-rounded tw-min-h-[44px] tw-min-w-[44px] tw-flex tw-items-center tw-justify-center\"
            onclick=\"this.parentElement.remove()\"
            aria-label=\"Dismiss alert\"
        >
            {$this->featherIcon('x', ['size' => 'tw-w-4 tw-h-4'])}
        </button>" : '';
        
        return "
        <div class=\"tw-flex tw-items-start tw-space-x-3 tw-p-4 tw-border tw-rounded-lg $class\" role=\"alert\">
            {$this->featherIcon($iconName, ['size' => 'tw-w-5 tw-h-5', 'class' => 'tw-flex-shrink-0 tw-mt-0.5'])}
            <div class=\"tw-flex-1\">$message</div>
            $dismissButton
        </div>";
    }
    
    /**
     * Badge component
     */
    protected function badge(string $text, string $type = 'default', array $options = []): string
    {
        $icon = $options['icon'] ?? null;
        $size = $options['size'] ?? 'default';
        
        $typeClasses = [
            'default' => 'tw-bg-gray-100 tw-text-gray-800',
            'primary' => 'tw-bg-primary-100 tw-text-primary-800',
            'success' => 'tw-bg-green-100 tw-text-green-800',
            'error' => 'tw-bg-red-100 tw-text-red-800',
            'warning' => 'tw-bg-yellow-100 tw-text-yellow-800',
            'info' => 'tw-bg-blue-100 tw-text-blue-800'
        ];
        
        $sizeClasses = [
            'sm' => 'tw-px-2 tw-py-1 tw-text-xs',
            'default' => 'tw-px-3 tw-py-1 tw-text-sm',
            'lg' => 'tw-px-4 tw-py-2 tw-text-base'
        ];
        
        $class = $typeClasses[$type] ?? $typeClasses['default'];
        $sizeClass = $sizeClasses[$size] ?? $sizeClasses['default'];
        
        $iconHtml = $icon ? $this->featherIcon($icon, ['size' => 'tw-w-3 tw-h-3', 'class' => 'tw-mr-1']) : '';
        
        return "<span class=\"tw-inline-flex tw-items-center tw-font-medium tw-rounded-full $class $sizeClass\">$iconHtml$text</span>";
    }
    
    /**
     * Card component
     */
    protected function card(string $content, array $options = []): string
    {
        $title = $options['title'] ?? null;
        $footer = $options['footer'] ?? null;
        $class = $options['class'] ?? '';
        $padding = $options['padding'] ?? 'tw-p-6';
        
        $titleHtml = $title ? "<h3 class=\"tw-text-lg tw-font-semibold tw-text-gray-800 tw-mb-4\">$title</h3>" : '';
        $footerHtml = $footer ? "<div class=\"tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-200\">$footer</div>" : '';
        
        return "
        <div class=\"tw-card $class\">
            <div class=\"$padding\">
                $titleHtml
                $content
                $footerHtml
            </div>
        </div>";
    }
    
    /**
     * Rating stars component
     */
    protected function ratingStars(float $rating, array $options = []): string
    {
        $maxStars = $options['max'] ?? 5;
        $showNumber = $options['show-number'] ?? true;
        $size = $options['size'] ?? 'tw-w-4 tw-h-4';
        
        $stars = '';
        for ($i = 1; $i <= $maxStars; $i++) {
            $filled = $i <= $rating;
            $half = !$filled && $i - 0.5 <= $rating;
            
            $starClass = $filled ? 'tw-text-yellow-400 tw-fill-current' : 
                        ($half ? 'tw-text-yellow-400 tw-fill-current tw-opacity-50' : 'tw-text-gray-300');
            
            $stars .= $this->featherIcon('star', ['size' => $size, 'class' => $starClass]);
        }
        
        $numberHtml = $showNumber ? "<span class=\"tw-ml-2 tw-text-sm tw-text-gray-600\">" . number_format($rating, 1) . "</span>" : '';
        
        return "<div class=\"tw-flex tw-items-center\" role=\"img\" aria-label=\"Rating: $rating out of $maxStars stars\">$stars$numberHtml</div>";
    }
    
    /**
     * Progress bar component
     */
    protected function progressBar(int $percentage, array $options = []): string
    {
        $color = $options['color'] ?? 'primary';
        $size = $options['size'] ?? 'default';
        $showLabel = $options['show-label'] ?? true;
        
        $colorClasses = [
            'primary' => 'tw-bg-primary-600',
            'success' => 'tw-bg-green-600',
            'warning' => 'tw-bg-yellow-600',
            'error' => 'tw-bg-red-600'
        ];
        
        $sizeClasses = [
            'sm' => 'tw-h-2',
            'default' => 'tw-h-3',
            'lg' => 'tw-h-4'
        ];
        
        $colorClass = $colorClasses[$color] ?? $colorClasses['primary'];
        $sizeClass = $sizeClasses[$size] ?? $sizeClasses['default'];
        
        $labelHtml = $showLabel ? "<span class=\"tw-text-sm tw-text-gray-600 tw-ml-2\">$percentage%</span>" : '';
        
        return "
        <div class=\"tw-flex tw-items-center\" role=\"progressbar\" aria-valuenow=\"$percentage\" aria-valuemin=\"0\" aria-valuemax=\"100\">
            <div class=\"tw-flex-1 tw-bg-gray-200 tw-rounded-full $sizeClass\">
                <div class=\"$colorClass $sizeClass tw-rounded-full tw-transition-all tw-duration-300\" style=\"width: $percentage%\"></div>
            </div>
            $labelHtml
        </div>";
    }
}
