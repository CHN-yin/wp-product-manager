<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 获取产品对象
 *
 * @param  int|WP_Post|null $post
 * @return WPPM_Product|false
 */
function wppm_get_product( $post = null ) {
    if ( $post === null ) $post = get_post();
    try {
        return new WPPM_Product( $post );
    } catch ( Exception $e ) {
        return false;
    }
}

/**
 * 查询产品列表
 *
 * @param  array $args WP_Query 参数
 * @return WP_Query
 */
function wppm_get_products( $args = [] ) {
    $defaults = [
        'post_type'      => 'wppm_product',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'orderby'        => 'meta_value_num',
        'meta_key'       => '_wppm_sort_order',
        'order'          => 'ASC',
    ];
    $args = wp_parse_args( $args, $defaults );
    return new WP_Query( $args );
}

/**
 * 输出产品图片
 */
function wppm_product_image( $product = null, $size = 'large', $attr = [] ) {
    if ( ! $product ) $product = wppm_get_product();
    if ( ! $product ) return;
    if ( $product->has_image() ) {
        echo $product->get_image( $size, $attr );
    } else {
        echo apply_filters( 'wppm_placeholder_image', '<div class="wppm-no-image">' . esc_html__( '暂无图片', 'wp-product-manager' ) . '</div>' );
    }
}

/**
 * 输出产品副图列表
 */
function wppm_product_gallery( $product = null, $size = 'medium' ) {
    if ( ! $product ) $product = wppm_get_product();
    if ( ! $product ) return;
    $images = $product->get_gallery_images( $size );
    if ( empty( $images ) ) return;
    echo '<div class="wppm-gallery">';
    foreach ( $images as $img ) {
        echo '<div class="wppm-gallery-item">';
        echo '<a href="' . esc_url( wp_get_attachment_url( $img['id'] ) ) . '" data-id="' . $img['id'] . '">';
        echo $img['img'];
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

/**
 * 输出分类面包屑
 */
function wppm_breadcrumb( $separator = ' › ' ) {
    $terms = get_the_terms( get_the_ID(), 'wppm_product_cat' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) return;
    $parts = [
        '<a href="' . esc_url( get_post_type_archive_link( 'wppm_product' ) ) . '">' . esc_html__( '产品', 'wp-product-manager' ) . '</a>',
    ];
    $term = array_shift( $terms );
    // 输出父分类
    if ( $term->parent ) {
        $parent = get_term( $term->parent, 'wppm_product_cat' );
        if ( $parent && ! is_wp_error( $parent ) ) {
            $parts[] = '<a href="' . esc_url( get_term_link( $parent ) ) . '">' . esc_html( $parent->name ) . '</a>';
        }
    }
    $parts[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
    $parts[] = '<span>' . esc_html( get_the_title() ) . '</span>';
    echo '<nav class="wppm-breadcrumb">' . implode( esc_html( $separator ), $parts ) . '</nav>';
}
