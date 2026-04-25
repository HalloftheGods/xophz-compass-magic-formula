<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Xophz_Compass_Magic_Formula
 * @subpackage Xophz_Compass_Magic_Formula/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Xophz_Compass_Magic_Formula
 * @subpackage Xophz_Compass_Magic_Formula/admin
 * @author     Your Name <email@example.com>
 */
class Xophz_Compass_Magic_Formula_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Xophz_Compass_Magic_Formula_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Xophz_Compass_Magic_Formula_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xophz-compass-magic-formula-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Xophz_Compass_Magic_Formula_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Xophz_Compass_Magic_Formula_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xophz-compass-magic-formula-admin.js', array( 'jquery' ), $this->version, false );

	}


	/**
	 * Add menu item 
	 *
	 * @since    1.0.0
	 */
	public function addToMenu(){
        Xophz_Compass::add_submenu($this->plugin_name);
	}

	/**
	 * Render the Questbook Contact Assignment dropdown in a native Meta Box.
	 * 
	 * @since 1.2.0
	 */
	public function add_questbook_assignment_meta_box( $post_type, $post ) {
		$registered_cpts = get_option( 'xophz_compass_registered_cpts', array() );
		$cpt_slugs = wp_list_pluck( $registered_cpts, 'slug' );

		if ( in_array( $post_type, $cpt_slugs, true ) ) {
			add_meta_box(
				'questbook_assignment_box',
				__( 'Questbook Assignment', 'xophz-compass-magic-formula' ),
				array( $this, 'render_questbook_assignment_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	public function render_questbook_assignment_meta_box( $post ) {
		wp_nonce_field( 'questbook_assignment_save', 'questbook_assignment_nonce' );

		// Determine current assignment
		$current_assignment = get_post_meta( $post->ID, '_qb_contact_id', true );
		if ( ! $current_assignment && $post->post_author ) {
			// Find contact matching this author
			$contact = get_posts( array(
				'post_type'   => 'questbook_contact',
				'meta_key'    => '_qb_user_id',
				'meta_value'  => $post->post_author,
				'numberposts' => 1
			) );
			if ( $contact ) {
				$current_assignment = $contact[0]->ID;
			}
		}

		// Pre-populate if passed via GET parameter from Questbook CRM
		if ( ! $current_assignment && isset( $_GET['assign_contact'] ) ) {
			$current_assignment = absint( $_GET['assign_contact'] );
		}

		// Get all contacts
		$contacts = get_posts( array(
			'post_type'      => 'questbook_contact',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC'
		) );

		echo '<p class="description">' . __( 'Assign this asset to a customer profile.', 'xophz-compass-magic-formula' ) . '</p>';
		echo '<select name="questbook_contact_assignment" id="questbook_contact_assignment" style="width: 100%;">';
		echo '<option value="">' . __( '&mdash; Select Contact &mdash;', 'xophz-compass-magic-formula' ) . '</option>';
		foreach ( $contacts as $contact ) {
			echo '<option value="' . esc_attr( $contact->ID ) . '" ' . selected( $current_assignment, $contact->ID, false ) . '>' . esc_html( $contact->post_title ) . '</option>';
		}
		echo '</select>';
	}

	public function save_questbook_assignment_meta( $post_id, $post ) {
		if ( ! isset( $_POST['questbook_assignment_nonce'] ) || ! wp_verify_nonce( $_POST['questbook_assignment_nonce'], 'questbook_assignment_save' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$registered_cpts = get_option( 'xophz_compass_registered_cpts', array() );
		$cpt_slugs = wp_list_pluck( $registered_cpts, 'slug' );
		if ( ! in_array( $post->post_type, $cpt_slugs, true ) ) {
			return;
		}

		if ( isset( $_POST['questbook_contact_assignment'] ) ) {
			$contact_id = absint( $_POST['questbook_contact_assignment'] );
			
			if ( $contact_id ) {
				$user_id = get_post_meta( $contact_id, '_qb_user_id', true );
				if ( $user_id ) {
					// Update post_author directly in database to avoid infinite save loops
					global $wpdb;
					$wpdb->update(
						$wpdb->posts,
						array( 'post_author' => $user_id ),
						array( 'ID' => $post_id ),
						array( '%d' ),
						array( '%d' )
					);
					delete_post_meta( $post_id, '_qb_contact_id' ); // Remove fallback meta if present
				} else {
					// Contact is a raw lead without a wp_user map
					update_post_meta( $post_id, '_qb_contact_id', $contact_id );
				}
			} else {
				// Assignment removed
				delete_post_meta( $post_id, '_qb_contact_id' );
			}
		}
	}
}
