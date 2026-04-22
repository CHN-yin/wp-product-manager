<?php
/**
 * Plugin Name: WP Product Manager
 * Description: 自定义产品管理插件，支持产品类型、分类、自定义字段、模板等完整功能。
 * Version:     1.0.0
 * Author:      CHNyin
 * Text Domain: wp-product-manager
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 插件常量
define( 'WPPM_VERSION',     '1.0.0' );
define( 'WPPM_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'WPPM_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'WPPM_PLUGIN_FILE', __FILE__ );

// 加载子模块
require_once WPPM_PLUGIN_DIR . 'includes/class-post-type.php';
require_once WPPM_PLUGIN_DIR . 'includes/class-taxonomy.php';
require_once WPPM_PLUGIN_DIR . 'includes/class-meta-boxes.php';
require_once WPPM_PLUGIN_DIR . 'includes/class-template-loader.php';
require_once WPPM_PLUGIN_DIR . 'includes/class-product.php';
require_once WPPM_PLUGIN_DIR . 'includes/class-admin-columns.php';
require_once WPPM_PLUGIN_DIR . 'includes/functions.php';

/**
 * 主插件类
 */
class WP_Product_Manager {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action( 'plugins_loaded',  [ $this, 'load_textdomain' ] );
        add_action( 'init',            [ $this, 'init' ] );
        add_action( 'wp_enqueue_scripts',    [ $this, 'frontend_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
    }

    public function init() {
        WPPM_Post_Type::register();
        WPPM_Taxonomy::register();
        WPPM_Meta_Boxes::init();
        WPPM_Template_Loader::init();
        WPPM_Admin_Columns::init();
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'wp-product-manager',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );
    }

    public function frontend_assets() {
        if ( is_singular( 'wppm_product' ) || is_post_type_archive( 'wppm_product' ) || is_tax( 'wppm_product_cat' ) ) {
            wp_enqueue_style(
                'wppm-frontend',
                WPPM_PLUGIN_URL . 'assets/css/frontend.css',
                [],
                WPPM_VERSION
            );
        }
    }

    public function admin_assets( $hook ) {
        $screen = get_current_screen();
        if ( $screen && $screen->post_type === 'wppm_product' ) {
            wp_enqueue_style(
                'wppm-admin',
                WPPM_PLUGIN_URL . 'assets/css/admin.css',
                [],
                WPPM_VERSION
            );
            wp_enqueue_script(
                'wppm-admin',
                WPPM_PLUGIN_URL . 'assets/js/admin.js',
                [ 'jquery', 'jquery-ui-sortable' ],
                WPPM_VERSION,
                true
            );
            wp_localize_script( 'wppm-admin', 'wppm_admin', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'wppm_admin_nonce' ),
                'i18n'     => [
                    'add_field'    => __( '添加字段', 'wp-product-manager' ),
                    'remove_field' => __( '删除', 'wp-product-manager' ),
                    'confirm_remove' => __( '确定要删除此字段吗？', 'wp-product-manager' ),
                ],
            ] );
        }
    }
}

// 激活/停用钩子
register_activation_hook( __FILE__, 'wppm_activate' );
register_deactivation_hook( __FILE__, 'wppm_deactivate' );

function wppm_activate() {
    WPPM_Post_Type::register();
    WPPM_Taxonomy::register();
    flush_rewrite_rules();
}

function wppm_deactivate() {
    flush_rewrite_rules();
}

// 启动插件
function wppm() {
    return WP_Product_Manager::instance();
}
add_action( 'plugins_loaded', 'wppm', 0 );
