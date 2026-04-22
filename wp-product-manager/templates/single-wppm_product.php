<?php
/**
 * 单个产品模板
 * 主题可复制此文件到主题根目录（single-wppm_product.php）进行覆盖
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

// 获取产品对象
$product = wppm_get_product();
?>
<div id="wppm-single" class="wppm-single-product">
    <div class="wppm-container">
        <?php wppm_breadcrumb(); ?>

        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

        <article id="product-<?php the_ID(); ?>" <?php post_class( 'wppm-product' ); ?>>

            <div class="wppm-product-inner">

                <!-- ── 左侧：图片区 ───────────────────────────────── -->
                <div class="wppm-product-images">

                    <!-- 主图 -->
                    <div class="wppm-main-image">
                        <?php wppm_product_image( $product, 'large', [ 'class' => 'wppm-featured-img', 'alt' => get_the_title() ] ); ?>
                    </div>

                    <!-- 副图 -->
                    <?php if ( $product && ! empty( $product->get_gallery_ids() ) ) : ?>
                    <div class="wppm-gallery-thumbs">
                        <?php wppm_product_gallery( $product, 'thumbnail' ); ?>
                    </div>
                    <?php endif; ?>

                </div><!-- .wppm-product-images -->

                <!-- ── 右侧：产品信息 ──────────────────────────────── -->
                <div class="wppm-product-summary">

                    <h1 class="wppm-product-title"><?php the_title(); ?></h1>

                    <?php if ( $product && $product->get_sku() ) : ?>
                    <p class="wppm-sku">
                        <span class="wppm-label"><?php esc_html_e( 'SKU：', 'wp-product-manager' ); ?></span>
                        <?php echo esc_html( $product->get_sku() ); ?>
                    </p>
                    <?php endif; ?>

                    <?php if ( $product ) : ?>
                    <p class="wppm-categories">
                        <span class="wppm-label"><?php esc_html_e( '分类：', 'wp-product-manager' ); ?></span>
                        <?php echo $product->get_categories_list(); ?>
                    </p>
                    <?php endif; ?>

                    <!-- 摘要 -->
                    <?php if ( has_excerpt() ) : ?>
                    <div class="wppm-product-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                    <?php endif; ?>

                    <!-- 自定义字段表格 -->
                    <?php if ( $product ) : ?>
                    <?php $product->the_custom_fields_table(); ?>
                    <?php endif; ?>

                    <!-- 标签 -->
                    <?php if ( $product ) : $tags = $product->get_tags_list(); if ( $tags ) : ?>
                    <p class="wppm-tags">
                        <span class="wppm-label"><?php esc_html_e( '标签：', 'wp-product-manager' ); ?></span>
                        <?php echo $tags; ?>
                    </p>
                    <?php endif; endif; ?>

                </div><!-- .wppm-product-summary -->

            </div><!-- .wppm-product-inner -->

            <!-- ── 产品详细描述 ─────────────────────────────────────── -->
            <div class="wppm-product-description">
                <h2><?php esc_html_e( '产品详情', 'wp-product-manager' ); ?></h2>
                <?php the_content(); ?>
            </div>

            <!-- ── 评论区 ───────────────────────────────────────────── -->
            <?php if ( comments_open() || get_comments_number() ) : ?>
            <div class="wppm-product-comments">
                <?php comments_template(); ?>
            </div>
            <?php endif; ?>

        </article>

        <?php endwhile; endif; ?>

    </div><!-- .wppm-container -->
</div><!-- #wppm-single -->
<?php get_footer(); ?>
