<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 模板加载器
 * 优先级：子主题 → 主题 → 插件内置模板
 */
class WPPM_Template_Loader {

    public static function init() {
        add_filter( 'template_include', [ __CLASS__, 'template_loader' ] );
    }

    public static function template_loader( $template ) {

        if ( is_singular( 'wppm_product' ) ) {
            $found = self::locate_template( [
                'single-wppm_product.php',
                'single-product.php',
            ] );
            if ( $found ) return $found;

        } elseif ( is_post_type_archive( 'wppm_product' ) || is_tax( [ 'wppm_product_cat', 'wppm_product_tag' ] ) ) {
            $found = self::locate_template( [
                'archive-wppm_product.php',
                'archive-product.php',
            ] );
            if ( $found ) return $found;
        }

        return $template;
    }

    /**
     * 按优先级查找模板
     */
    public static function locate_template( array $templates ) {
        // 先让主题找
        $located = locate_template( $templates, false, false );
        if ( $located ) return $located;

        // 再到插件内置
        foreach ( $templates as $tpl ) {
            $path = WPPM_PLUGIN_DIR . 'templates/' . $tpl;
            if ( file_exists( $path ) ) return $path;
        }

        return false;
    }

    /**
     * 获取并加载模板片段（供外部调用）
     */
    public static function get_template_part( $slug, $name = null, $args = [] ) {
        $templates = [];
        if ( $name ) {
            $templates[] = "{$slug}-{$name}.php";
        }
        $templates[] = "{$slug}.php";

        $located = self::locate_template( $templates );
        if ( $located ) {
            if ( ! empty( $args ) ) extract( $args, EXTR_SKIP ); // phpcs:ignore
            load_template( $located, false );
        }
    }
}
