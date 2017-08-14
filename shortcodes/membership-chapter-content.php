<?php
/**
 * Shortcode to hide/show content based on a user's chapter
 */
function pmproch_membership_chapter_content_shortcode($atts, $content=null, $code="")
{
	//grab key and value attributes
	extract(shortcode_atts(array(
		'chapter' => NULL,
	), $atts));
	
	//default to hiding
	$show = false;
	
	if(is_user_logged_in()) {
		global $current_user;
		$meta_value = get_user_meta($current_user->ID, 'membership_chapter', true);
		if(!empty($meta_value)) {
			$user_chapter = new Membership_Chapter($meta_value);
			if($chapter == $user_chapter->ID ||
			   $chapter == $user_chapter->name || 
			   $chapter == $user_chapter->post->post_name) {
				$show = true;
			}
		}
	}

	if($show)
		return do_shortcode($content);	//show content
	else
		return "";	//just hide it
}
add_shortcode("membership-chapter-content", "pmproch_membership_chapter_content_shortcode");