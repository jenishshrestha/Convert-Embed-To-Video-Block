<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


/**
 * Function to convert a single post when the "Convert" link is clicked
 * @param mixed $post_id
 * @return void
 */
function convert_single_post_embed($post_id)
{
    $post = get_post($post_id);

    if ($post && has_shortcode($post->post_content, 'embed')) {
        $post_content = $post->post_content;

        // Regex to find the entire
        //<!-- wp:shortcode -->...<!-- /wp:shortcode --> block
        $pattern = '/<!-- wp:shortcode -->(.*?)<!-- \/wp:shortcode -->/is';

        // Convert [embed] shortcodes inside wp:shortcode block
        if (preg_match_all($pattern, $post_content, $matches)) {
            foreach ($matches[1] as $shortcode_content) {

                if (preg_match('/\[embed\](.*?)\[\/embed\]/i', $shortcode_content, $embed_match)) {
                    $embed_url = trim($embed_match[1]);

                    $shortCode = generate_gutenberg_embed_block($embed_url);
                    $shortCodeType = $shortCode['type'];
                    $gutenberg_block = $shortCode['block'];

                    if ($shortCodeType === 'youtube' || $shortCodeType === 'vimeo') {
                        $post_content = str_replace("<!-- wp:shortcode -->$shortcode_content<!-- /wp:shortcode -->", $gutenberg_block, $post_content);
                    }
                }
            }

            // Update the post with the converted content
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $post_content,
            ));
        }
    }
}

/**
 * Generate Gutenberg Template
 * @param mixed $embed_url
 * @return array
 */
function generate_gutenberg_embed_block($embed_url)
{
    $provider_slug = '';

    // Detect if the URL is from Vimeo or YouTube
    if (strpos($embed_url, 'vimeo.com') !== false) {
        $provider_slug = 'vimeo';
    } elseif (strpos($embed_url, 'youtube.com') !== false || strpos($embed_url, 'youtu.be') !== false) {
        $provider_slug = 'youtube';
    }

    $is_provider_class = 'is-provider-' . $provider_slug;
    $wp_block_embed_class = 'wp-block-embed-' . $provider_slug;

    // Create the Gutenberg block with dynamic values for provider
    $gutenberg_block = "<!-- wp:embed {\"url\":\"$embed_url\",\"type\":\"video\",\"providerNameSlug\":\"$provider_slug\",\"responsive\":true,\"className\":\"wp-embed-aspect-16-9 wp-has-aspect-ratio\"} --><figure class=\"wp-block-embed is-type-video $is_provider_class $wp_block_embed_class wp-embed-aspect-16-9 wp-has-aspect-ratio\"><div class=\"wp-block-embed__wrapper\">$embed_url</div></figure><!-- /wp:embed -->";

    return [
        'type' => $provider_slug,
        'block' => $gutenberg_block
    ];
}

/**
 * Handle conversion when "Convert" link is clicked
 * @return void
 */
function handle_post_conversion()
{
    if (isset($_GET['convert_post_id']) && current_user_can('edit_posts')) {
        $post_id = intval($_GET['convert_post_id']);
        convert_single_post_embed($post_id);

        // Remove 'convert_post_id' from the URL and add 'conversion=success'
        $current_url = remove_query_arg('convert_post_id');  // Remove the 'convert_post_id' from URL
        $redirect_url = add_query_arg('conversion', 'success', $current_url);  // Add 'conversion=success'

        // Redirect to the modified URL
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('admin_init', 'handle_post_conversion');