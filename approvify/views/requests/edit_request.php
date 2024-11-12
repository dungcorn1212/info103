<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div id="response"></div>
            <?php echo form_open_multipart(admin_url('approvify/edit_request/' . $request->id), ['id' => 'requestForm', 'class' => 'disable-on-submit']); ?>
            <?php echo form_hidden('csrf_token', $this->security->get_csrf_hash()); ?>
            <div class="col-md-12">
                <button type="button" onclick="window.history.back();" class="btn btn-primary tw-mr-2">
                    <i class="fa fa-arrow-left"></i> Quay lại
                </button>
                <h2><?php echo isset($type_data) ? html_escape($type_data->category_name) : ''; ?></h2>
                <p><?php echo isset($type_data) ? html_escape($type_data->category_description) : ''; ?></p>
            </div>

            <div class="col-md-9">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <?php echo render_input('request_title', 'approvify_request_title', $request->request_title); ?>
                        </div>

                        <div class="col-md-12">
                            <?php echo render_textarea('request_content', 'approvify_request_content', $request->request_content, ['rows' => 10], [], '', 'tinymce'); ?>
                        </div>

                        <div class="col-md-12">
                            <div class="dropdown pull-left">
                                <button class="btn btn-info dropdown-toggle" type="button" id="templateDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Chọn mẫu đề xuất
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="templateDropdown">
                                    <li><a href="#" class="template-option" data-template="payment">Đề xuất thanh toán</a></li>
                                    <li><a href="#" class="template-option" data-template="report">Báo cáo công việc</a></li>
                                </ul>
                            </div>
                            <button type="submit" id="submit-button" class="btn btn-primary saveDocument pull-right"><?php echo _l('approvify_update_request'); ?></button>
                        </div>
                    </div>
                </div>
                <p><b><span class="text-danger">Lưu ý: Vui lòng kiểm tra lại nội dung trước khi bấm cập nhật đề xuất.</span></b></p>
            </div>

            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12 mbot10">
<div class="attachments">
    <h5>File đính kèm hiện tại:</h5>
    <?php
    if (!empty($attachments)) {
        foreach ($attachments as $attachment) {
            $path = FCPATH . 'modules/approvify/uploads/requests/' . $request_data->id . '/' . $attachment['filename'];
            $attachment_url = substr(module_dir_url('approvify/uploads/requests/' . $request->id . '/' . $attachment['filename']), 0, -1);
            $is_image = is_image($path);
            echo '<div class="attachment-item" id="attachment-' . $attachment['id'] . '">';
            echo '<a target="_blank" href="' . html_escape($attachment_url) . '" class="attachment-name" ' . ($is_image ? 'data-lightbox="attachment-reply-"' : '') . '>' . html_escape($attachment['filename']) . '</a>';
            if ($is_image) {
                echo '<div class="preview_image">';
                echo '<img class="preview-img" src="' . site_url('download/preview_image?path=' . protected_file_url_by_path($path) . '&type=') . '">';
                echo '</div>';
            }
            echo '<a href="#" class="delete-btn text-danger" onclick="delete_request_attachment(' . $attachment['id'] . '); return false;"><i class="fa fa-times"></i></a>';
            echo '</div>';
        }
    } else {
        echo '<p>Không có file đính kèm.</p>';
    }
    ?>
</div>


                            <div class="clearfix"></div>
                            <hr />
                            <div class="new-attachment">
                                <label for="attachment" class="control-label"><?php echo _l('up_load_new_file'); ?></label>
                                <div class="input-group">
                                    <input type="file" extension="<?php echo str_replace('.', '', get_option('ticket_attachments_file_extensions')); ?>" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="attachments[]" accept="<?php echo get_ticket_form_accepted_mimes(); ?>" multiple>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
$(document).ready(function() {
    var deletedAttachments = [];
    // Xử lý xóa tệp đính kèm
    window.delete_request_attachment = function(id) {
        if (confirm('Bạn có chắc chắn muốn xóa tệp đính kèm này?')) {
            $.ajax({
                url: admin_url + 'approvify/delete_request_attachment/' + id,
                type: 'POST',
                data: {csrf_token: $('input[name="csrf_token"]').val()},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#attachment-' + id).fadeOut(500, function() {
                            $(this).remove();
                        });
                         deletedAttachments.push(id);
                        $('#deleted_attachments').val(JSON.stringify(deletedAttachments));
                        alert_float('success', response.message);
                    } else {
                        alert_float('danger', response.message);
                    }
                },
                error: function() {
                    alert_float('danger', 'Đã xảy ra lỗi. Vui lòng thử lại.');
                }
            });
        }
    };

    // Xử lý form submit
