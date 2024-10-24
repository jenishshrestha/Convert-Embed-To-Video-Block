<?php

// Hook to add the menu and submenus
add_action('admin_menu', function () {
    // Add parent menu page
    add_menu_page(
        'Convert [Embed] to Video',
        'Conversion',
        'manage_options',
        'embed-shortcode-list',
        'display_embed_shortcode_list',
        'dashicons-editor-table',
        99
    );

    // Rename the first (parent) submenu
    add_submenu_page(
        'embed-shortcode-list',         // Parent slug
        'All Posts with Embed Shortcodes',         // Subpage title (new name for the parent itself)
        '[Embed]',         // Submenu title (new name)
        'manage_options',               // Capability
        'embed-shortcode-list',         // Same slug as the parent to ensure it's the same page
        'display_embed_shortcode_list'  // Callback function to display content
    );

    // Add the subpage
    add_submenu_page(
        'embed-shortcode-list',
        'Embed Subpage Title',
        'Footnotes Lists',
        'manage_options',
        'embed-subpage',
        'display_footnotes_list'
    );
});