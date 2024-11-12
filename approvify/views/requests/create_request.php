<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div id="response"></div>
            <?php echo form_open(current_full_url(), ['id' => 'requestForm', 'class' => 'disable-on-submit']); ?>
            <div class="col-md-12">
                <h2>
                    <?php echo isset($type_data) ? $type_data->category_name : ''; ?>
                </h2>
                <p><?php echo isset($type_data) ? $type_data->category_description : ''; ?></p>
            </div>

            <div class="col-md-9">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <?php echo render_input('request_title', 'approvify_request_title'); ?>
                        </div>

                        <div class="col-md-12">
                            <?php echo render_textarea('request_content', 'approvify_request_content', '', ['rows' => 10], [], '', 'tinymce'); ?>
                        </div>

                        <div class="col-md-12">
                            <div class="dropdown pull-left">
                                <button class="btn btn-info dropdown-toggle" type="button" id="templateDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Chọn mẫu đề xuất
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="templateDropdown">
                                    <li><a href="#" class="template-option" data-template="payment">Đề xuất thanh toán</a></li>
                                    <li><a href="#" class="template-option" data-template="dexuatchung">Đề xuất chung</a></li>
                                    <!-- <li><a href="#" class="template-option" data-template="report">Báo cáo công việc</a></li> -->
                                </ul>
                            </div>
                            <button type="submit" id="submit-button" class="btn btn-primary saveDocument pull-right"><?php echo _l('approvify_create_request'); ?></button>
                        </div>
                    </div>
                </div>
                <p><b><span style="color: red;">Lưu ý: Vui lòng kiểm tra lại nội dung trước khi bấm gửi phê duyệt.</span></b></p>
            </div>

            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="col-md-12 mbot10">
                            <div class="new-attachment">
                                <label for="attachment" class="control-label"><?php echo _l('up_load_new_file'); ?></label>
                                <div class="input-group">
                                    <input type="file" extension="<?php echo str_replace('.', '', get_option('ticket_attachments_file_extensions')); ?>" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="attachments[]" accept="<?php echo get_ticket_form_accepted_mimes(); ?>" multiple>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                        </div>
                    </div>
                </div>
            </div>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>


