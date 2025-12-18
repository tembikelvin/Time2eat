<?php
/**
 * Cameroonian & Bamenda African Art Patterns Component
 * Inspired by Toghu, Ndop cloth, and Bamileke traditional designs
 */
?>

<style>
/* Cameroonian Ndop Cloth Pattern - Indigo geometric designs */
.african-pattern-ndop {
    background-color: #1e3a8a;
    background-image: 
        repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255,255,255,.05) 10px, rgba(255,255,255,.05) 20px),
        repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(255,255,255,.03) 10px, rgba(255,255,255,.03) 20px);
}

/* Toghu-inspired colorful pattern */
.african-pattern-toghu {
    background: linear-gradient(135deg, #f97316 0%, #dc2626 25%, #eab308 50%, #16a34a 75%, #2563eb 100%);
    background-size: 400% 400%;
    animation: toghu-shift 15s ease infinite;
}

@keyframes toghu-shift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* Bamileke Geometric Pattern - Triangles and Diamonds */
.african-pattern-geometric {
    background-color: #dc2626;
    background-image: 
        linear-gradient(30deg, #f97316 12%, transparent 12.5%, transparent 87%, #f97316 87.5%, #f97316),
        linear-gradient(150deg, #f97316 12%, transparent 12.5%, transparent 87%, #f97316 87.5%, #f97316),
        linear-gradient(30deg, #f97316 12%, transparent 12.5%, transparent 87%, #f97316 87.5%, #f97316),
        linear-gradient(150deg, #f97316 12%, transparent 12.5%, transparent 87%, #f97316 87.5%, #f97316),
        linear-gradient(60deg, #eab30877 25%, transparent 25.5%, transparent 75%, #eab30877 75%, #eab30877),
        linear-gradient(60deg, #eab30877 25%, transparent 25.5%, transparent 75%, #eab30877 75%, #eab30877);
    background-size: 80px 140px;
    background-position: 0 0, 0 0, 40px 70px, 40px 70px, 0 0, 40px 70px;
}

/* Spider Web Pattern - Symbol of wisdom in Bamileke culture */
.african-pattern-spider {
    background: radial-gradient(circle at center, transparent 20%, rgba(249, 115, 22, 0.1) 20%, rgba(249, 115, 22, 0.1) 21%, transparent 21%),
                radial-gradient(circle at center, transparent 40%, rgba(249, 115, 22, 0.08) 40%, rgba(249, 115, 22, 0.08) 41%, transparent 41%),
                radial-gradient(circle at center, transparent 60%, rgba(249, 115, 22, 0.06) 60%, rgba(249, 115, 22, 0.06) 61%, transparent 61%),
                radial-gradient(circle at center, transparent 80%, rgba(249, 115, 22, 0.04) 80%, rgba(249, 115, 22, 0.04) 81%, transparent 81%);
    background-size: 100px 100px;
}

/* Zigzag Pattern - Traditional Grassfields design */
.african-pattern-zigzag {
    background: linear-gradient(135deg, #f97316 25%, transparent 25%) -50px 0,
                linear-gradient(225deg, #f97316 25%, transparent 25%) -50px 0,
                linear-gradient(315deg, #f97316 25%, transparent 25%),
                linear-gradient(45deg, #f97316 25%, transparent 25%);
    background-size: 100px 100px;
    background-color: #dc2626;
}

/* Cameroon Flag Colors Stripes */
.african-pattern-flag {
    background: linear-gradient(to right, 
        #16a34a 0%, #16a34a 33.33%, 
        #dc2626 33.33%, #dc2626 66.66%, 
        #eab308 66.66%, #eab308 100%);
}

/* Circular Motif - Double Gong symbol (royalty) */
.african-pattern-circles {
    background-color: #dc2626;
    background-image: 
        radial-gradient(circle at 25% 25%, rgba(249, 115, 22, 0.3) 2%, transparent 2%),
        radial-gradient(circle at 75% 75%, rgba(249, 115, 22, 0.3) 2%, transparent 2%),
        radial-gradient(circle at 25% 75%, rgba(234, 179, 8, 0.2) 3%, transparent 3%),
        radial-gradient(circle at 75% 25%, rgba(234, 179, 8, 0.2) 3%, transparent 3%);
    background-size: 60px 60px;
}

/* Animated Toghu Border */
.african-border-animated {
    position: relative;
    overflow: hidden;
}

.african-border-animated::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #16a34a, #dc2626, #eab308, #f97316);
    animation: border-slide 3s linear infinite;
}

@keyframes border-slide {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Decorative Corner Elements - Bamileke style */
.african-corner-tl,
.african-corner-tr,
.african-corner-bl,
.african-corner-br {
    position: absolute;
    width: 60px;
    height: 60px;
    opacity: 0.15;
}

.african-corner-tl {
    top: 0;
    left: 0;
    background: linear-gradient(135deg, #f97316 50%, transparent 50%);
}

.african-corner-tr {
    top: 0;
    right: 0;
    background: linear-gradient(225deg, #dc2626 50%, transparent 50%);
}

.african-corner-bl {
    bottom: 0;
    left: 0;
    background: linear-gradient(45deg, #eab308 50%, transparent 50%);
}

.african-corner-br {
    bottom: 0;
    right: 0;
    background: linear-gradient(315deg, #16a34a 50%, transparent 50%);
}

/* Floating African Symbols */
@keyframes float-symbol {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

.african-symbol-float {
    animation: float-symbol 6s ease-in-out infinite;
}

/* Ndop Indigo Overlay */
.african-overlay-ndop {
    background: linear-gradient(135deg, 
        rgba(30, 58, 138, 0.9) 0%, 
        rgba(30, 64, 175, 0.85) 50%, 
        rgba(37, 99, 235, 0.8) 100%);
}

/* Toghu Colorful Overlay */
.african-overlay-toghu {
    background: linear-gradient(135deg, 
        rgba(249, 115, 22, 0.15) 0%, 
        rgba(220, 38, 38, 0.15) 25%, 
        rgba(234, 179, 8, 0.15) 50%, 
        rgba(22, 163, 74, 0.15) 75%, 
        rgba(37, 99, 235, 0.15) 100%);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .african-corner-tl,
    .african-corner-tr,
    .african-corner-bl,
    .african-corner-br {
        width: 40px;
        height: 40px;
    }
}
</style>

<!-- SVG Symbols for Cameroonian Art -->
<svg style="display: none;">
    <!-- Spider Symbol - Wisdom -->
    <symbol id="african-spider" viewBox="0 0 100 100">
        <circle cx="50" cy="50" r="8" fill="currentColor"/>
        <line x1="50" y1="50" x2="20" y2="20" stroke="currentColor" stroke-width="2"/>
        <line x1="50" y1="50" x2="80" y2="20" stroke="currentColor" stroke-width="2"/>
        <line x1="50" y1="50" x2="20" y2="80" stroke="currentColor" stroke-width="2"/>
        <line x1="50" y1="50" x2="80" y2="80" stroke="currentColor" stroke-width="2"/>
        <line x1="50" y1="50" x2="10" y2="50" stroke="currentColor" stroke-width="2"/>
        <line x1="50" y1="50" x2="90" y2="50" stroke="currentColor" stroke-width="2"/>
        <line x1="50" y1="50" x2="50" y2="10" stroke="currentColor" stroke-width="2"/>
        <line x1="50" y1="50" x2="50" y2="90" stroke="currentColor" stroke-width="2"/>
        <circle cx="20" cy="20" r="4" fill="currentColor"/>
        <circle cx="80" cy="20" r="4" fill="currentColor"/>
        <circle cx="20" cy="80" r="4" fill="currentColor"/>
        <circle cx="80" cy="80" r="4" fill="currentColor"/>
    </symbol>
    
    <!-- Double Gong - Royalty/Authority -->
    <symbol id="african-gong" viewBox="0 0 100 100">
        <ellipse cx="35" cy="50" rx="20" ry="30" fill="none" stroke="currentColor" stroke-width="3"/>
        <ellipse cx="65" cy="50" rx="20" ry="30" fill="none" stroke="currentColor" stroke-width="3"/>
        <line x1="35" y1="20" x2="35" y2="10" stroke="currentColor" stroke-width="2"/>
        <line x1="65" y1="20" x2="65" y2="10" stroke="currentColor" stroke-width="2"/>
        <circle cx="35" cy="50" r="5" fill="currentColor"/>
        <circle cx="65" cy="50" r="5" fill="currentColor"/>
    </symbol>
    
    <!-- Frog Symbol - Fertility -->
    <symbol id="african-frog" viewBox="0 0 100 100">
        <ellipse cx="50" cy="60" rx="25" ry="20" fill="currentColor"/>
        <circle cx="40" cy="50" r="8" fill="currentColor"/>
        <circle cx="60" cy="50" r="8" fill="currentColor"/>
        <circle cx="38" cy="48" r="3" fill="white"/>
        <circle cx="58" cy="48" r="3" fill="white"/>
        <ellipse cx="25" cy="65" rx="8" ry="12" fill="currentColor"/>
        <ellipse cx="75" cy="65" rx="8" ry="12" fill="currentColor"/>
    </symbol>
    
    <!-- Geometric Diamond - Traditional pattern -->
    <symbol id="african-diamond" viewBox="0 0 100 100">
        <polygon points="50,10 90,50 50,90 10,50" fill="none" stroke="currentColor" stroke-width="3"/>
        <polygon points="50,30 70,50 50,70 30,50" fill="currentColor" opacity="0.5"/>
        <line x1="50" y1="10" x2="50" y2="90" stroke="currentColor" stroke-width="1"/>
        <line x1="10" y1="50" x2="90" y2="50" stroke="currentColor" stroke-width="1"/>
    </symbol>
    
    <!-- Zigzag Pattern Element -->
    <symbol id="african-zigzag" viewBox="0 0 100 20">
        <polyline points="0,10 10,0 20,10 30,0 40,10 50,0 60,10 70,0 80,10 90,0 100,10" 
                  fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
</svg>

