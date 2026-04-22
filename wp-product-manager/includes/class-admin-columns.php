<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPPM_Admin_Columns {

    public static function init() {
        add_filter( 'manage_wppm_product_posts_columns',       [ __CLASS__, 'set_columns' ] );
        add_action( 'manage_wppm_product_posts_custom_column', [ __CLASS__, 'render_column' ], 10, 2 );
        add_filter( 'manage_edit-wppm_product_sortable_columns', [ __CLASS__, 'sortable_columns' ] );
    }

    public static function set_columns( $columns ) {
        $new = [
            'cb'            => $columns['cb'],
            'wppm_thumb'    => __( '主图', 'wp-product-manager' ),
            'title'         => __( '产品名称', 'wp-product-manager' ),
            'taxonomy-wppm_product_cat' => __( '分类', 'wp-product-manager' ),
            'wppm_sku'      => __( 'SKU', 'wp-product-manager' ),
            'wppm_order'    => __( '排序', 'wp-product-manager' ),
            'wppm_comments' => __( '评论', 'wp-product-manager' ),
            'date'          => __( '日期', 'wp-product-manager' ),
        ];
        return $new;
    }

    public static function render_column( $column, $post_id ) {
        switch ( $column ) {
            case 'wppm_thumb':
                $thumb = get_the_post_thumbnail( $post_id, [ 50, 50 ] );
                echo $thumb ? $thumb : '<span style="color:#ccc;">—</span>';
                break;
            case 'wppm_sku':
                $sku = get_post_meta( $post_id, '_wppm_sku', true );
                echo $sku ? esc_html( $sku ) : '<span style="color:#ccc;">—</span>';
                break;
            case 'wppm_order':
                echo esc_html( get_post_meta( $post_id, '_wppm_sort_order', true ) ?: 0 );
                break;
            case 'wppm_comments':
                $post           = get_post( $post_id );
                $comment_status = $post->comment_status === 'open'
                    ? '<span style="color:green;">✓</span>'
                    : '<span style="color:#ccc;">✗</span>';
                echo $comment_status;
                break;
        }
    }

    public static function sortable_columns( $columns ) {
        $columns['wppm_sku']   = 'wppm_sku';
        $columns['wppm_order'] = 'wppm_order';
        return $columns;
    }
}
