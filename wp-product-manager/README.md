# WP Product Manager

WordPress 自定义产品管理插件，无需 WooCommerce 依赖，轻量独立。

---

## 功能列表

| 功能 | 说明 |
|------|------|
| 自定义文章类型 | `wppm_product`，固定链接：`/product/{slug}` |
| 产品分类 | 层级分类 `wppm_product_cat`，固定链接：`/product-category/{slug}` |
| 产品标签 | 平铺标签 `wppm_product_tag` |
| 产品主图 | 使用 WordPress 原生特色图片 |
| 产品副图 | 支持多图上传、拖拽排序、单张删除 |
| 自定义字段 | 可动态增减字段（text / textarea / number / url / email / date / color），支持拖拽排序 |
| SKU / 货号 | 后台侧边栏填写，列表页可见 |
| 评论开关 | 后台侧边栏单独控制每个产品 |
| 前台列表模板 | 产品网格 + 分类筛选导航 + 分页 |
| 前台单品模板 | 主图 + 副图 + 摘要 + 自定义字段表格 + 正文 + 评论 |
| 产品对象 API | `WPPM_Product` 类，提供完整 getter |

---

## 安装方法

1. 将 `wp-product-manager` 文件夹上传至 `/wp-content/plugins/`
2. 在后台「插件」中激活 **WP Product Manager**
3. 进入「设置 → 固定链接」重新保存固定链接（刷新规则）

---

## 模板覆盖

插件内置模板位于 `templates/` 目录，主题可复制并覆盖：

```
your-theme/
├── single-wppm_product.php   ← 单品页模板
└── archive-wppm_product.php  ← 列表页模板
```

---

## 在主题中使用产品对象

```php
// 在 The Loop 中获取当前产品
$product = wppm_get_product();

// 基础字段
echo $product->get_title();
echo $product->get_excerpt();
echo $product->get_content();
echo $product->get_permalink();
echo $product->get_sku();

// 主图
echo $product->get_image( 'large' );
$product->has_image(); // bool

// 副图
$gallery = $product->get_gallery_images( 'medium' );
foreach ( $gallery as $img ) {
    echo $img['img']; // <img> 标签
}

// 分类 / 标签
echo $product->get_categories_list();

// 自定义字段
$fields = $product->get_custom_fields(); // 数组
$price  = $product->get_custom_field( 'price' ); // 按 key 取单个值

// 评论
if ( $product->comments_open() ) { comments_template(); }
```

---

## 查询产品列表

```php
$query = wppm_get_products( [
    'posts_per_page' => 8,
    'tax_query' => [[
        'taxonomy' => 'wppm_product_cat',
        'field'    => 'slug',
        'terms'    => 'electronics',
    ]],
] );

if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
        $query->the_post();
        $product = wppm_get_product();
        // ...
    }
    wp_reset_postdata();
}
```

---

## 钩子参考

| 钩子 | 类型 | 说明 |
|------|------|------|
| `wppm_post_type_args` | filter | 过滤 CPT 注册参数 |
| `wppm_taxonomy_cat_args` | filter | 过滤分类注册参数 |
| `wppm_placeholder_image` | filter | 过滤无图片占位 HTML |

---

## 文件结构

```
wp-product-manager/
├── wp-product-manager.php          # 主插件文件
├── includes/
│   ├── class-post-type.php         # 自定义文章类型
│   ├── class-taxonomy.php          # 产品分类 & 标签
│   ├── class-meta-boxes.php        # 后台元数据框
│   ├── class-product.php           # 产品对象类
│   ├── class-template-loader.php   # 模板加载器
│   ├── class-admin-columns.php     # 列表页自定义列
│   └── functions.php               # 辅助函数
├── templates/
│   ├── single-wppm_product.php     # 单品模板
│   └── archive-wppm_product.php    # 列表模板
├── assets/
│   ├── css/
│   │   ├── frontend.css
│   │   └── admin.css
│   └── js/
│       └── admin.js
└── README.md
```
