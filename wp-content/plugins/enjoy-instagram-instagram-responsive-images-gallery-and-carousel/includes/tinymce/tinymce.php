<?php
/**
 * Class TinyMCE button handler
 */

if( ! defined( 'ABSPATH' ) ) {
    exit; // exit if call directly
}

final class tinymce_enjoyinstagram_button {

    /**
     * @var string
     */
    public $pluginname = 'enjoyinstagram';

    /**
     * @var int
     */
    public $internalVersion = 200;

    /**
     * the constructor
     *
     * @return void
     */
    public function __construct() {
        // Modify the version when tinyMCE plugins are changed.
        add_filter( 'tiny_mce_version', array( $this, 'change_tinymce_version' ) );
        // init process for button control
        add_action( 'init', array( $this, 'addbuttons' ) );
        // AJAX action
        add_action( 'wp_ajax_enjoyinstagram_tinymce', array( $this, 'ajax_tinymce' ) );
    }

    /**
     * Add plugin button filters
     *
     * @return void
     */
    public function addbuttons() {
        // Don't bother doing this stuff if the current user lacks permissions
        if( ! current_user_can('edit_posts') || ! current_user_can('edit_pages')
            || get_user_option( 'rich_editing' ) != 'true' ) {
            return;
        }

        add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ), 5 );
        add_filter( 'mce_buttons', array( $this, 'register_button' ), 5 );
    }

    /**
     * Regiter the plugin button
     *
     * @param array $buttons
     * @return array
     */
    public function register_button( $buttons ) {
        array_push( $buttons, 'separator', $this->pluginname );
        return $buttons;
    }

    /**
     * Add plugin
     *
     * @return $plugin_array
     */
    public function add_tinymce_plugin($plugin_array) {
        $plugin_array[$this->pluginname] = plugins_url( 'editor_plugin.js', __FILE__ );
        return $plugin_array;
    }

    /**
     * Force system to clear cache.
     * A different version will rebuild the cache
     *
     * @param integer $version
     * @return integer
     */
    public function change_tinymce_version( $version ) {
        $version = $version + $this->internalVersion;
        return $version;
    }

    /**
     * Call TinyMCE window content via admin-ajax
     *
     * @since 1.7.0
     * @return void
     */
    public function ajax_tinymce() {
        if( ! current_user_can('edit_pages') || ! current_user_can('edit_posts') ) {
            wp_die( __( 'You are not allowed to be here', 'enjoy-instagram-instagram-responsive-images-gallery-and-carousel' ) );
        }

        include_once( 'shortcode-tinymce-window.php');
        die();
    }

}

// Call it now
$tinymce_button = new tinymce_enjoyinstagram_button();
