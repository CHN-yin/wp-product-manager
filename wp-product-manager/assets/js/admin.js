/* global jQuery, wppm_admin, wp */
(function ($) {
    'use strict';

    /* ═══════════════════════════════════════════════════════════
       副图（图片库）管理
    ═══════════════════════════════════════════════════════════ */
    var galleryFrame;

    function syncGalleryIds() {
        var ids = [];
        $('#wppm-gallery-images .wppm-gallery-item').each(function () {
            ids.push($(this).data('id'));
        });
        $('#wppm_gallery_ids').val(ids.join(','));
    }

    function addGalleryItem(id, thumbUrl) {
        // 移除空状态提示
        $('#wppm-gallery-images .wppm-gallery-empty').remove();

        var $item = $(
            '<div class="wppm-gallery-item" data-id="' + id + '">' +
            '<img src="' + thumbUrl + '" alt="">' +
            '<span class="wppm-remove-image">&#10005;</span>' +
            '</div>'
        );
        $('#wppm-gallery-images').append($item);
        syncGalleryIds();
    }

    $('#wppm-add-gallery-images').on('click', function () {
        if (galleryFrame) { galleryFrame.open(); return; }

        galleryFrame = wp.media({
            title:    '选择副图',
            button:   { text: '添加到副图' },
            multiple: true,
            library:  { type: 'image' }
        });

        galleryFrame.on('select', function () {
            var existing = $('#wppm_gallery_ids').val().split(',').filter(Boolean);
            galleryFrame.state().get('selection').each(function (attachment) {
                var id  = String(attachment.get('id'));
                if (existing.indexOf(id) !== -1) return; // 去重
                existing.push(id);
                var sizes = attachment.get('sizes');
                var url   = (sizes && sizes.thumbnail) ? sizes.thumbnail.url : attachment.get('url');
                addGalleryItem(id, url);
            });
        });

        galleryFrame.open();
    });

    $(document).on('click', '.wppm-gallery-item .wppm-remove-image', function (e) {
        e.stopPropagation();
        $(this).closest('.wppm-gallery-item').remove();
        syncGalleryIds();
        if (!$('#wppm-gallery-images .wppm-gallery-item').length) {
            $('#wppm-gallery-images').append(
                '<p class="wppm-gallery-empty">尚未添加副图，点击下方按钮上传。</p>'
            );
        }
    });

    $('#wppm-clear-gallery').on('click', function () {
        if (!confirm('确定要清空所有副图吗？')) return;
        $('#wppm-gallery-images').empty().append(
            '<p class="wppm-gallery-empty">尚未添加副图，点击下方按钮上传。</p>'
        );
        $('#wppm_gallery_ids').val('');
    });

    $('#wppm-gallery-images').sortable({
        placeholder: 'wppm-gallery-item ui-sortable-placeholder',
        cursor: 'grabbing',
        tolerance: 'pointer',
        update: syncGalleryIds
    });


    /* ═══════════════════════════════════════════════════════════
       自定义字段管理
    ═══════════════════════════════════════════════════════════ */
    var cfIndex = $('#wppm-cf-tbody .wppm-cf-row').length;

    $('#wppm-add-field').on('click', function () {
        var tpl = $('#wppm-cf-row-tpl').html();
        if (!tpl) return;
        var $row = $(tpl.replace(/__IDX__/g, cfIndex));
        $('#wppm-cf-tbody').append($row);
        cfIndex++;
        $row.find('input[type="text"]:first').focus();
    });

    $(document).on('click', '.wppm-remove-field', function () {
        if (!confirm('确定要删除此字段吗？')) return;
        $(this).closest('.wppm-cf-row').remove();
        reindexRows();
    });

    function reindexRows() {
        $('#wppm-cf-tbody .wppm-cf-row').each(function (i) {
            $(this).find('[name^="wppm_custom_fields["]').each(function () {
                $(this).attr('name', $(this).attr('name').replace(/\[\d+\]/, '[' + i + ']'));
            });
        });
    }

    // 字段类型切换 → 切换 input / textarea
    $(document).on('change', '.wppm-cf-type', function () {
        var type  = $(this).val();
        var $td   = $(this).closest('.wppm-cf-row').find('.wppm-cf-value-cell');
        var $cur  = $td.find('.wppm-cf-value');
        var name  = $cur.attr('name');
        var val   = $cur.val();

        if (type === 'textarea') {
            if ($cur.is('textarea')) return;
            $cur.replaceWith(
                $('<textarea rows="2" class="widefat wppm-cf-value"></textarea>')
                    .attr('name', name).text(val)
            );
        } else {
            if ($cur.is('input')) {
                $cur.attr('type', type);
            } else {
                $cur.replaceWith(
                    $('<input class="widefat wppm-cf-value">').attr({type: type, name: name, value: val})
                );
            }
        }
    });

    // 自定义字段行拖拽排序
    $('#wppm-cf-tbody').sortable({
        handle:      '.wppm-drag-icon',
        placeholder: 'wppm-cf-row ui-sortable-placeholder',
        axis:        'y',
        cursor:      'grabbing',
        tolerance:   'pointer',
        update:      reindexRows
    });

}(jQuery));
