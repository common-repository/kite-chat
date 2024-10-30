<?php
/*
  Plugin Name: Kite Chat
  Plugin URI:
  Description:
  Author: AntBuddy
  Author URI: https://antbuddy.com/
  Text Domain: kitechat
  Version: 1.0.0
*/


/*
 * Variables
 *
 * Assignments are for default value -- change on admin page.
 */

$kitechat_options =
	array(
		  'kitechat_appid' => ''

		  , 'kitechat_type' => 'bar'

		  , 'kitechat_bg' => '519500'

		  , 'kitechat_text' => 'fff'

		  , 'kitechat_label' => "Let's chat"

		  , 'kitechat_title' => 'Hello, we are here to help!'

		  );


/*
 * Startup
 */

add_action('plugins_loaded', 'kitechat_setup');

/*
 * Functions start here
 */

/* Get options and setup filters & actions */
function kitechat_setup() {
	load_plugin_textdomain('kitechat', false
			       , dirname(plugin_basename(__FILE__)));
	kitechat_setup_options ();

	add_action('admin_menu', 'kitechat_menu');
	add_action( 'wp_footer', 'kitechat_footer_script' );
}

function kitechat_footer_script () {
	$appId = kitechat_option('kitechat_appid');
	$kitechatType = kitechat_option('kitechat_type');
	$kitechatBg = kitechat_option('kitechat_bg');
	$kitechatText = kitechat_option('kitechat_text');
	$kitechatLabel = kitechat_option('kitechat_label');
	$kitechatTitle = kitechat_option('kitechat_title');
	?>
	<script type="text/javascript">
	(function () {
        var width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
          type = "<?= $kitechatType ?>", colors = { background: "#<?=$kitechatBg?>", text: "#<?=$kitechatText?>"};

        if (width <= 991) type = 'circle';
        window.abKiteAsyncInit = function() {
          abKiteSDK.init({
            appId: "<?=$appId?>",
            abKiteServer: 'kite.antbuddy.com',
            insert2Selector: 'body',
            language: 'en',
            type: type,
            helloMessage: "<?=$kitechatLabel?>",
            colors: colors,
            title: "<?=$kitechatTitle?>"
          });
        };
    })();

    (function(d, s, id){
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) {return;}
      js = d.createElement(s); js.id = id;
      js.src = '//kite.antbuddy.com/sdk/v0.0.0/sdk.js';
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'ab-kite-jssdk'));
		</script>
	<?php
}
/* Add admin options page */
function kitechat_menu() {
	global $wp_version;

	// Add color picker WP >= 3.5
	if (version_compare($wp_version, '3.5', '>=')) {
		wp_enqueue_style( 'wp-color-picker');
		wp_enqueue_script('ab_script', plugins_url( 'kite.script.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), '', true);
	}

	// Modern WP?
	if (version_compare($wp_version, '3.0', '>=')) {
	    add_options_page('Kite Chat', 'Kite Chat', 'manage_options', 'kitechat-options', 'kitechat_option_page');
	    return;
	}

	// Older WPMU?
	if (function_exists("get_current_site")) {
	    add_submenu_page('wpmu-admin.php', 'Kite Chat', 'Kite Chat', 9, 'kitechat-options', 'kitechat_option_page');
	    return;
	}

	// Older WP
	add_options_page('Kite Chat', 'Kite Chat', 9, 'kitechat-options', 'kitechat_option_page');
}

/* Get current option value */
function kitechat_option($option_name) {
	global $kitechat_options;

	if (isset($kitechat_options[$option_name])) {
		return stripslashes($kitechat_options[$option_name]);
	} else {
		return null;
	}
}

/* Update options in db from global variables */
function kitechat_update_options() {
	update_option('kitechat_appid', kitechat_option('kitechat_appid'));
	update_option('kitechat_type', kitechat_option('kitechat_type'));
	update_option('kitechat_bg', kitechat_option('kitechat_bg'));
	update_option('kitechat_text', kitechat_option('kitechat_text'));
	update_option('kitechat_label', kitechat_option('kitechat_label'));
	update_option('kitechat_title', kitechat_option('kitechat_title'));
}

/* Only change var if option exists */
function kitechat_get_option($option) {
	$a = get_option($option);

	if ($a !== false) {
		global $kitechat_options;

		$kitechat_options[$option] = stripslashes($a);
	}
}

/* Setup global variables from options */
function kitechat_setup_options() {
	kitechat_get_option('kitechat_appid');
	kitechat_get_option('kitechat_type');
	kitechat_get_option('kitechat_bg');
	kitechat_get_option('kitechat_text');
	kitechat_get_option('kitechat_label');
	kitechat_get_option('kitechat_title');
}

// Actual option page
function kitechat_option_page() {
	if (!current_user_can('manage_options')) {
		wp_die('Sorry, but you do not have permissions to change settings.');
	}

	/* Make sure post was from this page */
	// if (count($_POST) > 0) {
	// 	check_admin_referer('limit-login-attempts-options');
	// }

	/* Should we update options? */
	if (isset($_POST['update_options'])) {
		global $kitechat_options;

		$kitechat_options['kitechat_appid'] = $_POST['kitechat_appid'];
		$kitechat_options['kitechat_type'] = $_POST['kitechat_type'];
		$kitechat_options['kitechat_bg'] = str_replace("#", "", $_POST['kitechat_bg']);
		$kitechat_options['kitechat_text'] = str_replace("#", "", $_POST['kitechat_text']);
		$kitechat_options['kitechat_label'] = $_POST['kitechat_label'];
		$kitechat_options['kitechat_title'] = $_POST['kitechat_title'];

		// kitechat_sanitiz_variables();
		kitechat_update_options();
		echo '<div id="message" class="updated fade"><p>'
			. __('Options changed', 'kitechat')
			. '</p></div>';
	}
	$appId = kitechat_option('kitechat_appid');
	$kitechatType = kitechat_option('kitechat_type');
	$kitechatBg = kitechat_option('kitechat_bg');
	$kitechatText = kitechat_option('kitechat_text');
	$kitechatLabel = kitechat_option('kitechat_label');
	$kitechatTitle = kitechat_option('kitechat_title');

	?>
	<form method="post" action="options-general.php?page=kitechat-options">
		<h1><?php echo __('Kite Chat Setting', 'kitechat'); ?></h1>
		<table class="form-table">
			<tbody>
			<tr>
				<th><label for="kitechat_appid"><?php echo __('Kite App ID', 'kitechat'); ?></label></th>
				<td>
					<input type='text' name="kitechat_appid" id="kitechat_appid" placeholder="<?php echo __('Kite App ID');?>" value="<?=$appId ?>" class="regular-text code" />
				</td>
			</tr>
			<tr>
				<th><label for="kitechat_type"><?php echo __('Type', 'kitechat');?></label></th>
				<td>
					<select name="kitechat_type" id="kitechat_type">
						<option value="bar" <?=($kitechatType==='bar')?"selected":""?>><?php echo __('Bar', 'kitechat');?></option>
						<option value="circle" <?=($kitechatType==='circle')?"selected":""?>><?php echo __('Circle', 'kitechat');?></option>
						<option value="rounded-rect" <?=($kitechatType==='rounded-rect')?"selected":""?>><?php echo __('Rounded Rectangle', 'kitechat');?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="kitechat_bg"><?php echo __('Background Color', 'kitechat');?></label></th>
				<td>
					<input type='text' name="kitechat_bg" id="kitechat_bg" placeholder="<?php echo __('Background Color', 'kitechat');?>" value="#<?=$kitechatBg ?>" class="regular-text code cpa-color-picker" />
				</td>
			</tr>
			<tr>
				<th><label for="kitechat_color"><?php echo __('Text Color', 'kitechat');?></label></th>
				<td>
					<input type='text' name="kitechat_text" id="kitechat_text" placeholder="<?php echo __('Text Color', 'kitechat');?>" value="#<?=$kitechatText ?>" class="regular-text code cpa-color-picker" />
				</td>
			</tr>
			<tr>
				<th><label for="kitechat_label"><?php echo __('Label', 'kitechat');?></label></th>
				<td>
					<input type='text' name="kitechat_label" id="kitechat_label" placeholder="<?php echo __('Label', 'kitechat');?>" value="<?=$kitechatLabel ?>" class="regular-text code" />
				</td>
			</tr>
			<tr>
				<th><label for="kitechat_title"><?php echo __('Title', 'kitechat');?></label></th>
				<td>
					<input type='text' name="kitechat_title" id="kitechat_title" placeholder="<?php echo __('Title', 'kitechat');?>" value="<?=$kitechatTitle ?>" class="regular-text code" />
				</td>
			</tr>
			</tbody>
		</table>
		<input type="submit" name="update_options" class="button button-primary" value="<?php echo __('Save change', 'kitechat');?>" />
	</form>
<?php
}
?>
