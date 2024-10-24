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

    if (isset($_GET['post_type_filter'])) {
        $post_type = sanitize_text_field($_GET['post_type_filter']);
    }

    // Number of posts per page (you can set this dynamically based on user preference)
    $posts_per_page = 50;

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

    // Get the total count of the posts in a pagination
    $post_count = count($results);

    // Get the total number of matching posts
    $total_posts = $wpdb->get_var("SELECT FOUND_ROWS()");

    // Calculate the total number of pages
    $total_pages = ceil($total_posts / $posts_per_page);
    ?>
    <div class="wrap">
        <h1>Posts with Embed Shortcodes</h1>

        <form method="GET" action="">
            <input type="hidden" name="page" class="page_slug" value="embed-shortcode-list">
            <?php wp_nonce_field('_wpnonce', '_wpnonce'); ?>

            <div class="tablenav top">
                <!-- Bulk Action Section -->
                <div class="alignleft actions bulkactions">
                    <select name="action">
                        <option value="-1">Bulk Actions</option>
                        <option value="convert_selected">Convert [Embed]</option>
                    </select>
                    <input type="submit" id="action" name="apply_bulk_action" class="button action" value="Apply">
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
                <div class="tablenav-pages one-page">
                    <span class="displaying-num"><?php echo $total_posts; ?> items</span>
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
                                    <!-- <a
                                        href="<?php //echo admin_url('admin.php?page=embed-shortcode-list&convert_post_id=' . $post->ID); ?>">Convert</a> -->

                                    <a href="<?php echo add_query_arg('convert_post_id', $post->ID); ?>">Convert</a>
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

            <div class="tablenav bottom">
                <!-- Bulk Action Section -->
                <div class="alignleft actions bulkactions">
                    <select name="action">
                        <option value="-1">Bulk Actions</option>
                        <option value="convert_selected">Convert [Embed]</option>
                    </select>
                    <input type="submit" id="action" name="apply_bulk_action" class="button action" value="Apply">
                </div>

                <!-- Pagination -->
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo $total_posts; ?> items</span>
                    <span class="pagination-links">
                        <?php
                        if ($paged > 1) {
                            echo '<a class="first-page button" href="' . add_query_arg('paged', 1) . '"><span class="screen-reader-text">First page</span><span aria-hidden="true">«</span></a>';
                        } else {
                            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>';
                        }

                        // Previous page link
                        if ($paged > 1) {
                            echo '<a class="prev-page button" href="' . add_query_arg('paged', $paged - 1) . '"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">‹</span></a>';
                        } else {
                            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>';
                        }

                        // Current page and total pages
                        echo '<span class="screen-reader-text">Current Page</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">' . $paged . ' of <span class="total-pages">' . $total_pages . '</span></span></span>';

                        // Next page link
                        if ($paged < $total_pages) {
                            echo '<a class="next-page button" href="' . add_query_arg('paged', $paged + 1) . '"><span class="screen-reader-text">Next page</span><span aria-hidden="true">›</span></a>';
                        } else {
                            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>';
                        }

                        // Last page link
                        if ($paged < $total_pages) {
                            echo '<a class="last-page button" href="' . add_query_arg('paged', $total_pages) . '"><span class="screen-reader-text">Last page</span><span aria-hidden="true">»</span></a>';
                        } else {
                            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </form>
    </div>
    <?php
}