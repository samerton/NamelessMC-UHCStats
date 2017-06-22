<?php 
/*
 *	Made by Samerton
 *  https://worldscapemc.com
 *
 *  License: MIT
 */

// Settings for the Stats addon

// Ensure user is logged in, and is admin
if($user->isLoggedIn()){
	if($user->canViewACP($user->data()->id)){
		if($user->isAdmLoggedIn()){
			// Can view
		} else {
			Redirect::to('/admin');
			die();
		}
	} else {
		Redirect::to('/');
		die();
	}
} else {
	Redirect::to('/');
	die();
}
?>

<h3>Addon: Stats</h3>
Author: andrewcbaker<br />
Version: 1.0<br />
Description: Show stats for UHC<br />

<h3>Stats Settings</h3>
<?php
	if(Input::exists()){
		if(Token::check(Input::get('token'))){
			
			if(Input::get('action') == 'database'){
				// Update database details
				// Validate input
				$validate = new Validate();
				
				$validation = $validate->check($_POST, array(
					'dbaddress' => array(
						'required' => true
					),
					'dbname' => array(
						'required' => true
					),
					'dbusername' => array(
						'required' => true
					)
				));
				
				if($validation->passed()){
					// Check config is writable
					// Generate config path
					$config_path = join(DIRECTORY_SEPARATOR, array('addons', 'Stats', 'config.php'));
					
					if(is_writable($config_path)){
						// Writable
						
						// Make string to input
						$input_string = '<?php' . PHP_EOL . 
										'$inf_db = array(' . PHP_EOL .
										'    \'address\' => \'' . str_replace('\'', '\\\'', Input::get('dbaddress')) . '\',' . PHP_EOL .
										'    \'name\' => \'' . str_replace('\'', '\\\'', Input::get('dbname')) . '\',' . PHP_EOL .
										'    \'username\' => \'' . str_replace('\'', '\\\'', Input::get('dbusername')) . '\',' . PHP_EOL .
										'    \'password\' => \'' . str_replace('\'', '\\\'', Input::get('dbpassword')) . '\',' . PHP_EOL .
										'    \'prefix\' => \'' . str_replace('\'', '\\\'', Input::get('dbprefix')) . '\'' . PHP_EOL .
										');';
						
						$file = fopen($config_path, 'w');
						fwrite($file, $input_string);
						fclose($file);
						
					} else {
						// Not writable
						Session::flash('admin_stats', '<div class="alert alert-danger">Your <strong>addons/Stats/config.php</strong> file is not writable. Please check file permissions.</div>');
					}
				}
				
			}
		} else {
			// Invalid token
			Session::flash('admin_stats', '<div class="alert alert-danger">' . $admin_language['invalid_token'] . '</div>');
		}
	}
	
	// Display settings
	if(Session::exists('admin_stats')){
		echo Session::flash('admin_stats');
	}
	
	// Generate token
	$token = Token::generate();
	
	require(join(DIRECTORY_SEPARATOR, array('addons', 'Stats', 'config.php')));
?>
<form action="" method="post">
  <div class="form-group">
	<label for="InputLinkPosition"><?php echo $admin_language['page_link_location']; ?></label>
	<?php
	// Get position of link
	$c->setCache('statsaddon');
	if($c->isCached('linklocation')){
		$link_location = $c->retrieve('linklocation');
	} else {
		$c->store('linklocation', 'footer');
		$link_location = 'footer';
	}
	?>
	<select name="linkposition" id="InputLinkPosition" class="form-control">
	  <option value="navbar" <?php if($link_location == 'navbar'){ echo 'selected="selected"'; } ?>><?php echo $admin_language['page_link_navbar']; ?></option>
	  <option value="more" <?php if($link_location == 'more'){ echo 'selected="selected"'; } ?>><?php echo $admin_language['page_link_more']; ?></option>
	  <option value="footer" <?php if($link_location == 'footer'){ echo 'selected="selected"'; } ?>><?php echo $admin_language['page_link_footer']; ?></option>
	  <option value="none" <?php if($link_location == 'none'){ echo 'selected="selected"'; } ?>><?php echo $admin_language['page_link_none']; ?></option>
	</select>
  </div>
  <input type="hidden" name="action" value="settings">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <input type="submit" class="btn btn-primary" value="<?php echo $general_language['submit']; ?>">
</form>
<hr />
<form action="" method="post">
  <strong>Update Database Settings</strong><br /><br />
  <div class="form-group">
    <label for="InputDatabaseAddress">Database Address</label>
	<input type="text" id="InputDatabaseAddress" name="dbaddress" class="form-control" placeholder="Address" value="<?php echo $inf_db['address']; ?>">
  </div>
  <div class="form-group">
    <label for="InputDatabaseName">Database Name</label>
	<input type="text" id="InputDatabaseName" name="dbname" class="form-control" placeholder="Name" value="<?php echo $inf_db['name']; ?>">
  </div>
  <div class="form-group">
    <label for="InputTablePrefix">Table Prefix (with trailing _)</label>
	<input type="text" id="InputTablePrefix" name="dbprefix" class="form-control" placeholder="Prefix" value="<?php echo $inf_db['prefix']; ?>">
  </div>
  <div class="form-group">
    <label for="InputDatabaseUsername">Database Username</label>
	<input type="text" id="InputDatabaseUsername" name="dbusername" class="form-control" placeholder="Hidden">
  </div>
  <div class="form-group">
    <label for="InputDatabasePassword">Database Password</label>
	<input type="password" id="InputDatabasePassword" name="dbpassword" class="form-control" placeholder="Hidden">
  </div>
  <input type="hidden" name="action" value="database">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <input type="submit" class="btn btn-primary" value="<?php echo $general_language['submit']; ?>">
</form>

<?php

