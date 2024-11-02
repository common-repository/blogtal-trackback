<?php
/*
Plugin Name: Blogtal Trackback
Plugin URI: http://www.snippetit.com/2009/04/wordpress-plugin-blogtal-trackback/
Description: This plugin will send trackback to www.blogtal.com ONLY when user publish a post or change the post status to publish.
Version: 1.1
Author: Low Sze Hau
Author URI: http://www.szehau.com/

*/
/* Copyright 2009 Low Sze Hau  (email : szehau.weblog@gmail.com) */

define( 'BLOGTAL_TRACKBACK_SECRET_ID_OPTION', 'blogtal_trackback_secrect_id_option' );
define( 'BLOGTAL_TRACKBACK_DEFAULT_OPTION', 'blogtal_trackback_default_option' );
define( 'BLOGTAL_TRACKBACK_URL', 'http://trackback.blogtal.com/' );

function blogtal_tb_activate() {
	add_option( BLOGTAL_TRACKBACK_SECRET_ID_OPTION, '' );
	add_option( BLOGTAL_TRACKBACK_DEFAULT_OPTION, 'true' );
}

function blogtal_tb_admin_menu_action() {
	// Add option page
	add_options_page( "Blogtal Trackback", "Blogtal Trackback", 10, __FILE__, 'blogtal_tb_admin_menu_option' );
	
	// Add meta box
	if( function_exists( 'add_meta_box' ) ) {
		add_meta_box('blogtal_tb', 'Blogtal Trackback', 'blogtal_tb_meta_box', 'post');
	} else {
		add_action('dbx_post_advanced', 'blogtal_tb_meta_box_old' );
	}
}

/* Prints the inner fields for the custom post/page section */
function blogtal_tb_meta_box() {
	global $post;
	
	$blogtal_tb = get_option( BLOGTAL_TRACKBACK_DEFAULT_OPTION ) == 'true';
	
	?>
	<label><input type="checkbox" name="blogtal_tb" 
	<?php if ( $blogtal_tb ) { echo 'checked="checked"'; } ?>
	<?php if ( $post->post_status == 'publish' ) { echo 'disabled'; } ?>
	/> Send trackback to www.blogtal.com?</label>
	<?php
}

/* Prints the edit form for pre-WordPress 2.5 post/page */
function blogtal_tb_meta_box_old() {
	global $post;
	
	$blogtal_tb = get_option( BLOGTAL_TRACKBACK_DEFAULT_OPTION );
		
	if ( current_user_can( 'edit_posts' ) ) { ?>
	<fieldset id="sociableoption" class="dbx-box">
	<h3 class="dbx-handle">Blogtal Trackback</h3>
	<div class="dbx-content">
		<label><input type="checkbox" name="blogtal_tb" 
		<?php if ( $blogtal_tb ) { echo 'checked="checked"'; } ?>
		<?php if ( $post->post_status == 'publish' ) { echo 'disabled'; } ?>
		/> Send trackback to www.blogtal.com?</label>
	</div>
	</fieldset>
	<?php 
	}
}

function blogtal_tb_publish_post_action( $post ) {

	// Get secret ID
	$secret_ID = get_option( BLOGTAL_TRACKBACK_SECRET_ID_OPTION );
	
	// If secreted ID is set
	if ( isset( $_POST['blogtal_tb'] ) && !is_null( $secret_ID ) && strlen( $secret_ID = trim( $secret_ID ) ) > 0 && $post->post_type == 'post' ) {
	
		// Get title and excerpt;
		$has_excerpt = !is_null( $post->post_excerpt ) && trim( $post->post_excerpt ) > 0;
		$title = $post->post_title;
		$excerpt = $has_excerpt ? $post->post_excerpt : $post->post_content;
		
		// Prepare the trackback URL
		$trackback_url = BLOGTAL_TRACKBACK_URL.$secret_ID;
		
		// Send the trackback to blogtal.com
		trackback( $trackback_url, $title, $excerpt, $post->ID );
	}
	return $post->ID;
}

