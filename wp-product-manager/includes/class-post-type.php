<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPPM_Post_Type {

    public static function register() {
        $labels = [
            'name'                  => __( '产品', 'wp-product-manager' ),
            'singular_name'         => __( '产品', 'wp-product-manager' ),
            'menu_name'             => __( '产品管理', 'wp-product-manager' ),
            'name_admin_bar'        => __( '产品', 'wp-product-manager' ),
            'add_new'               => __( '新增产品', 'wp-product-manager' ),
            'add_new_item'          => __( '新增产品', 'wp-product-manager' ),
            'new_item'              => __( '新产品', 'wp-product-manager' ),
            'edit_item'             => __( '编辑产品', 'wp-product-manager' ),
            'view_item'             => __( '查看产品', 'wp-product-manager' ),
            'all_items'             => __( '所有产品', 'wp-product-manager' ),
            'search_items'          => __( '搜索产品', 'wp-product-manager' ),
            'parent_item_colon'     => __( '父产品：', 'wp-product-manager' ),
            'not_found'             => __( '未找到产品', 'wp-product-manager' ),
            'not_found_in_trash'    => __( '回收站中未找到产品', 'wp-product-manager' ),
            'featured_image'        => __( '产品主图', 'wp-product-manager' ),
            'set_featured_image'    => __( '设置产品主图', 'wp-product-manager' ),
            'remove_featured_image' => __( '移除产品主图', 'wp-product-manager' ),
            'use_featured_image'    => __( '用作产品主图', 'wp-product-manager' ),
            'archives'              => __( '产品归档', 'wp-product-manager' ),
            'insert_into_item'      => __( '插入到产品', 'wp-product-manager' ),
            'uploaded_to_this_item' => __( '上传到此产品', 'wp-product-manager' ),
            'items_list'            => __( '产品列表', 'wp-product-manager' ),
            'items_list_navigation' => __( '产品列表导航', 'wp-product-manager' ),
            'filter_items_list'     => __( '筛选产品列表', 'wp-product-manager' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => [ 'slug' => 'product', 'with_front' => false ],
            'capability_type'    => 'post',
            'has_archive'        => 'products',
            'hierarchical'       => false,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-archive',
            'supports'           => [
                'title',
                'editor',
                'excerpt',
                'thumbnail',
                'comments',
                'revisions',
                'custom-fields',
            ],
            'show_in_rest'       => false, // 禁用 Gutenberg 块编辑器
            'taxonomies'         => [ 'wppm_product_cat' ],
        ];

        register_post_type( 'wppm_product', apply_filters( 'wppm_post_type_args', $args ) );

        // 强制经典编辑器（兼容已安装 Classic Editor 插件的情况）
        add_filter( 'use_block_editor_for_post_type', [ __CLASS__, 'disable_gutenberg' ], 10, 2 );
        add_filter( 'gutenberg_can_edit_post_type',   [ __CLASS__, 'disable_gutenberg' ], 10, 2 );
    }

    public static function disable_gutenberg( $use_block_editor, $post_type ) {
        if ( $post_type === 'wppm_product' ) return false;
        return $use_block_editor;
    }
}
