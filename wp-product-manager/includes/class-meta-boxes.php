<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPPM_Meta_Boxes {

    public static function init() {
        add_action( 'add_meta_boxes',  [ __CLASS__, 'add_meta_boxes' ] );
        add_action( 'add_meta_boxes',  [ __CLASS__, 'remove_default_meta_boxes' ], 99 );
        add_action( 'save_post',       [ __CLASS__, 'save_meta_boxes' ], 10, 2 );
        add_action( 'admin_head',      [ __CLASS__, 'localize_screen_labels' ] );
    }

    public static function remove_default_meta_boxes() {
        remove_meta_box( 'postcustom', 'wppm_product', 'normal' );
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'wppm_gallery',
            __( '产品副图（图片库）', 'wp-product-manager' ),
            [ __CLASS__, 'render_gallery_meta_box' ],
            'wppm_product', 'normal', 'high'
        );
        add_meta_box(
            'wppm_custom_fields',
            __( '产品参数 / 自定义字段', 'wp-product-manager' ),
            [ __CLASS__, 'render_custom_fields_meta_box' ],
            'wppm_product', 'normal', 'default'
        );
        add_meta_box(
            'wppm_settings',
            __( '产品设置', 'wp-product-manager' ),
            [ __CLASS__, 'render_settings_meta_box' ],
            'wppm_product', 'side', 'default'
        );
    }

    public static function localize_screen_labels() {
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'wppm_product' ) return;
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el;
            el = document.querySelector('#postexcerpt .hndle span');
            if (el) el.textContent = '产品摘要';
        });
        </script>
        <?php
    }

    /* ── 副图 ──────────────────────────────────────────────── */
    public static function render_gallery_meta_box( $post ) {
        wp_nonce_field( 'wppm_save_gallery', 'wppm_gallery_nonce' );
        $gallery_ids = get_post_meta( $post->ID, '_wppm_gallery_ids', true );
        $ids_string  = is_array( $gallery_ids ) ? implode( ',', array_filter( $gallery_ids ) ) : '';
        ?>
        <div class="wppm-gallery-wrap">
            <input type="hidden" id="wppm_gallery_ids" name="wppm_gallery_ids" value="<?php echo esc_attr( $ids_string ); ?>">
            <div id="wppm-gallery-images" class="wppm-gallery-images">
                <?php
                if ( ! empty( $gallery_ids ) ) {
                    foreach ( (array) $gallery_ids as $img_id ) {
                        $img_id = absint( $img_id );
                        if ( ! $img_id ) continue;
                        echo '<div class="wppm-gallery-item" data-id="' . $img_id . '">';
                        echo wp_get_attachment_image( $img_id, 'thumbnail' );
                        echo '<span class="wppm-remove-image">&#10005;</span>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="wppm-gallery-empty">' . esc_html__( '尚未添加副图，点击下方按钮上传。', 'wp-product-manager' ) . '</p>';
                }
                ?>
            </div>
            <p class="wppm-gallery-actions">
                <button type="button" id="wppm-add-gallery-images" class="button">
                    <?php esc_html_e( '+ 添加副图', 'wp-product-manager' ); ?>
                </button>
                <button type="button" id="wppm-clear-gallery" class="button wppm-danger-btn" style="margin-left:6px;">
                    <?php esc_html_e( '清空所有', 'wp-product-manager' ); ?>
                </button>
                <span class="description" style="margin-left:10px;">
                    <?php esc_html_e( '可拖拽图片调整顺序', 'wp-product-manager' ); ?>
                </span>
            </p>
        </div>
        <?php
    }

    /* ── 自定义字段 ─────────────────────────────────────────── */
    public static function render_custom_fields_meta_box( $post ) {
        wp_nonce_field( 'wppm_save_custom_fields', 'wppm_custom_fields_nonce' );
        $custom_fields = get_post_meta( $post->ID, '_wppm_custom_fields', true );
        if ( ! is_array( $custom_fields ) ) $custom_fields = [];
        ?>
        <div class="wppm-cf-wrap">
            <table class="widefat fixed striped wppm-cf-table" id="wppm-cf-table">
                <thead>
                    <tr>
                        <th width="22" class="wppm-col-handle"></th>
                        <th width="18%"><?php esc_html_e( '字段名 (Key)', 'wp-product-manager' ); ?></th>
                        <th width="18%"><?php esc_html_e( '显示标签', 'wp-product-manager' ); ?></th>
                        <th width="14%"><?php esc_html_e( '类型', 'wp-product-manager' ); ?></th>
                        <th><?php esc_html_e( '字段值', 'wp-product-manager' ); ?></th>
                        <th width="44"><?php esc_html_e( '删除', 'wp-product-manager' ); ?></th>
                    </tr>
                </thead>
                <tbody id="wppm-cf-tbody">
                    <?php foreach ( $custom_fields as $i => $f ) { self::render_cf_row( $i, $f ); } ?>
                </tbody>
            </table>
            <p style="margin-top:8px;">
                <button type="button" id="wppm-add-field" class="button button-primary">
                    <?php esc_html_e( '+ 添加字段', 'wp-product-manager' ); ?>
                </button>
                <span class="description" style="margin-left:8px;">
                    <?php esc_html_e( 'Key 只允许字母、数字、下划线', 'wp-product-manager' ); ?>
                </span>
            </p>
        </div>
        <script type="text/html" id="wppm-cf-row-tpl">
            <?php self::render_cf_row( '__IDX__', [] ); ?>
        </script>
        <?php
    }

    public static function render_cf_row( $index, $field = [] ) {
        $key   = isset( $field['key'] )   ? esc_attr( $field['key'] )               : '';
        $label = isset( $field['label'] ) ? esc_attr( $field['label'] )             : '';
        $type  = isset( $field['type'] )  ? $field['type']                          : 'text';
        $value = isset( $field['value'] ) ? $field['value']                         : '';
        $types = [
            'text'     => '文本',
            'textarea' => '多行文本',
            'number'   => '数字',
            'url'      => '链接 URL',
            'email'    => '邮箱',
            'date'     => '日期',
            'color'    => '颜色',
        ];
        ?>
        <tr class="wppm-cf-row">
            <td class="wppm-cf-handle"><span class="wppm-drag-icon dashicons dashicons-menu"></span></td>
            <td><input type="text" name="wppm_custom_fields[<?php echo $index; ?>][key]" value="<?php echo $key; ?>" class="widefat" placeholder="如 price"></td>
            <td><input type="text" name="wppm_custom_fields[<?php echo $index; ?>][label]" value="<?php echo $label; ?>" class="widefat" placeholder="如 价格"></td>
            <td>
                <select name="wppm_custom_fields[<?php echo $index; ?>][type]" class="wppm-cf-type">
                    <?php foreach ( $types as $tv => $tl ) : ?>
                        <option value="<?php echo esc_attr($tv); ?>" <?php selected($type,$tv); ?>><?php echo esc_html($tl); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td class="wppm-cf-value-cell">
                <?php if ( $type === 'textarea' ) : ?>
                    <textarea name="wppm_custom_fields[<?php echo $index; ?>][value]" rows="2" class="widefat wppm-cf-value"><?php echo esc_textarea($value); ?></textarea>
                <?php else : ?>
                    <input type="<?php echo esc_attr($type?:'text'); ?>" name="wppm_custom_fields[<?php echo $index; ?>][value]" value="<?php echo esc_attr($value); ?>" class="widefat wppm-cf-value">
                <?php endif; ?>
            </td>
            <td style="text-align:center;vertical-align:middle;">
                <button type="button" class="button wppm-remove-field"><span class="dashicons dashicons-trash" style="margin-top:3px;font-size:16px;"></span></button>
            </td>
        </tr>
        <?php
    }

    /* ── 产品设置（侧边栏） ──────────────────────────────────── */
    public static function render_settings_meta_box( $post ) {
        wp_nonce_field( 'wppm_save_settings', 'wppm_settings_nonce' );
        $sku        = get_post_meta( $post->ID, '_wppm_sku', true );
        $sort_order = get_post_meta( $post->ID, '_wppm_sort_order', true );
        $comments   = $post->comment_status;
        ?>
        <div class="wppm-side-box">
            <div class="wppm-side-row">
                <label for="wppm_sku"><strong><?php esc_html_e( 'SKU / 货号', 'wp-product-manager' ); ?></strong></label>
                <input type="text" id="wppm_sku" name="wppm_sku"
                       value="<?php echo esc_attr($sku); ?>" class="widefat"
                       placeholder="<?php esc_attr_e('产品编号（选填）','wp-product-manager'); ?>">
            </div>
            <div class="wppm-side-row">
                <label for="wppm_sort_order"><strong><?php esc_html_e( '排序权重', 'wp-product-manager' ); ?></strong></label>
                <input type="number" id="wppm_sort_order" name="wppm_sort_order"
                       value="<?php echo esc_attr($sort_order !== '' ? $sort_order : '0'); ?>"
                       class="widefat" min="0" step="1">
                <p class="description"><?php esc_html_e('数值越小排越前','wp-product-manager'); ?></p>
            </div>
            <div class="wppm-side-row" style="border-top:1px solid #ddd;padding-top:12px;margin-top:4px;">
                <strong><?php esc_html_e( '开启评论功能', 'wp-product-manager' ); ?></strong>
                <div style="margin-top:8px;display:flex;gap:16px;">
                    <label><input type="radio" name="comment_status" value="open" <?php checked($comments,'open'); ?>> <?php esc_html_e('开启','wp-product-manager'); ?></label>
                    <label><input type="radio" name="comment_status" value="closed" <?php checked($comments,'closed'); ?>> <?php esc_html_e('关闭','wp-product-manager'); ?></label>
                </div>
            </div>
        </div>
        <?php
    }

    /* ── 保存 ───────────────────────────────────────────────── */
    public static function save_meta_boxes( $post_id, $post ) {
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if ( wp_is_post_revision($post_id) )                return;
        if ( $post->post_type !== 'wppm_product' )          return;
        if ( ! current_user_can('edit_post', $post_id) )    return;

        // 副图
        if ( isset($_POST['wppm_gallery_nonce']) &&
             wp_verify_nonce($_POST['wppm_gallery_nonce'],'wppm_save_gallery') ) {
            $raw = sanitize_text_field( $_POST['wppm_gallery_ids'] ?? '' );
            if ($raw !== '') {
                $ids = array_values(array_filter(array_map('absint', explode(',',$raw))));
                update_post_meta($post_id,'_wppm_gallery_ids',$ids);
            } else {
                delete_post_meta($post_id,'_wppm_gallery_ids');
            }
        }

        // 自定义字段
        if ( isset($_POST['wppm_custom_fields_nonce']) &&
             wp_verify_nonce($_POST['wppm_custom_fields_nonce'],'wppm_save_custom_fields') ) {
            $raw_fields = $_POST['wppm_custom_fields'] ?? [];
            $clean = [];
            foreach ((array)$raw_fields as $f) {
                $key = sanitize_key($f['key'] ?? '');
                if ($key === '') continue;
                $clean[] = [
                    'key'   => $key,
                    'label' => sanitize_text_field($f['label'] ?? ''),
                    'type'  => sanitize_text_field($f['type']  ?? 'text'),
                    'value' => sanitize_textarea_field($f['value'] ?? ''),
                ];
            }
            update_post_meta($post_id,'_wppm_custom_fields',$clean);
        }

        // 产品设置
        if ( isset($_POST['wppm_settings_nonce']) &&
             wp_verify_nonce($_POST['wppm_settings_nonce'],'wppm_save_settings') ) {
            update_post_meta($post_id,'_wppm_sku',sanitize_text_field($_POST['wppm_sku'] ?? ''));
            update_post_meta($post_id,'_wppm_sort_order',absint($_POST['wppm_sort_order'] ?? 0));
            $cs = (isset($_POST['comment_status']) && $_POST['comment_status']==='open') ? 'open' : 'closed';
            remove_action('save_post',[__CLASS__,'save_meta_boxes'],10);
            wp_update_post(['ID'=>$post_id,'comment_status'=>$cs]);
            add_action('save_post',[__CLASS__,'save_meta_boxes'],10,2);
        }
    }
}