$('#requestForm').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    if ($form.data('submitted') === true) {
        // Đã submit rồi, không làm gì cả
        return false;
    }
    
    // Đánh dấu form đã được submit
    $form.data('submitted', true);
    
    // Vô hiệu hóa nút submit
    $form.find(':submit').prop('disabled', true);
    
    // Gửi form
    this.submit();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    setTimeout(function() {
                        window.location.href = admin_url + 'approvify/view_request/' + <?php echo $request->id; ?>;
                    }, 1000);
                } else {
                    alert_float('warning', response.message);
                }
            },
            error: function() {
                alert_float('danger', 'Đã xảy ra lỗi. Vui lòng thử lại.');
            }
        });
    });

    // Xử lý chọn mẫu đề xuất
    $('.template-option').on('click', function(e) {
        e.preventDefault();
        var template = $(this).data('template');
        // Thêm logic để áp dụng mẫu vào nội dung
        console.log('Áp dụng mẫu:', template);
    });
});

// Logic cho TinyMCE 
   $(function() {
        var form_id = '#requestForm';

        tinymce.init({
            selector: 'textarea#request_content',
            plugins: 'paste lists link table',
            menubar: false,
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | table',
            paste_as_text: false,
            paste_enable_default_filters: false,
            forced_root_block: 'p',
            force_br_newlines: false,
            force_p_newlines: true,
            table_default_styles: {
                width: '100%',
                border: '1px solid #ccc'
            },
            table_cell_default_styles: {
                border: '1px solid #ccc',
                padding: '8px'
            },
            content_style: `
                body { font-family: Arial, sans-serif; }
                h1, h2, h3 { text-align: center; }
                p { text-indent: 20px; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .indent { text-indent: 20px; }
                table { border-collapse: collapse; width: 100%; }
                table, th, td { border: 1px solid #ccc; }
                th, td { padding: 8px; text-align: left; }
                ol, ul { padding-left: 30px; }
                li { margin-bottom: 5px; }
            `,
            setup: function(editor) {
                editor.on('init', function() {
                    editor.setContent(<?php echo json_encode($request->request_content); ?>);
                });

                editor.on('BeforeSetContent', function(e) {
                    if (e.content.startsWith('<')) return;
                    e.content = '<p>' + e.content.replace(/\n/g, '</p><p>') + '</p>';
                    e.content = e.content.replace(/<p>\s*<\/p>/g, '<p>&nbsp;</p>');
                });

                editor.on('PastePostProcess', function(e) {
                    var content = e.node;

                    // Xử lý bảng
                    var tables = content.getElementsByTagName('table');
                    for (var i = 0; i < tables.length; i++) {
                        var table = tables[i];
                        table.style.width = '100%';
                        table.style.borderCollapse = 'collapse';
                        table.setAttribute('border', '1');
                        var cells = table.getElementsByTagName('td');
                        for (var j = 0; j < cells.length; j++) {
                            var cell = cells[j];
                            cell.style.border = '1px solid #ccc';
                            cell.style.padding = '8px';
                        }
                    }

                    // Xử lý danh sách
                    var lists = content.querySelectorAll('ol, ul');
                    lists.forEach(function(list) {
                        list.style.paddingLeft = '30px';
                        var items = list.getElementsByTagName('li');
                        for (var k = 0; k < items.length; k++) {
                            items[k].style.marginBottom = '5px';
                        }
                    });
                });
            }
        });

        $(form_id).appFormValidator({
            onSubmit: function(form) {
                var content = tinymce.get('request_content').getContent();
                $('#request_content').val(content);

                $("input[type=file]").each(function() {
                    if ($(this).val() === "") {
                        $(this).prop('disabled', true);
                    }
                });

                var formURL = $(form).attr("action");
                var formData = new FormData($(form)[0]);

                $.ajax({
                    type: $(form).attr('method'),
                    data: formData,
                    mimeType: $(form).attr('enctype'),
                    contentType: false,
                    cache: false,
                    processData: false,
                    url: formURL
                }).done(function(response) {
                    try {
                        var parsedResponse = JSON.parse(response);
                        if (parsedResponse.success) {
                            alert_float('success', 'Đề xuất đã được cập nhật thành công.');
                            setTimeout(function() {
                                window.location.href = admin_url + 'approvify/view_request/' + <?php echo $request->id; ?>;
                            }, 1000);
                        } else {
                            alert_float('danger', 'Có lỗi xảy ra khi cập nhật đề xuất: ' + (parsedResponse.message || 'Không có thông báo lỗi cụ thể.'));
                        }
                    } catch (e) {
                        alert_float('danger', 'Có lỗi xảy ra khi xử lý phản hồi từ máy chủ.');
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert_float('danger', 'Có lỗi xảy ra khi gửi yêu cầu: ' + textStatus);
                }).always(function() {
                    // Re-enable the submit button
                    $('#submit-button').prop('disabled', false);
                });

                return false;
            }
        });

        $('.template-option').on('click', function(e) {
            e.preventDefault();
            var templateType = $(this).data('template');
            var template = getTemplate(templateType);
            tinymce.get('request_content').setContent(template);
        });

        function getTemplate(type) {
            switch (type) {
                case 'payment':
                    return '<h2 class="text-center">Đề xuất thanh toán</h2>' +
                        '<p class="indent"><strong>Kính gửi:</strong> Phòng Tài Chính Công Ty Cổ Phần Bệnh Viện Hữu Nghị Quốc Tế Hà Nội</p>' +
                        '<p class="indent"><strong>Họ và tên người đề nghị thanh toán:</strong> [Họ tên người đề xuất]</p>' +
                        '<p class="indent"><strong>Bộ phận:</strong> [Tên phòng ban]</p>' +
                        '<p class="indent"><strong>Nội dung thanh toán:</strong></p>' +
                        '<ul>' +
                        '<li><strong>Chi tiết các khoản chi:</strong></li>' +
                        '</ul>' +
                        '<table class="tg" style="margin-left: auto; margin-right: auto; table-layout: fixed; width: 80%;"><colgroup><col style="width: 40px;"><col style="width: 350px;"><col style="width: 100px;"><col style="width: 110px;"></colgroup>' +
                        '<thead>' +
                        '<tr>' +
                        '<th class="tg-b6t7">STT</th>' +
                        '<th class="tg-b6t7">NỘI DUNG</th>' +
                        '<th class="tg-b6t7">SỐ TIỀN</th>' +
                        '<th class="tg-b6t7">GHI CHÚ</th>' +
                        '</tr>' +
                        '</thead>' +
                        '<tbody>' +
                        '<tr>' +
                        '<td class="tg-amwm">1</td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="tg-amwm">2</td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="tg-amwm">3</td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="tg-amwm">4</td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="tg-amwm">5</td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="tg-amwm">6</td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '<td class="tg-0lax"></td>' +
                        '</tr>' +
                        '</tbody>' +
                        '</table>' +
                        '<p></p>' +
                        '<li><strong>Tổng số tiền đề xuất thanh toán: </strong><span style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen, Ubuntu, Cantarell, \'Open Sans\', \'Helvetica Neue\', sans-serif; font-size: 12pt;">... VNĐ (Viết bằng chữ: ...)</span></li>' +
                        '<p><span style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen, Ubuntu, Cantarell, \'Open Sans\', \'Helvetica Neue\', sans-serif; font-size: 12pt;"></span></p>' +
                        '<p class="indent">Đề nghị thanh toán bằng tiền mặt hoặc chuyển khoản qua tài khoản ngân hàng theo chi tiết sau:</p>' +
                        '<p><strong>- Tên tài khoản:</strong> ...</p>' +
                        '<p><strong>- Số tài khoản:</strong> ...</p>' +
                        '<p><strong>- Ngân hàng:</strong> ...</p>';
                case 'leave':
                    return '<h2 class="text-center">Đơn xin nghỉ phép</h2>' +
                        '<p class="indent"><strong>Kính gửi:</strong> [Tên người quản lý]</p>' +
                        '<p class="indent"><strong>Ngày:</strong> [Ngày tháng năm]</p>' +
                        '<p class="indent">Tôi viết đơn này để xin phép nghỉ [số ngày] ngày, từ ngày [ngày bắt đầu] đến ngày [ngày kết thúc].</p>' +
                        '<p class="indent"><strong>Lý do nghỉ phép:</strong> [Nêu rõ lý do]</p>' +
                        '<p class="indent">Trong thời gian nghỉ, tôi sẽ bàn giao công việc cho [tên đồng nghiệp] để đảm bảo công việc được thực hiện liên tục.</p>' +
                        '<p class="indent">Kính mong [Tên người quản lý] xem xét và chấp thuận.</p>' +
                        '<p class="text-right">Trân trọng,</p>' +
                        '<p class="text-right">[Tên người xin nghỉ phép]</p>';
                case 'report':
                    return '<h2 class="text-center">Báo cáo công việc</h2>' +
                        '<p class="indent"><strong>Người báo cáo:</strong> [Tên nhân viên]</p>' +
                        '<p class="indent"><strong>Ngày báo cáo:</strong> [Ngày tháng năm]</p>' +
                        '<p class="indent"><strong>Khoảng thời gian báo cáo:</strong> Từ [ngày bắt đầu] đến [ngày kết thúc]</p>' +
                        '<h3 class="text-center">1. Công việc đã hoàn thành</h3>' +
                        '<ul>' +
                        '<li>[Công việc 1]</li>' +
                        '<li>[Công việc 2]</li>' +
                        '<li>[Công việc 3]</li>' +
                        '</ul>' +
                        '<h3 class="text-center">2. Công việc đang thực hiện</h3>' +
                        '<ul>' +
                        '<li>[Công việc 1] - Tiến độ: [%]</li>' +
                        '<li>[Công việc 2] - Tiến độ: [%]</li>' +
                        '</ul>' +
                        '<h3 class="text-center">3. Kế hoạch công việc tuần tới</h3>' +
                        '<ul>' +
                        '<li>[Kế hoạch 1]</li>' +
                        '<li>[Kế hoạch 2]</li>' +
                        '</ul>' +
                        '<h3 class="text-center">4. Khó khăn và đề xuất</h3>' +
                        '<p class="indent">[Nêu rõ các khó khăn gặp phải và đề xuất giải pháp]</p>' +
                        '<p class="text-right">Trân trọng,</p>' +
                        '<p class="text-right">[Tên người báo cáo]</p>';
                default:
                    return '';
            }
        }
    });
</script>