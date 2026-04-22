<?php
/**
 * 产品列表模板（归档 + 分类页复用）
 * 主题可复制此文件到主题根目录（archive-wppm_product.php）进行覆盖
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<div id="wppm-archive" class="wppm-archive">
    <div class="wppm-container">

        <!-- ── 页面标题 ─────────────────────────────────────────────── -->
        <header class="wppm-archive-header">
            <?php if ( is_tax( 'wppm_product_cat' ) ) : ?>
                <?php $term = get_queried_object(); ?>
                <h1 class="wppm-archive-title"><?php echo esc_html( $term->name ); ?></h1>
                <?php if ( $term->description ) : ?>
                <div class="wppm-term-description"><?php echo wpautop( esc_html( $term->description ) ); ?></div>
                <?php endif; ?>
            <?php elseif ( is_tax( 'wppm_product_tag' ) ) : ?>
                <h1 class="wppm-archive-title">
                    <?php esc_html_e( '标签：', 'wp-product-manager' ); ?>
                    <?php echo esc_html( get_queried_object()->name ); ?>
                </h1>
            <?php else : ?>
                <h1 class="wppm-archive-title"><?php esc_html_e( '所有产品', 'wp-product-manager' ); ?></h1>
            <?php endif; ?>

            <!-- 分类筛选导航 -->
            <nav class="wppm-cat-nav">
                <?php
                $cats = get_terms( [
                    'taxonomy'   => 'wppm_product_cat',
                    'hide_empty' => true,
                    'parent'     => 0,
                ] );
                if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) :
                    $current_term = is_tax() ? get_queried_object()->term_id : 0;
                ?>
                <ul class="wppm-cat-list">
                    <li class="<?php echo $current_term === 0 ? 'current' : ''; ?>">
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'wppm_product' ) ); ?>">
                            <?php esc_html_e( '全部', 'wp-product-manager' ); ?>
                        </a>
                    </li>
                    <?php foreach ( $cats as $cat ) : ?>
                    <li class="<?php echo $current_term === $cat->term_id ? 'current' : ''; ?>">
                        <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
                            <?php echo esc_html( $cat->name ); ?>
                            <span class="wppm-cat-count"><?php echo $cat->count; ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </nav>
        </header>

        <!-- ── 产品网格 ─────────────────────────────────────────────── -->
        <?php if ( have_posts() ) : ?>
        <div class="wppm-products-grid">
            <?php while ( have_posts() ) : the_post(); ?>
            <?php
                $product = wppm_get_product();
                if ( ! $product ) continue;
            ?>
            <article id="product-<?php the_ID(); ?>" <?php post_class( 'wppm-product-card' ); ?>>

                <!-- 主图 -->
                <a href="<?php the_permalink(); ?>" class="wppm-card-image">
                    <?php if ( $product->has_image() ) : ?>
                        <?php echo $product->get_image( 'medium', [ 'alt' => get_the_title() ] ); ?>
                    <?php else : ?>
                        <div class="wppm-no-image">
                            <span class="dashicons dashicons-format-image"></span>
                        </div>
                    <?php endif; ?>
                </a>

                <!-- 内容 -->
                <div class="wppm-card-body">

                    <h2 class="wppm-card-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>

                    <?php $cats_list = $product->get_categories_list(); if ( $cats_list ) : ?>
                    <p class="wppm-card-cats"><?php echo $cats_list; ?></p>
                    <?php endif; ?>

                    <?php if ( has_excerpt() ) : ?>
                    <div class="wppm-card-excerpt"><?php the_excerpt(); ?></div>
                    <?php endif; ?>

                    <!-- 展示几个自定义字段（取前 3 个） -->
                    <?php $fields = array_slice( $product->get_custom_fields(), 0, 3 ); ?>
                    <?php if ( ! empty( $fields ) ) : ?>
                    <ul class="wppm-card-fields">
                        <?php foreach ( $fields as $f ) : ?>
                        <li>
                            <span class="wppm-field-label"><?php echo esc_html( $f['label'] ?: $f['key'] ); ?>：</span>
                            <span class="wppm-field-value"><?php echo esc_html( $f['value'] ); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <a href="<?php the_permalink(); ?>" class="wppm-btn wppm-btn-outline">
                        <?php esc_html_e( '查看详情', 'wp-product-manager' ); ?>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>

                </div><!-- .wppm-card-body -->

            </article>
            <?php endwhile; ?>
        </div><!-- .wppm-products-grid -->

        <!-- 分页 -->
        <div class="wppm-pagination">
            <?php the_posts_pagination( [
                'mid_size'  => 2,
                'prev_text' => __( '&laquo; 上一页', 'wp-product-manager' ),
                'next_text' => __( '下一页 &raquo;', 'wp-product-manager' ),
            ] ); ?>
        </div>

        <?php else : ?>
        <div class="wppm-no-products">
            <p><?php esc_html_e( '暂无产品。', 'wp-product-manager' ); ?></p>
        </div>
        <?php endif; ?>

    </div><!-- .wppm-container -->
</div><!-- #wppm-archive -->
<?php get_footer(); ?>
