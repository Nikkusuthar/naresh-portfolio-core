<?php
/**
 * Plugin Name: Naresh Portfolio Core
 * Description: Portfolio ke projects, shortcodes aur custom fields.
 * Version: 1.1
 * Author: Naresh Suthar
 */

// Seedha URL se is file ko kholne se rokta hai (security)
if (!defined('ABSPATH')) {
    exit;
}

class Naresh_Portfolio_Core {

    public function __construct() {
        add_action('init', array($this, 'register_project_cpt'));
        add_shortcode('latest_projects', array($this, 'latest_projects_shortcode'));
        add_action('add_meta_boxes', array($this, 'add_project_meta_box'));
        add_action('save_post_project', array($this, 'save_project_meta'));
    }

    // 1. Custom Post Type "Project" aur taxonomy "Technology"
    public function register_project_cpt() {
        register_post_type('project', array(
            'labels' => array(
                'name'          => 'Projects',
                'singular_name' => 'Project',
                'add_new_item'  => 'Add New Project',
                'edit_item'     => 'Edit Project',
                'all_items'     => 'All Projects',
            ),
            'public'       => true,
            'has_archive'  => true,
            'menu_icon'    => 'dashicons-portfolio',
            'supports'     => array('title', 'editor', 'excerpt', 'thumbnail'),
            'rewrite'      => array('slug' => 'projects'),
            'show_in_rest' => true,
        ));

        register_taxonomy('technology', 'project', array(
            'labels' => array(
                'name'          => 'Technologies',
                'singular_name' => 'Technology',
            ),
            'hierarchical' => false,
            'public'       => true,
            'show_in_rest' => true,
            'rewrite'      => array('slug' => 'technology'),
        ));
    }

    // 2. Shortcode: [latest_projects count="3"]
    public function latest_projects_shortcode($atts) {
        $atts = shortcode_atts(array(
            'count' => 3,
        ), $atts, 'latest_projects');

        $query = new WP_Query(array(
            'post_type'      => 'project',
            'posts_per_page' => (int) $atts['count'],
        ));

        if (!$query->have_posts()) {
            return '<p>Koi project nahi mila.</p>';
        }

        $output = '<ul class="shortcode-projects">';

        while ($query->have_posts()) {
            $query->the_post();
            $output .= '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
        }

        wp_reset_postdata();

        $output .= '</ul>';

        return $output;
    }

    // 3. Meta box: edit screen me "Project Details" box
    public function add_project_meta_box() {
        add_meta_box(
            'naresh_project_details',
            'Project Details',
            array($this, 'render_project_meta_box'),
            'project',
            'normal',
            'high'
        );
    }

    public function render_project_meta_box($post) {
        $url = get_post_meta($post->ID, '_project_url', true);

        wp_nonce_field('naresh_save_project', 'naresh_project_nonce');
        ?>
        <p>
            <label for="project_url"><strong>Project link (live ya GitHub)</strong></label><br>
            <input type="url" id="project_url" name="project_url"
                   value="<?php echo esc_attr($url); ?>"
                   style="width:100%;" placeholder="https://">
        </p>
        <?php
    }

    public function save_project_meta($post_id) {
        // Nonce check (fake request se bachav)
        if (!isset($_POST['naresh_project_nonce'])) {
            return;
        }
        $nonce = sanitize_text_field(wp_unslash($_POST['naresh_project_nonce']));
        if (!wp_verify_nonce($nonce, 'naresh_save_project')) {
            return;
        }

        // Autosave par kuch mat karo
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // User ko edit karne ki permission hai?
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Sanitize karke save karo
        if (isset($_POST['project_url'])) {
            update_post_meta(
                $post_id,
                '_project_url',
                esc_url_raw(wp_unslash($_POST['project_url']))
            );
        }
    }
}

new Naresh_Portfolio_Core();

// Activate hone par: CPT register karke permalinks refresh
function naresh_core_activate() {
    $plugin = new Naresh_Portfolio_Core();
    $plugin->register_project_cpt();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'naresh_core_activate');

// Deactivate hone par permalinks refresh
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');