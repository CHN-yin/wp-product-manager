<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPPM_Taxonomy {

    public static function register() {
        // 产品分类
        $cat_labels = [
            'name'              => __( '产品分类', 'wp-product-manager' ),
            'singular_name'     => __( '产品分类', 'wp-product-manager' ),
            'search_items'      => __( '搜索分类', 'wp-product-manager' ),
            'all_items'         => __( '所有分类', 'wp-product-manager' ),
            'parent_item'       => __( '父分类', 'wp-product-manager' ),
            'parent_item_colon' => __( '父分类：', 'wp-product-manager' ),
            'edit_item'         => __( '编辑分类', 'wp-product-manager' ),
            'update_item'       => __( '更新分类', 'wp-product-manager' ),
            'add_new_item'      => __( '新增分类', 'wp-product-manager' ),
            'new_item_name'     => __( '新分类名称', 'wp-product-manager' ),
            'menu_name'         => __( '产品分类', 'wp-product-manager' ),
            'not_found'         => __( '未找到分类', 'wp-product-manager' ),
            'no_terms'          => __( '暂无分类', 'wp-product-manager' ),
            'back_to_items'     => __( '← 返回分类列表', 'wp-product-manager' ),
        ];

        $cat_args = [
            'hierarchical'      => true,
            'labels'            => $cat_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'product-category' ],
            'show_in_rest'      => true,
        ];

        register_taxonomy(
            'wppm_product_cat',
            'wppm_product',
            apply_filters( 'wppm_taxonomy_cat_args', $cat_args )
        );

        // 产品标签
        $tag_labels = [
            'name'          => __( '产品标签', 'wp-product-manager' ),
            'singular_name' => __( '产品标签', 'wp-product-manager' ),
            'search_items'  => __( '搜索标签', 'wp-product-manager' ),
            'all_items'     => __( '所有标签', 'wp-product-manager' ),
            'edit_item'     => __( '编辑标签', 'wp-product-manager' ),
            'update_item'   => __( '更新标签', 'wp-product-manager' ),
            'add_new_item'  => __( '新增标签', 'wp-product-manager' ),
            'new_item_name' => __( '新标签名称', 'wp-product-manager' ),
            'menu_name'     => __( '产品标签', 'wp-product-manager' ),
        ];

        $tag_args = [
            'hierarchical'      => false,
            'labels'            => $tag_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'product-tag' ],
            'show_in_rest'      => true,
        ];

        register_taxonomy(
            'wppm_product_tag',
            'wppm_product',
            apply_filters( 'wppm_taxonomy_tag_args', $tag_args )
        );
    }
}
