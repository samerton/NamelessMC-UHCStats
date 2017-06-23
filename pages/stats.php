<?php 
/* 
 *	Made by relavis and Samerton
 *
 *  License: MIT
 */

// Stats addon page
$page = 'Stats'; // for navbar

// Ensure the addon is enabled
if(!in_array('Stats', $enabled_addon_pages)){
	// Not enabled, redirect to homepage
	echo '<script data-cfasync="false">window.location.replace(\'/\');</script>';
	die();
}

require('core/includes/paginate.php');
require('addons/Stats/config.php');
require('addons/Stats/Stats.php');
require('core/integration/uuid.php');

$stats = new Stats($inf_db, $stats_language);
$pagination = new Pagination();
$timeago = new Timeago();


// Redirect to fix pagination if URL does not end in /
if(substr($_SERVER['REQUEST_URI'], -1) !== '/' && !strpos($_SERVER['REQUEST_URI'], '?')){
	echo '<script data-cfasync="false">window.location.replace(\'/stats/\');</script>';
	die();
}

// Get page number
if(isset($_GET['p'])){
	if(!is_numeric($_GET['p'])){
		Redirect::to('/stats/');
		die();
	} else {
		if($_GET['p'] == 1){ 
			// Avoid bug in pagination class
			Redirect::to('/stats/');
			die();
		}
		$p = $_GET['p'];
	}
} else {
	$p = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Stats page for the <?php echo $sitename; ?> community">
    <meta name="author" content="<?php echo $sitename; ?>">
    <meta name="theme-color" content="#454545" />
	<?php if(isset($custom_meta)){ echo $custom_meta; } ?>

	<?php
	// Generate header and navbar content
	// Page title
	$title = $stats_language['stats'];
	
	require('core/includes/template/generate.php');
	?>
	
	<!-- Custom style -->
	<style>
	html {
		overflow-y: scroll;
	}
	</style>
	
  </head>

  <body>
    <?php
	// Load navbar
	$smarty->display('styles/templates/' . $template . '/navbar.tpl');
	?>
	
    <div class="container">	
	  <div class="well">
        <h2><?php echo $stats_language['stats']; ?></h2>
		<?php
		if(!isset($_GET['id'])){
			// Check cache
			$c->setCache('stats_cache');
			if($c->isCached('stats')){
				$all_stats = $c->retrieve('stats');
			} else {
				$all_stats = $stats->getAllStats();
				
				$c->store('stats', $all_stats, 120);
			}

			// Pagination
			$paginate = PaginateArray($p);
			
			$n = $paginate[0];
			$f = $paginate[1];
			
			if(count($all_stats) > $f){
				$d = $p * 10;
			} else {
				$d = count($all_stats) - $n;
				$d = $d + $n;
			}
		?>
	    <div class="table-responsive">
		  <table class="table table-bordered">
		    <colgroup>
		      <col span="1" style="width: 15%;">
		      <col span="1" style="width: 15%;">
		      <col span="1" style="width: 15%">
		      <col span="1" style="width: 45%">
		      <col span="1" style="width: 10%">
		    </colgroup>
		    <thead>
		      <tr>
			    <td><?php echo $user_language['username']; ?></td>
				<td><?php echo $stats_language['wins']; ?></td>
			    <td><?php echo $stats_language['kills']; ?></td>
			    <td><?php echo $stats_language['deaths']; ?></td>
			    <td><?php echo $stats_language['kd']; ?></td>
		      </tr>
		    </thead>
		    <tbody>
			<?php 
			while($n < $d){
				if(isset($mcname)) unset($mcname);
				
				$stat = $all_stats[$n];

				$stats_query = $queries->getWhere('users', array('uuid', '=', str_replace('-', '', $stat["uuid"])));
				
				if(empty($stats_query)){

					$stats_query = $queries->getWhere('uuid_cache', array('uuid', '=', str_replace('-', '', $stat["uuid"])));
					
					if(empty($stats_query)){
						// Query Minecraft API to retrieve username
						$profile = ProfileUtils::getProfile(str_replace('-', '', $stat["uuid"]));
						if(empty($profile)){
							// Couldn't find player
							
						} else {
							$result = $profile->getProfileAsArray();
								if(isset($result['username'])){
								$mcname = htmlspecialchars($result["username"]);
								$uuid = htmlspecialchars(str_replace('-', '', $stat["uuid"]));
								try {
									$queries->create("uuid_cache", array(
										'mcname' => $mcname,
										'uuid' => $uuid
									));
								} catch(Exception $e){
									die($e->getMessage());
								}
							}
						}
					}
					$mcname = $queries->getWhere('uuid_cache', array('uuid', '=', str_replace('-', '', $stat["uuid"])));
					if(count($mcname))
						$mcname = $mcname[0]->mcname;
					else
						$mcname = 'Unknown';

				} else {
					$mcname = $stats_query[0]->mcname;
				}
			
			?>
		      <tr>
			    <td><a href="/profile/<?php echo htmlspecialchars($mcname); ?>"><?php echo htmlspecialchars($mcname); ?></a></td>
				<td><?php echo $stat["wins"]; ?></td>
				<td><?php echo $stat["kills"]; ?></td>
				<td><?php echo $stat["deaths"]; ?></td>
				<td><?php echo $stat["kd"]; ?></td>
		      </tr>
			<?php
				$n++;
			}
			?>
		    </tbody>
		  </table>
		</div>
			<?php
			$pagination->setCurrent($p);
			$pagination->setTotal(count($all_stats));
			$pagination->alwaysShowPagination();
			
			echo $pagination->parse();
		} else {
			
			if(!isset($_GET['id'])){
				Redirect::to('/stats');
				die();
			}
			
			// Get stat info, depending on plugin

					$stat = $stats->getStat($_GET["id"]);
					
					// Get username from UUID
					// First check if they're registered on the site
					$username = $queries->getWhere('users', array('uuid', '=', $stat[0]->UUID));
					if(!count($username)){
						// Couldn't find, check UUID cache
						$username = $queries->getWhere('uuid_cache', array('uuid', '=', $stat[0]->UUID));
						
						if(!count($username)){
							// Couldn't find, check database
							$username = $stats->getUsernameFromUUID($stat[0]->UUID);
							
							if(!count($username)){
								// Couldn't find, get and put into cache
								$profile = ProfileUtils::getProfile($stat[0]->UUID);
								if(empty($profile)){
									echo 'Could not find that player, please try again later.';
									die();
								} else {
									// Enter into database
									$result = $profile->getProfileAsArray();
									$username = htmlspecialchars($result["username"]);
									$uuid = htmlspecialchars($stat[0]->UUID);
									try {
										$queries->create("uuid_cache", array(
											'mcname' => $username,
											'uuid' => $uuid
										));
									} catch(Exception $e){
										die($e->getMessage());
									}
								}
								
							} else { 
								$username = htmlspecialchars($username[0]->player);
							}
						} else {
							$username = htmlspecialchars($username[0]->mcname);
						}
					} else {
						$username = htmlspecialchars($username[0]->mcname);
					}
					
			
			if(!isset($stat) || !isset($username)){
				echo '<script data-cfasync="false">window.location.replace("/stats");</script>';
				die();
			}
			?>
		  <hr />
		  <h4 style="display:inline;"><?php echo $stats_language['viewing_stat']; ?></h4>
		  <span class="pull-right"><a class="btn btn-primary" href="/stats"><?php echo $general_language['back']; ?></a></span>
		  <br /><br />
		  <?php echo $stats_language['user'] . ' <strong><a href="/profile/' . $username . '">' . $username . '</a></strong>'; ?><br />
		  <?php echo $stats_language['wins'] . ': ' . $stat->wins; ?><br />
		  <?php echo $stats_language['kills'] . ': ' . $stat->kills; ?><br />
		  <?php echo $stats_language['deaths'] . ': ' . $stat->deaths; ?><br />
		  <?php echo $stats_language['kd'] . ': ' . $stat->kd; ?><br />
		  <hr />
		  <?php
		}
		?>
	  </div>
    </div>
	
	<?php
	// Footer
	require('core/includes/template/footer.php');
	$smarty->display('styles/templates/' . $template . '/footer.tpl');
	
	// Scripts 
	require('core/includes/template/scripts.php');
	?>

  </body>
</html>
