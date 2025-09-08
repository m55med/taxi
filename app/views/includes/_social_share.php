<?php
// Note: This partial is intended to be used within an Alpine.js component
// where a `link` variable is defined, e.g., x-data="{ link: '...' }"
?>
<div class="flex items-center space-x-2 space-x-reverse mt-3" x-cloak>
    <p class="text-sm font-medium text-gray-600 ml-2">مشاركة:</p>
    
    <!-- WhatsApp -->
    <a :href="'https://wa.me/?text=' + encodeURIComponent(link)"
       target="_blank"
       class="group relative flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 hover:bg-green-500 text-gray-600 hover:text-white transition-all duration-300"
       title="Share on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Telegram -->
    <a :href="'https://t.me/share/url?url=' + encodeURIComponent(link) + '&text=Check out this link!'"
       target="_blank"
       class="group relative flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 hover:bg-sky-400 text-gray-600 hover:text-white transition-all duration-300"
       title="Share on Telegram">
        <i class="fab fa-telegram-plane"></i>
    </a>

    <!-- Facebook -->
    <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(link)"
       target="_blank"
       class="group relative flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 hover:bg-blue-600 text-gray-600 hover:text-white transition-all duration-300"
       title="Share on Facebook">
        <i class="fab fa-facebook-f"></i>
    </a>

    <!-- Twitter -->
    <a :href="'https://twitter.com/intent/tweet?url=' + encodeURIComponent(link) + '&text=Check out this link!'"
       target="_blank"
       class="group relative flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 hover:bg-sky-500 text-gray-600 hover:text-white transition-all duration-300"
       title="Share on Twitter">
        <i class="fab fa-twitter"></i>
    </a>

    <!-- LinkedIn -->
    <a :href="'https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent(link)"
       target="_blank"
       class="group relative flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 hover:bg-blue-700 text-gray-600 hover:text-white transition-all duration-300"
       title="Share on LinkedIn">
        <i class="fab fa-linkedin-in"></i>
    </a>
    
    <!-- Email -->
    <a :href="'mailto:?subject=Check out this link!&body=' + encodeURIComponent(link)"
       class="group relative flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-600 text-gray-600 hover:text-white transition-all duration-300"
       title="Share via Email">
        <i class="fas fa-envelope"></i>
    </a>
</div>
