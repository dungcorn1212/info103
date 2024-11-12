<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
  <title><?php echo _l('recruitment_form'); ?></title>
  <?php app_external_form_header($form); ?>
  <?php hooks()->do_action('app_web_to_lead_form_head'); ?>
  <style>
    /* CSS cho phản hồi */
#response {
  margin-bottom: 20px;
}

/* CSS cho các form */
.disable-on-submit {
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* CSS cho các trường form */
.form-group {
  margin-bottom: 20px;
}

/* CSS cho nhãn của trường form */
.form-group label {
  font-weight: bold;
}

/* CSS cho input và textarea */
.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

/* CSS cho nút submit */
.submit-btn-wrapper {
  text-align: center;
}

.submit-btn-wrapper button {
  padding: 10px 20px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.submit-btn-wrapper button:hover {
  background-color: #45a049;
}
h1.tuyendung_tieude {
    font-weight: bold;
    color: #2a7b59;
}

  </style>
</head>
<body class="web-to-lead <?php echo $form->form_key; ?>"<?php if(is_rtl(true)){ echo ' dir="rtl"';} ?>>
  <div class="container">
    <!-- <div class="container-fluid"> -->
    <div class="row justify-content-md-center">
      <div class="<?php if($this->input->get('col')){echo $this->input->get('col');} else {echo 'col-md-12';} ?>">
        <div id="response"></div>
        <center><img src="https://info.hyh.vn/ducna/images/logo.png" class="img-responsive" alt="HYH &amp; AHIB"></center> <br>
        <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>$form->form_key,'class'=>'disable-on-submit')); ?>
        <?php hooks()->do_action('web_to_lead_form_start'); ?>
        <?php echo form_hidden('key',$form->form_key); ?>
        <?php echo form_hidden('rec_campaignid', $rec_campaignid); ?>
        <div class="row">
          <?php foreach($form_fields as $field){
           render_form_builder_field($field);
         } ?>
         <?php if(get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '' && $form->recaptcha == 1){ ?>
         <div class="col-md-12">
           <div class="form-group"><div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_site_key'); ?>"></div>
           <div id="recaptcha_response_field" class="text-danger"></div>
         </div>
         <?php } ?>
         <?php if (is_gdpr() && get_option('gdpr_enable_terms_and_conditions_lead_form') == 1) { ?>
         <div class="col-md-12">
          <div class="checkbox chk">
            <input type="checkbox" name="accept_terms_and_conditions" required="true" id="accept_terms_and_conditions" <?php echo set_checkbox('accept_terms_and_conditions', 'on'); ?>>
            <label for="accept_terms_and_conditions">
              <?php echo _l('gdpr_terms_agree', terms_url()); ?>
            </label>
          </div>
        </div>
        <?php } ?>
         <div class="clearfix"></div>
         <div class="text-left col-md-12 submit-btn-wrapper">
          <button class="btn btn-success" id="form_submit" type="submit"><?php echo $form->submit_btn_name; ?></button>
        </div>
      </div>

      <?php hooks()->do_action('web_to_lead_form_end'); ?>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>
<?php app_external_form_footer($form); ?>

<?php require 'modules/recruitment/assets/js/channel_form_js.php';?>
<?php hooks()->do_action('app_web_to_lead_form_footer'); ?>
</body>
</html>
