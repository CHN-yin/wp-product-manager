<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 产品对象类 —— 模板中通过 wppm_get_product() 获取实例
 */
class WPPM_Product {

    /** @var WP_Post */
    protected $post;

    /** @var int */
    public $ID;

    public function __construct( $post ) {
        if ( is_numeric( $post ) ) {
            $post = get_post( $post );
        }
        if ( ! $post || $post->post_type !== 'wppm_product' ) {
            throw new InvalidArgumentException( 'Invalid product post.' );
        }
        $this->post = $post;
        $this->ID   = $post->ID;
    }

    // ─── 基础字段 ──────────────────────────────────────────────

    public function get_id()      { return $this->ID; }
    public function get_title()   { return get_the_title( $this->post ); }
    public function get_excerpt() { return get_the_excerpt( $this->post ); }
    public function get_content() { return apply_filters( 'the_content', $this->post->post_content ); }
    public function get_slug()    { return $this->post->post_name; }
    public function get_status()  { return $this->post->post_status; }
    public function get_date( $format = '' ) {
        return get_the_date( $format, $this->post );
    }
    public function get_permalink() { return get_permalink( $this->post ); }

    // ─── SKU / 排序 ────────────────────────────────────────────

    public function get_sku() {
        return get_post_meta( $this->ID, '_wppm_sku', true );
    }

    public function get_sort_order() {
        return (int) get_post_meta( $this->ID, '_wppm_sort_order', true );
    }

    // ─── 评论 ──────────────────────────────────────────────────

    public function comments_open() {
        return $this->post->comment_status === 'open';
    }

    public function get_comment_count() {
        return (int) $this->post->comment_count;
    }

    // ─── 主图 ──────────────────────────────────────────────────

    public function get_image_id() {
        return get_post_thumbnail_id( $this->post );
    }

    public function get_image( $size = 'large', $attr = [] ) {
        return get_the_post_thumbnail( $this->post, $size, $attr );
    }

    public function get_image_url( $size = 'large' ) {
        $id = $this->get_image_id();
        if ( ! $id ) return '';
        $src = wp_get_attachment_image_src( $id, $size );
        return $src ? $src[0] : '';
    }

    public function has_image() {
        return has_post_thumbnail( $this->post );
    }

    // ─── 副图（图片库） ────────────────────────────────────────

    public function get_gallery_ids() {
        $ids = get_post_meta( $this->ID, '_wppm_gallery_ids', true );
        return is_array( $ids ) ? array_map( 'absint', $ids ) : [];
    }

    public function get_gallery_images( $size = 'medium' ) {
        $images = [];
        foreach ( $this->get_gallery_ids() as $img_id ) {
            $images[] = [
                'id'  => $img_id,
                'url' => wp_get_attachment_image_url( $img_id, $size ),
                'img' => wp_get_attachment_image( $img_id, $size ),
                'alt' => get_post_meta( $img_id, '_wp_attachment_image_alt', true ),
            ];
        }
        return $images;
    }

    // ─── 自定义字段 ────────────────────────────────────────────

    public function get_custom_fields() {
        $fields = get_post_meta( $this->ID, '_wppm_custom_fields', true );
        return is_array( $fields ) ? $fields : [];
    }

    /**
     * 按 key 获取单个自定义字段值
     */
    public function get_custom_field( $key ) {
        foreach ( $this->get_custom_fields() as $field ) {
            if ( $field['key'] === $key ) return $field['value'];
        }
        return null;
    }

    /**
     * 输出自定义字段表格（辅助方法）
     */
    public function the_custom_fields_table( $class = 'wppm-custom-fields' ) {
        $fields = $this->get_custom_fields();
        if ( empty( $fields ) ) return;
        echo '<table class="' . esc_attr( $class ) . '">';
        foreach ( $fields as $f ) {
            echo '<tr>';
            echo '<th>' . esc_html( $f['label'] ?: $f['key'] ) . '</th>';
            echo '<td>' . esc_html( $f['value'] ) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    // ─── 分类 ──────────────────────────────────────────────────

    public function get_categories( $args = [] ) {
        return wp_get_post_terms( $this->ID, 'wppm_product_cat', $args );
    }

    public function get_tags( $args = [] ) {
        return wp_get_post_terms( $this->ID, 'wppm_product_tag', $args );
    }

    public function get_categories_list( $separator = ', ' ) {
        return get_the_term_list( $this->ID, 'wppm_product_cat', '', $separator );
    }

    public function get_tags_list( $separator = ', ' ) {
        return get_the_term_list( $this->ID, 'wppm_product_tag', '', $separator );
    }

    // ─── 魔术方法 ──────────────────────────────────────────────

    public function __get( $key ) {
        return isset( $this->post->$key ) ? $this->post->$key : null;
    }

    public function get_post() {
        return $this->post;
    }
}
