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


/**
 * Load plugin textdomain.
 */
function abnipes_stock_progress_textdomain() {
    load_plugin_textdomain( 'abnipes-stock-progress', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

add_action('init', 'abnipes_stock_progress_textdomain');


// register javascript and css on initialization
function abnipes_woo_register_script() {

    wp_register_style( 'ab-progress-bar-css', plugins_url('/assets/css/style.css', __FILE__), false, '1.0.0', 'all');
    wp_register_style( 'ab-progress-style-css', plugins_url('/assets/css/progress-bar.css', __FILE__), false, '1.0.0', 'all');
    wp_register_style( 'ab-progress-responsive-css', plugins_url('/assets/css/responsive.css', __FILE__), false, '1.0.0', 'all');
    
    wp_register_script( 'ab-progress-bar-js', plugins_url('/assets/js/progress-bar.js', __FILE__), array('jquery'), '1.0.0' );
    wp_register_script( 'ab-app-js', plugins_url('/assets/js/app.js', __FILE__), array('jquery'), '1.0.0' );
}

add_action('init', 'abnipes_woo_register_script');


// use the registered javascript and css above
function abnipes_woo_enqueue_style(){
    wp_enqueue_style('ab-progress-bar-css');
    wp_enqueue_style('ab-progress-style-css');
    wp_enqueue_style('ab-progress-responsive-css');

    wp_enqueue_script( 'ab-progress-bar-js' );
    wp_enqueue_script( 'ab-app-js' );
}

add_action('wp_enqueue_scripts', 'abnipes_woo_enqueue_style');


function abnipes_stock_progress_bar() {
    global $product;
    global $post;
    $bar_stock_product = $product->get_stock_quantity();
    $bar_stock_sales = get_post_meta($post->ID, 'total_sales', true);
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
    } else if (is_product() && $product->is_type( 'variable' )) {
    ?>
        <!-- code about variable product quantity -->
    <?php
    }

}

add_action('woocommerce_single_product_summary', 'abnipes_stock_progress_bar', 25);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);







