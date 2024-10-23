<?php

// Ensure this file is called from WordPress and not directly
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Convert selected post in bulk
 * @return void
 */
function handle_bulk_conversion()
{
    // Check if the form is submitted and posts are selected
    if (isset($_POST['apply_bulk_action']) && $_POST['bulk_action'] === 'convert_selected' && ! empty($_POST['post_ids'])) {

        // Verify nonce for security
        if (! isset($_POST['_wpnonce_bulk_convert']) || ! wp_verify_nonce($_POST['_wpnonce_bulk_convert'], 'bulk_convert_nonce_action')) {
            wp_die(__('Security check failed. Please try again.', 'convert-embed'));
        }

        // Sanitize post IDs
        $post_ids = array_map('intval', $_POST['post_ids']);

        foreach ($post_ids as $post_id) {
            convert_single_post_embed($post_id);
        }

        // Redirect to avoid re-submission
        wp_redirect(admin_url('admin.php?page=embed-shortcode-list&bulk_converted=' . count($post_ids)));
        exit;
    }
}

// Hook to run the bulk conversion when the form is submitted
add_action('admin_init', 'handle_bulk_conversion');


/**
 * Convert selected post on batches of 50
 * @return void
 */
function handle_bulk_conversion_in_batches()
{
    // Check if the bulk action form is submitted and posts are selected
    if (isset($_POST['apply_bulk_action']) && $_POST['bulk_action'] === 'convert_selected' && ! empty($_POST['post_ids'])) {
        $post_ids = array_map('intval', $_POST['post_ids']); // Sanitize post IDs

        // Set the batch size
        $batch_size = 50; // Number of posts to process per batch
        $total_posts = count($post_ids);
        $num_batches = ceil($total_posts / $batch_size);

        // Process posts in batches
        for ($i = 0; $i < $num_batches; $i++) {
            // Get the current batch of post IDs
            $batch_ids = array_slice($post_ids, $i * $batch_size, $batch_size);

            // Process each post in the current batch
            foreach ($batch_ids as $post_id) {
                convert_single_post_embed($post_id); // Reuse your existing conversion function
            }

            // Optional: Add a short delay to reduce server load (if necessary)
            // sleep(1); // Uncomment to introduce a 1-second delay between batches
        }

        // Redirect to avoid re-submission
        wp_redirect(admin_url('admin.php?page=embed-shortcode-list&bulk_converted=' . $total_posts));
        exit;
    }
}