function blogtal_tb_admin_menu_option() {
	
	// variables for the field and option names 
	$secret_ID_field_name = "blogtal_tb_secret_ID";
	$default_field_name = "blogtal_tb_default";
	
	// Success or error message;
	$message = '';
	
	// Check submitted data
	if( isset( $_POST[ $secret_ID_field_name ] ) ) {
		
		// Trim it
		$secret_ID_field_value = trim( $_POST[ $secret_ID_field_name ] );
		$default_field_value = isset( $_POST[ $default_field_name ] ) ? 'true' : ' false';
		
		// Validate the value length
		if( strlen( $secret_ID_field_value) != 40 ) {
			$message = '<div style="color:red;" class="updated fade"><p><strong>'._( 'Invalid secret ID' ).'</strong></p></div>';
		} else {
			update_option( BLOGTAL_TRACKBACK_SECRET_ID_OPTION, $secret_ID_field_value  );
			update_option( BLOGTAL_TRACKBACK_DEFAULT_OPTION, $default_field_value  );
			$message = '<div class="updated fade"><p><strong>'._( 'Options saved' ).'</strong></p></div>';
		}
	}
	
	// Read in existing secret ID from database
    $secret_ID = get_option( BLOGTAL_TRACKBACK_SECRET_ID_OPTION );
	$default = get_option( BLOGTAL_TRACKBACK_DEFAULT_OPTION );
	echo $message;
	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br /></div>';
	echo '<h2>Blogtal Trackback</h2>';
	echo '<form name="blogtal_tb" method="post" action="'.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'">';
	echo '<h3>Instruction</h3>';
	echo '<p>';
	echo 'Blogtal Trackback plugin is a plugin that sends trackback to Blog Portal (www.blogtal.com) ONLY when you publish a post or set the post status to publish. ';
	echo '</p>';
	echo '<p>';
	echo 'In order to send trackback to Blog Portal, you will need to be a member of Blog Portal and register your blog(s) in Blog Portal. ';
	echo '<a target="_blank" href="http://www.blogtal.com/sign-up/">Registration is free.</a>';
	echo '</p>';
	echo '<p>';
	echo 'To get your secret URL from Blog Portal, login into www.blogtal.com. Click on the "Trackback Link" link under the Member Area menu. You will find the ';
	echo 'secret URL for the registered blog.';
	echo '</p>';
	echo '<h3>Secret URL</h3>';
	echo '<p>'.BLOGTAL_TRACKBACK_URL.'<input type="text" maxlength="40" size="50" name="'.$secret_ID_field_name.'" value="'.$secret_ID.'" />';
	echo '</p>';
	echo '<h3>Default Action</h3>';
	echo '<p><label><input type="checkbox" name="'.$default_field_name.'" value="yes" '.( $default == 'true' ? 'checked="checked"' : '' ).' /> Send trackback to www.blogtal.com whenever I publish a post.</label>';
	echo '</p>';
	echo '<p class="submit">';
	echo '<input type="submit" name="Submit" class="button-primary" value="'._( 'Save secret ID' ).'" />';
	echo '</p>';
	echo '</form>';
	echo '</div>';
}

add_action( 'activate_blogtal-trackaback/blogtal-trackaback.php', 'blogtal_tb_activate' );
add_action( 'admin_menu', 'blogtal_tb_admin_menu_action' );
add_action( 'private_to_publish', 'blogtal_tb_publish_post_action' );
add_action( 'draft_to_publish', 'blogtal_tb_publish_post_action' );
add_action( 'new_to_publish', 'blogtal_tb_publish_post_action' );
add_action( 'future_to_publish', 'blogtal_tb_publish_post_action' );
add_action( 'pending_to_publish', 'blogtal_tb_publish_post_action' );
?>