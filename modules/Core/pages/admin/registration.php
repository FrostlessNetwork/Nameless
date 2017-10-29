<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr3
 *
 *  License: MIT
 *
 *  Admin index page
 */

if($user->isLoggedIn()){
	if(!$user->canViewACP()){
		// No
		Redirect::to(URL::build('/'));
		die();
	} else {
		// Check the user has re-authenticated
		if(!$user->isAdmLoggedIn()){
			// They haven't, do so now
			Redirect::to(URL::build('/admin/auth'));
			die();
		} else if(!$user->hasPermission('admincp.core.registration')){
            require('404.php');
            die();
        }
	}
} else {
	// Not logged in
	Redirect::to(URL::build('/login'));
	die();
}
 
$page = 'admin';
$admin_page = 'core';

// Deal with input
if(Input::exists()){
	// Check token
	if(Token::check(Input::get('token'))){
		// Valid token
		// Process input
		if(isset($_POST['enable_registration'])){
			// Either enable or disable registration
			$enable_registration_id = $queries->getWhere('settings', array('name', '=', 'registration_enabled'));
			$enable_registration_id = $enable_registration_id[0]->id;
			
			$queries->update('settings', $enable_registration_id, array(
				'value' => Input::get('enable_registration')
			));
		} else {
			// Registration settings
			if(isset($_POST['verification']) && $_POST['verification'] == 'on')
			  $verification = 1;
			else
			  $verification = 0;

			$verification_id = $queries->getWhere('settings', array('name', '=', 'email_verification'));
			$verification_id = $verification_id[0]->id;

            // reCAPCTHA enabled?
            if(Input::get('enable_recaptcha') == 1){
                $recaptcha = 'true';
            } else {
                $recaptcha = 'false';
            }
            $recaptcha_id = $queries->getWhere('settings', array('name', '=', 'recaptcha'));
            $recaptcha_id = $recaptcha_id[0]->id;
            $queries->update('settings', $recaptcha_id, array(
                'value' => $recaptcha
            ));
            // reCAPTCHA key
            $recaptcha_id = $queries->getWhere('settings', array('name', '=', 'recaptcha_key'));
            $recaptcha_id = $recaptcha_id[0]->id;
            $queries->update('settings', $recaptcha_id, array(
                'value' => htmlspecialchars(Input::get('recaptcha'))
            ));
            // reCAPTCHA secret key
            $recaptcha_secret_id = $queries->getWhere('settings', array('name', '=', 'recaptcha_secret'));
            $recaptcha_secret_id = $recaptcha_secret_id[0]->id;
            $queries->update('settings', $recaptcha_secret_id, array(
                'value' => htmlspecialchars(Input::get('recaptcha_secret'))
            ));

			try {
			  $queries->update('settings', $verification_id, array(
			     'value' => $verification
			  ));
			} catch(Exception $e){
			  $error = $e->getMessage();
			}
		}
	} else {
		// Invalid token
		$error = $language->get('general', 'invalid_token');
	}
}

// Check if registration is enabled
$registration_enabled = $queries->getWhere('settings', array('name', '=', 'registration_enabled'));
$registration_enabled = $registration_enabled[0]->value;

// Generate form token
$token = Token::get();

?>
<!DOCTYPE html>
<html lang="<?php echo (defined('HTML_LANG') ? HTML_LANG : 'en'); ?>">
  <head>
    <!-- Standard Meta -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

	<?php 
	$title = $language->get('admin', 'admin_cp');
	require('core/templates/admin_header.php'); 
	?>
	
	<link rel="stylesheet" href="/core/assets/plugins/switchery/switchery.min.css">
  
  </head>
  <body>
    <?php require('modules/Core/pages/admin/navbar.php'); ?>
	<div class="container">
	  <div class="row">
	    <div class="col-md-3">
		  <?php require('modules/Core/pages/admin/sidebar.php'); ?>
		</div>
		<div class="col-md-9">
		  <div class="card">
		    <div class="card-block">
			  <h3><?php echo $language->get('admin', 'registration'); ?></h3>

        <?php if(isset($error)){ ?>
        <div class="alert alert-danger">
          <?php echo $error; ?>
        </div>
			  <?php } ?>

			  <form id="enableRegistration" action="" method="post">
			    <?php echo $language->get('admin', 'enable_registration'); ?>
				<input type="hidden" name="enable_registration" value="0">
			    <input name="enable_registration" type="checkbox" class="js-switch js-check-change"<?php if($registration_enabled == '1'){ ?> checked<?php } ?> value="1" />
				<input type="hidden" name="token" value="<?php echo $token; ?>">
			  </form>
			  
			  <?php
			  if($registration_enabled == '1'){
				  // Is email verification enabled
				  $emails = $queries->getWhere('settings', array('name', '=', 'email_verification'));
				  $emails = $emails[0]->value;

				  // Recaptcha
                  $recaptcha_id = $queries->getWhere('settings', array('name', '=', 'recaptcha'));
                  $recaptcha_key = $queries->getWhere('settings', array('name', '=', 'recaptcha_key'));
                  $recaptcha_secret = $queries->getWhere('settings', array('name', '=', 'recaptcha_secret'));
			  ?>
			  <hr>
			  <form action="" method="post">
				<div class="form-group">
			      <label for="verification"><?php echo $language->get('admin', 'email_verification'); ?></label>
			      <input name="verification" id="verification" type="checkbox" class="js-switch"<?php if($emails == '1'){ ?> checked<?php } ?> />
				</div>
                <div class="form-group">
                  <label for="InputEnableRecaptcha"><?php echo $language->get('admin', 'google_recaptcha'); ?></label>
                  <input id="InputEnableRecaptcha" name="enable_recaptcha" type="checkbox" class="js-switch" value="1"<?php if($recaptcha_id[0]->value == 'true'){ ?> checked<?php } ?> />
                </div>
                <div class="form-group">
                  <label for="InputRecaptcha"><?php echo $language->get('admin', 'recaptcha_site_key'); ?></label>
                  <input type="text" name="recaptcha" class="form-control" id="InputRecaptcha" placeholder="<?php echo $language->get('admin', 'recaptcha_site_key'); ?>" value="<?php echo htmlspecialchars($recaptcha_key[0]->value); ?>">
                </div>
                <div class="form-group">
                  <label for="InputRecaptchaSecret"><?php echo $language->get('admin', 'recaptcha_secret_key'); ?></label>
                  <input type="text" name="recaptcha_secret" class="form-control" id="InputRecaptchaSecret" placeholder="<?php echo $language->get('admin', 'recaptcha_secret_key'); ?>" value="<?php echo htmlspecialchars($recaptcha_secret[0]->value); ?>">
                </div>
				<input type="hidden" name="token" value="<?php echo $token; ?>">
				<input type="submit" class="btn btn-primary" value="<?php echo $language->get('general', 'submit'); ?>">
			  </form>
			  <?php			  
			  }
			  ?>
			  
		    </div>
		  </div>
		</div>
	  </div>
    </div>
	
	<?php require('modules/Core/pages/admin/footer.php'); ?>

    <?php require('modules/Core/pages/admin/scripts.php'); ?>
	
	<script src="/core/assets/plugins/switchery/switchery.min.js"></script>
	
	<script>
	var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
	elems.forEach(function(html) {
	  var switchery = new Switchery(html);
	});
	
	/*
	 *  Submit form on clicking enable/disable registration
	 */
	var changeCheckbox = document.querySelector('.js-check-change');

	changeCheckbox.onchange = function() {
	  $('#enableRegistration').submit();
	};
	
	</script>
	
  </body>
</html>