</body>
<script>
    "use strict";

    var form_id = '#requestForm';

    $(function () {
        // Cấu hình TinyMCE

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
          setup: function (editor) {
            editor.on('BeforeSetContent', function (e) {
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

        // Xử lý gửi form

        $(form_id).appFormValidator({
            onSubmit: function (form) {
                $("input[type=file]").each(function () {
                    if ($(this).val() === "") {
                        $(this).prop('disabled', true);
                    }
                });
                $('#form_submit .fa-spin').removeClass('hide');

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
                }).always(function () {
                    $('#form_submit').prop('disabled', false);
                    $('#form_submit .fa-spin').addClass('hide');
                }).done(function (response) {
                    response = JSON.parse(response);
                    if (response.redirect_url) {
                        if (window.top) {
                            window.top.location.href = response.redirect_url;
                        } else {
                            window.location.href = response.redirect_url;
                        }
                        return;
                    }
                    if (response.success == false) {
                        $('#recaptcha_response_field').html(response.message);
                    } else if (response.success == true) {
                        $(form_id).remove();
                        $('#response').html('<div class="alert alert-success" style="margin-bottom:0;">' + response.message + '</div>');
                        $('html,body').animate({
                            scrollTop: $("#online_payment_form").offset().top
                        }, 'slow');
                    } else {
                        $('#response').html('Something went wrong...');
                    }
                    if (typeof (grecaptcha) != 'undefined') {
                        grecaptcha.reset();
                    }
                }).fail(function (data) {
                    if (typeof (grecaptcha) != 'undefined') {
                        grecaptcha.reset();
                    }
                    if (data.status == 422) {
                        $('#response').html('<div class="alert alert-danger">Some fields that are required are not filled properly.</div>');
                    } else {
                        $('#response').html(data.responseText);
                    }
                });
                return false;
            }
        });

        // Xử lý sự kiện click cho các option mẫu
        $('.template-option').on('click', function(e) {
            e.preventDefault();
            var templateType = $(this).data('template');
            var template = getTemplate(templateType);
            tinymce.get('request_content').setContent(template);
        });

        function getTemplate(type) {
            switch(type) {
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
                            '<p><strong>- Ngân hàng:</strong> ...</p>' ;
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
                case 'dexuatchung':
                    return '<h2 class="text-center">Phiếu đề xuất</h2>' +
                           '<p class="indent"><strong>Số:</strong> [Số phiếu]</p>' +
                           '<p class="indent"><strong>Ngày:</strong> [Ngày tháng năm]</p>' +
                           '<p class="indent"><strong>Kính gửi:</strong> [Tên người/bộ phận nhận đề xuất]</p>' +
                           '<p class="indent"><strong>Người đề xuất:</strong> [Tên người đề xuất]</p>' +
                           '<p class="indent"><strong>Bộ phận:</strong> [Tên bộ phận]</p>' +
                           '<p class="indent"><strong>Nội dung đề xuất:</strong> [Tóm tắt nội dung đề xuất]</p>' +
                           '<table class="table table-bordered">' +
                           '<thead>' +
                           '<tr>' +
                           '<th class="tg-b6t7">STT</th>' +
                           '<th class="tg-b6t7">NỘI DUNG</th>' +
                           '<th class="tg-b6t7">MÔ TẢ CHI TIẾT</th>' +
                           '<th class="tg-b6t7">SL</th>' +
                           '<th class="tg-b6t7">NHÀ CUNG CẤP</th>' +
                           '<th class="tg-b6t7">THỜI GIAN CẦN</th>' +
                           '</tr>' +
                           '</thead>' +
                           '<tbody>' +
                           
                           '</thead>' +
                           '<tbody>' +
                           '<tr><td>1</td><td></td><td></td><td></td><td></td><td></td></tr>' +
                           '<tr><td>2</td><td></td><td></td><td></td><td></td><td></td></tr>' +
                           '<tr><td>3</td><td></td><td></td><td></td><td></td><td></td></tr>' +
                           '<tr><td>4</td><td></td><td></td><td></td><td></td><td></td></tr>' +
                           '<tr><td>5</td><td></td><td></td><td></td><td></td><td></td></tr>' +
                           '</tbody>' +
                           '</table>' +
                           '<p class="indent">Kính mong [Tên người/bộ phận nhận đề xuất] duyệt!</p>';
          
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

// xác nhận trước khi gửi phê duyệt
document.getElementById('submit-button').addEventListener('click', function(event) {
    var confirmMessage = "Bạn đã chắc chắn nhập đúng, đủ nội dung bao gồm các tập tin đính kèm nếu có chưa? Bấm OK để hoàn tất gửi phiếu đề xuất hoặc Hủy bỏ để kiểm tra lại.";
    if (!confirm(confirmMessage)) {
        event.preventDefault(); // Ngăn chặn việc gửi biểu mẫu nếu người dùng nhấp vào "Hủy"
    }
});


</script>

<style>
    .request-content h1, .request-content h2, .request-content h3 { text-align: center; }
    .request-content p { text-indent: 20px; }
    .request-content .text-center { text-align: center; }
    .request-content .text-right { text-align: right; }
    .request-content .indent { text-indent: 20px; }

    .request-content ol {
        counter-reset: item;
        padding-left: 20px;
    }

    .request-content ol li {
        display: block;
        position: relative;
        margin: 10px 0;
    }

    .request-content ol li:before {
        content: counters(item, ".") " ";
        counter-increment: item;
        position: absolute;
        left: -2em;
    }

    .request-content ul {
        list-style-type: none;
        padding-left: 20px;
    }

    .request-content ul li {
        display: block;
        position: relative;
        margin: 5px 0;
    }

    .request-content ul li:before {
        content: "-";
        position: absolute;
        left: -1em;
    }
</style>

</html>