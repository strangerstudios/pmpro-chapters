<?php
/**
 * Membership Chapter Class, a wrapper for WP_POST for the Chapter CPT
 */
class Membership_Chapter {
	/**
	 * Constructor
     * Initializes the plugin by setting localization, filters, and administration functions.
     */
    function __construct($post) {		
		if(!empty($post) && is_object($post) && !empty($post->ID)) {
			//assume this is a WP post
			$this->post = $post;
		} elseif(!empty($post) && is_numeric($post)) {
			//assume a post id
			$this->post = get_post($post);
		}
		
		return $this->setVars();
    }

    /**
     * Setup properties of the chapter
     */
    private function setVars() {
    	if(!empty($this->post)) {
    		$this->id = $this->post->ID;
    		$this->ID = $this->post->ID;
    		$this->name = $this->post->post_title;
    		$this->description = $this->post->post_content;
    	} else {
    		$this->id = NULL;
    		$this->ID = NULL;
    		$this->name = '';
    		$this->description = '';
    	}

    	return $this;
    }

    /**
     * Creates or returns an instance of this class.
     *
     * @return  Membership_Chapter A single instance of this class.
     */
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }		
		
        return self::$instance;
    }

    /**
     * Run code on init
     */
    public static function init() {
    	//create the CPT
		Membership_Chapter::create_chapter_CPT();

		//make sure permalinks to CPT pages work
		flush_rewrite_rules();
    }

    /**
	 * Register the chapter Custom Post Type
	 */
	public static function create_chapter_CPT()
	{
		register_post_type('membership_chapter',
			array(
					'labels' => array(
							'name' => __( 'Chapters' ),								
							'singular_name' => __( 'Chapter' ),
							'slug' => 'chapter',
							'add_new' => __( 'New Chapter' ),
							'add_new_item' => __( 'New Chapter' ),
							'edit' => __( 'Edit Chapter' ),
							'edit_item' => __( 'Edit Chapter' ),
							'new_item' => __( 'Add New' ),
							'view' => __( 'View This Chapter' ),
							'view_item' => __( 'View This Chapter' ),
							'search_items' => __( 'Search Chapters' ),
							'not_found' => __( 'No Chapters Found' ),
							'not_found_in_trash' => __( 'No Chapters Found In Trash' )
					),
			'public' => true,							
			'show_ui' => true,
			'show_in_menu' => true,				
			'publicly_queryable' => true,
			'hierarchical' => true,
			'supports' => array('title','editor','thumbnail','custom-fields'),
			'can_export' => true,
			'show_in_nav_menus' => true,
			'rewrite' => array(
					'slug' => 'chapter',
					'with_front' => false
					),
			'has_archive' => 'chapter',
			'capability_type' => 'membership_chapter',
			'capabilities' => array(
					'publish_posts' => 'publish_membership_chapters',
					'edit_posts' => 'edit_membership_chapters',
					'edit_others_posts' => 'edit_others_membership_chapters',
					'delete_posts' => 'delete_membership_chapters',
					'delete_others_posts' => 'delete_others_membership_chapters',
					'read_private_posts' => 'read_private_hmembership_chapters',
					'edit_post' => 'edit_membership_chapter',
					'delete_post' => 'delete_membership_chapter',
					'read_post' => 'read_membership_chapter',
				),
			)
		);
	}

	/**
	 * Get chapter value from request, session, or user meta
	 */
	public static function getUserChapterSomewhere($user = NULL) {
		if(isset($_REQUEST['membership_chapter'])) {
			return intval($_REQUEST['membership_chapter']);
		} elseif(isset($_SESSION['membership_chapter'])) {
			return intval($_SESSION['membership_chapter']);
		} elseif(!empty($user)) {
			if(is_numeric($user))
				$user = get_userdata($user);

			return get_user_meta($user->ID, 'membership_chapter', true);
		} else {
			global $current_user;

			if(!empty($current_user->ID))
				return get_user_meta($current_user->ID, 'membership_chapter', true);
			else
				return false;
		}
	}

	/**
	 * Get all chapters
	 */
	public static function getAllChapters() {
		$posts = get_posts(array('post_type'=>'membership_chapter'));

		$chapters = array();
	
		foreach($posts as $post) {
			$chapters[] = new Membership_Chapter($post);
		}

		return $chapters;
	}
	
	/**
	 * Get a dropdown showing all chapters and one selected
	 */
	public static function drawChaptersDropdown($user = NULL) {
		$chapters = Membership_Chapter::getAllChapters();
		$selected = Membership_Chapter::getUserChapterSomewhere($user);
		?>
		<select id="membership_chapter" name="membership_chapter">
			<option value=""><?php _e('- Choose One -', 'pmproch');?></option>
			<?php
				foreach($chapters as $chapter) {
				?>
				<option value="<?php echo esc_attr($chapter->ID);?>" <?php selected($chapter->ID, $selected);?>><?php echo esc_textarea($chapter->name);?></option>
				<?php
				}
			?>
		</select>
		<?php
	}

	/**
	 * Add Chapter to the PMPro checkout
	 */
	public static function pmpro_checkout_boxes() {
		$chapters = Membership_Chapter::getAllChapters();

		//if no chapters yet, bail
		if(empty($chapters))
			return false;

		?>
		<label for="membership_chapter"><?php _e('Chapter', 'pmproch');?></label>
		<?php
		Membership_Chapter::drawChaptersDropdown();
	}

	/**
	 * Store values in session vars for offsite gateways/etc
	 */
	function pmpro_paypalexpress_session_vars() {
		if(!empty($_REQUEST['membership_chapter']))
			$_SESSION['membership_chapter'] = $_REQUEST['membership_chapter'];
		else
			$_SESSION['membership_chapter'] = '';
	}

	/**
	 * Save chapter to user meta after checkout
	 */
	public static function pmpro_after_checkout($user_id) {
		$chapter_id = Membership_Chapter::getUserChapterSomewhere();

		update_user_meta($user_id, 'membership_chapter', $chapter_id);

		if(isset($_SESSION['membership_chapter']))
			unset($_SESSION['membership_chapter']);
	}

	/**
	 * Add Chapter to the edit user/profile page
	 */
	public static function show_extra_profile_fields( $user ) {
		$chapters = Membership_Chapter::getAllChapters();
		?>
		<h2><?php _e('Chapter', 'pmpro');?></h2>
		<?php
			//if no chapters yet, bail
			if(empty($chapters)) {
				echo '<p>' . __('Normally, you would be able to set the user\'s chapter here, but you haven\'t added any chapters yet.', 'pmpro-chapters') . '<p>';
				return false;
			}
		?>
		<table class="form-table">
			<tr>
				<th><?php _e('Chapter', 'pmpro-chapters');?></th>
				<td>
					<?php Membership_Chapter::drawChaptersDropdown($user); ?>
				</td>
			</tr>
		</table>
		<?php
	}
	
	/**
	 * Update chapter after editing a user
	 */
	public static function save_extra_profile_fields( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) )
			return false;

		if(isset($_POST['membership_chapter'])) {
			update_user_meta($user_id, 'membership_chapter', intval($_POST['membership_chapter']));
		}
	}
}