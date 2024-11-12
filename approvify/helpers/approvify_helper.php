<?php

function approvify_return_request_status_html($statusId)
{
    $status = '';

    if ($statusId == 0) {
        $status = '<span class="label project-status-2" style="color:#94a3b8;border:1px solid #d4dae3;background: #fbfcfc;">' . _l('approvify_submitted_status') . '</span>';
    }
    if ($statusId == 1) {
        $status = '<span class="label project-status-5" style="color:#069e10;border:1px solid #6fdb65;background: #fbfcfc;">' . _l('approvify_approved_status') . '</span>';
    }
    if ($statusId == 2) {
        $status = '<span class="label project-status-5" style="color:#99062d;border:1px solid #ba246b;background: #fbfcfc;">' . _l('approvify_refused_status') . '</span>';
    }
    if ($statusId == 3) {
        $status = '<span class="label project-status-5" style="color:#d78d59;border:1px solid #d2ac97;background: #fbfcfc;">' . _l('approvify_canceled_status') . '</span>';
    }

    return $status;
}

function approvify_handle_request_attachments($requestId, $index_name = 'attachments')
{
    $path = FCPATH . 'modules/approvify/uploads/requests/' . $requestId . '/';
    $uploaded_files = [];

    if (isset($_FILES[$index_name])) {
        _file_attachments_index_fix($index_name);

        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            if ($i <= 5) {
                // Get the temp file path
                $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    // Getting file extension
                    $extension = strtolower(pathinfo($_FILES[$index_name]['name'][$i], PATHINFO_EXTENSION));

                    $allowed_extensions = explode(',', get_option('ticket_attachments_file_extensions'));
                    $allowed_extensions = array_map('trim', $allowed_extensions);
                    // Check for all cases if this extension is allowed
                    if (!in_array('.' . $extension, $allowed_extensions)) {
                        continue;
                    }
                    _maybe_create_upload_path($path);
                    $filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                    $newFilePath = $path . $filename;
                    // Upload the file into the temp dir
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        array_push($uploaded_files, [
                            'file_name' => $filename,
                            'filetype'  => $_FILES[$index_name]['type'][$i],
                        ]);
                    }
                }
            }
        }
    }
    if (count($uploaded_files) > 0) {
        return $uploaded_files;
    }

    return false;
}