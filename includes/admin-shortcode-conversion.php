<?php

// Ensure this file is called from WordPress and not directly
if (! defined('ABSPATH')) {
    exit;
}

// Function to display the listing page with bulk actions and post type filtering
function display_embed_shortcode_list()
{
    global $wpdb;

    // Get all public post types to populate the dropdown
    $post_types = get_post_types(array('public' => true), 'objects');

    // Set the first available post type as the default
    reset($post_types);
    $post_type = key($post_types);

    if (isset($_POST['post_type_filter'])) {
        $post_type = sanitize_text_field($_POST['post_type_filter']);
    }

    // Number of posts per page (you can set this dynamically based on user preference)
    $posts_per_page = 10;

    // Get the current page number (for pagination)
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

    // Calculate the offset based on the current page
    $offset = ($paged - 1) * $posts_per_page;

    // Query to get posts with [embed] shortcode and the selected post type
    $query = $wpdb->prepare("
            SELECT SQL_CALC_FOUND_ROWS ID, post_title, post_type, post_date 
            FROM $wpdb->posts
            WHERE post_status = 'publish' 
            AND post_content LIKE '%[embed]%'
            AND post_type = %s
            ORDER BY post_date DESC
            LIMIT %d OFFSET %d
        ", $post_type, $posts_per_page, $offset);

    $results = $wpdb->get_results($query);

    // Get the total count of the posts
    $post_count = count($results);

    // Get the total number of matching posts (for pagination)
    $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");

    // Calculate the total number of pages
    $total_pages = ceil($total_posts / $posts_per_page);
    ?>
    <div class="wrap">
        <h1>Posts with Embed Shortcodes</h1>

        <form method="POST" action="">
            <?php wp_nonce_field('bulk_convert_nonce_action', '_wpnonce_bulk_convert'); ?>

            <div class="tablenav top">
                <!-- Bulk Action Section -->
                <div class="alignleft actions bulkactions">
                    <select name="bulk_action">
                        <option value="no_action">Bulk Actions</option>
                        <option value="convert_selected">Convert to Gutenberg</option>
                    </select>
                    <input type="submit" name="apply_bulk_action" class="button action" value="Apply">
                </div>

                <!-- Post Type Filter Section -->
                <div class="alignleft actions bulkactions">
                    <select name="post_type_filter">
                        <?php foreach ($post_types as $type) : ?>
                            <option value="<?php echo esc_attr($type->name); ?>" <?php selected($post_type, $type->name); ?>>
                                <?php echo esc_html($type->label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="submit" name="posttype_filter_btn" class="button action" value="Filter">
                </div>

                <!-- Page nav -->
                <div class="tablenav-pages one-page"><span class="displaying-num"><?php echo $post_count; ?> items</span>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <input id="cb-select-all" type="checkbox">
                            <label for="cb-select-all"><span class="screen-reader-text">Select All</span></label>
                        </td>
                        <th scope="col" class="manage-column column-title">Title</th>
                        <th scope="col" class="manage-column column-post_type">Post Type</th>
                        <th scope="col" class="manage-column column-post_date">Post Date</th>
                        <th scope="col" class="manage-column column-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    if ($results) {
                        foreach ($results as $post) {
                            ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="post_ids[]" value="<?php echo esc_attr($post->ID); ?>">
                                </th>
                                <td>
                                    <a href="<?php echo get_edit_post_link($post->ID); ?>">
                                        <?php echo esc_html($post->post_title); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($post->post_type); ?></td>
                                <td><?php echo esc_html($post->post_date); ?></td>
                                <td>
                                    <a href="<?php echo get_edit_post_link($post->ID); ?>">View</a> |
                                    <a
                                        href="<?php echo admin_url('admin.php?page=embed-shortcode-list&convert_post_id=' . $post->ID); ?>">Convert</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="5">No ' . $post_type . ' found with [embed] shortcodes.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <!-- Pagination Controls -->
            <div class="tablenav bottom">
                <?php
                // Generate pagination links
                $pagination_args = array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo; Previous'),
                    'next_text' => __('Next &raquo;'),
                    'total' => $total_pages,
                    'current' => $paged
                );
                echo paginate_links($pagination_args);
                ?>
            </div>
        </form>
    </div>
    <?php
}

// Add this to the admin menu
add_action('admin_menu', function () {
    add_menu_page('Convert [Embed] to Video', 'Convert [Embed]', 'manage_options', 'embed-shortcode-list', 'display_embed_shortcode_list');
});