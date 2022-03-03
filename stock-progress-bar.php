<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Abnipes Stock Progress Bar
 * Plugin URI:        https://www.linkedin.com/in/abdulaha-islam/
 * Description:       Description of the plugin.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Abdulaha Islam
 * Author URI:        https://www.linkedin.com/in/abdulaha-islam/
 * Text Domain:       abnipes-stock-progress
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'Abnipes_WooCommerce_Stock_Progressbar' ) ) {

    final class Abnipes_WooCommerce_Stock_Progressbar {

        /**
         * Class Constructor
         */
        private function __construct() {
            // Define constants.
            $this->define_constants();

            // Initialize the action hooks.
            $this->init_actions();
        }

        /**
         * Initializes a singleton instance
         * 
         * @return \Abnipes_Woocommerce_Product_Slider
         */
        public static function init() {
            static $instance = false;

            if ( ! $instance ) {
                $instance = new self();
            }

            return $instance;
        }

        /**
         * Define the required plugin constants
         * 
         * @return void
         */
        public function define_constants() {
            define( 'ABNIPES_WCPPB_FILE', __FILE__ );
            define( 'ABNIPES_WCPPB_PATH', __DIR__ );
            define( 'ABNIPES_WCPPB_URL', plugins_url( '', ABNIPES_WCPPB_FILE ) );
            define( 'ABNIPES_WCPPB_ASSETS', ABNIPES_WCPPB_URL . '/assets' );
        }

        /**
         * Initialize WordPress action hooks
         *
         * @return void
         */
        public function init_actions() {
            add_action( 'plugins_loaded', array( $this, 'abnipes_stock_progress_textdomain' ) );
            add_action( 'init', array( $this, 'abnipes_woo_register_script' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'abnipes_woo_enqueue_style' ) );

            add_action('woocommerce_single_product_summary', array( $this, 'abnipes_stock_progress_bar' ), 25);
            remove_action('woocommerce_single_product_summary', array( $this, 'woocommerce_template_single_meta'), 40);

            add_action( 'wp_ajax_variation_ajax_action', array( $this, 'release_el_key') );
            add_action( 'wp_ajax_nopriv_variation_ajax_action', array( $this, 'release_el_key') );
        }


        /**
         * Load plugin textdomain.
         */
        function abnipes_stock_progress_textdomain() {
            load_plugin_textdomain( 'abnipes-stock-progress', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
        }

        /**
         * register javascript and css on initialization
         */
        function abnipes_woo_register_script() {

            wp_register_style( 'ab-progress-bar-css', plugins_url('/assets/css/style.css', __FILE__), false, '1.0.0', 'all');
            wp_register_style( 'ab-progress-style-css', plugins_url('/assets/css/progress-bar.css', __FILE__), false, '1.0.0', 'all');
            wp_register_style( 'ab-progress-responsive-css', plugins_url('/assets/css/responsive.css', __FILE__), false, '1.0.0', 'all');
            
            wp_register_script( 'ab-progress-bar-js', plugins_url('/assets/js/progress-bar.js', __FILE__), array('jquery'), '1.0.0' );
            wp_register_script( 'ab-app', plugins_url('/assets/js/app.js', __FILE__), array('jquery'), '1.0.0' );
        }

        /**
         * use the registered javascript and css above
        */
        function abnipes_woo_enqueue_style(){
            wp_enqueue_style('ab-progress-bar-css');
            wp_enqueue_style('ab-progress-style-css');
            wp_enqueue_style('ab-progress-responsive-css');


            wp_enqueue_script( 'ab-progress-bar-js' );
            wp_enqueue_script( 'ab-app' );
            $params = array(
                'ajax_url'   => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => wp_create_nonce( 'el_key_nonce' ),
            );
            wp_localize_script( 'ab-app', 'el_key_parms', $params );
        }

        public $stock;


        /**
         * release_el_key
        */
        function release_el_key() {
            if ( !DOING_AJAX ) {
                wp_die();
            } // Not Ajax

            $var_id = sanitize_text_field( $_POST['var_id'] );

            $variation_obj = new WC_Product_variation( $var_id );

            $stock = $variation_obj->get_stock_quantity();

            wp_send_json_success( $stock ); // todo: beautify output by array

            wp_die(); // this is required to terminate immediately and return a proper response
        }


        /**
         * Actual Output
        */
        function abnipes_stock_progress_bar($var_id) {

            $_product = wc_get_product(get_the_id());

            global $product;
            global $post;

            $bar_stock_product      = $product->get_stock_quantity();
            $bar_stock_sales        = get_post_meta($post->ID, 'total_sales', true);
            $product_total_quantity = $bar_stock_product + $bar_stock_sales;

            if( $product_total_quantity >= 1 ) {
                $percentage    = round($bar_stock_product / $product_total_quantity * 100);
            }
           
            if( is_product() && $product->is_type( 'simple' ) && $bar_stock_product >= 1 ) {
            ?>
                <div class="stock-info">
                    <div class="total-sold">Ordered: <span><?php echo $bar_stock_sales;  ?></span></div>
                    <div class="current-stock">Items available: <span><?php echo $bar_stock_product; ?></span></div>
                </div>
                <div class="progress-bar">
                    <div class="progress" data-percent="<?php echo $percentage; ?>"><span><?php // echo $percentage . '%'; ?></span></div>
                </div>
            <?php
            } elseif (is_product() && $product->is_type( 'variable' )) {


                // $var_id = (isset($_POST['var_id'])) ? $_POST['var_id'] : 'ID not found';
                // $var_id = (isset($_REQUEST['data'])) ? $_REQUEST['data'] : 'ID not found';

                // $var_id = $var_id;
                // echo $var_id;

                // $ba_stock_sales = get_post_meta($post->ID, 'total_sales', true);
                // echo $ba_stock_sales;

                ?>
                <div id="only-for-va" style="display:none">
                    <div class="stock-info">
                        <div class="total-sold">Ordered: <span></span></div>
                        <div class="current-stock">Items available: <span></span></div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" data-percent=""><span></span></div>
                    </div>
                </div>
                <?php 

            }
            ?>
           
            <?php

        }

    }

}

/**
 * Initializes the main plugin
 * 
 * @return \Abnipes_WooCommerce_Stock_Progressbar
 */
function Abnipes_WooCommerce_Stock_Progressbar() {
    return Abnipes_WooCommerce_Stock_Progressbar::init();
}

// kick-off the plugin
Abnipes_WooCommerce_Stock_Progressbar();
