<?php
/*
Plugin Name: GET Custom Content
Plugin URI: http://bryangentry.us/get-custom-content-wordpress-plugin/
Description: Add customized content to your WordPress website using GET variables in the URL
Version: 1.0
Author: bgentry
Author URI: http://bryangentry.us
License: GPL2
*/

add_shortcode('bg_gcc', 'bg_get_cc');

function bg_get_cc( $atts, $content = null ) {
	$queryvar = $atts['variable'];
	$return = '';
	if ( isset($_GET[$queryvar]) ) {
			if ( strpos( $content, '-value-'.$_GET[$queryvar] ) ) {
				$contentSplit = explode('-value-'.$_GET[$queryvar], $content);
				$return = wpautop($contentSplit[1]);
			} elseif ( strpos ($content, '_value_') ) {
				$value = strip_tags($_GET[$queryvar]);
				$return = wpautop(str_replace('_value_', $value, $content));
			}	else {
				//var_dump( strpos ($content, '-value-') );
				$term = get_term_by( 'name', $queryvar, 'bg_gcc_vars');
				if ( $term ) {
					$value = get_page_by_title( $_GET[$queryvar], 'OBJECT', 'bg_gcc_values' );
					if ( $value and has_term( $term->name, 'bg_gcc_vars', $value ) ) {
						$return = wpautop($value->post_content);				
					} elseif ( strpos( $term->description, '_value_' ) ) {
						$value = strip_tags($_GET[$queryvar]);
						$return = wpautop(str_replace('_value_', $value, $term->description));
					}
							
				}
				
			}
	return do_shortcode($return);	
	}
	
	}
	
	// register Foo_Widget widget
function register_gcc_widget() {
    register_widget( 'GCCWidget' );
}
add_action( 'widgets_init', 'register_gcc_widget' );

class GCCWidget extends WP_Widget {
	function __construct() {
		parent::__construct(
			'gcc_widget',
			'GET Custom Content',
			array('description'=>'Checks for custom content determined by query variables')
			);
	}

	public function widget( $args, $instance) {
		if ( isset ( $instance['gcc_var'] ) ) {
			$term = get_term ( $instance['gcc_var'], 'bg_gcc_vars' );
			if ( ! is_wp_error( $term ) ) {
				if ( isset ($_GET[$term->name] ) ) {
					$value = get_page_by_title( $_GET[$term->name], 'OBJECT', 'bg_gcc_values' );
					if ( $value and has_term( $term->name, 'bg_gcc_vars', $value ) ) {
						$title= apply_filters( 'widget_title', $instance['title'] );
						echo $args['before_widget'];
						if (!empty($title) ) {
						echo $args['before_title'] . $title . $args['after_title'];
						}
						echo wpautop(do_shortcode($value->post_content));
						echo $args['after_widget'];
					} elseif ( strpos( $term->description, '_value_' ) ) {
						$title= apply_filters( 'widget_title', $instance['title'] );
						echo $args['before_widget'];
						if (!empty($title) ) {
						echo $args['before_title'] . $title . $args['after_title'];
						}
						$value = strip_tags($_GET[$term->name]);
						echo wpautop(do_shortcode(str_replace('_value_', $value, $term->description)));
					}
				} 
			}
		} 
	}
	
	public function form($instance) {
		if ( isset ( $instance['title'] ) ) {
			$title= $instance['title'];
		} else {
			$title='New Title';
		} ?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id( 'title' );?>" name="<?php echo $this->get_field_name( 'title' );?>" type="text" value="<?php echo esc_attr($title); ?>"/>
		</p>
		<?php
		$vars = get_terms('bg_gcc_vars', array('hide_empty'=>0));
		if ( ! empty( $vars ) ) {
			?>
			<label><select name="<?php echo $this->get_field_name( 'gcc_var' ); ?>" class="widget_gcc_var">
			<?php
			foreach ( $vars as $var ) {
				$selected = ( $var->term_id == $instance['gcc_var'] ) ? ' selected="selected" ' : '';
				?>
				<option value="<?php echo $var->term_id; ?>" <?php echo $selected; ?>><?php echo $var->name; ?></option>
				<?php
			}
			?></select>
			<p><small><strong>Love this plugin?</strong> <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MRKES4XBYNDPU" title="Donate Now">Donate Now</a> to help support this and other plugins by <a href="http://bryangentry.us/" target="_blank">Bryan Gentry</a></small></p>
			<?php
		}
		
		
	}
	
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['gcc_var'] = ( ! empty( $new_instance['gcc_var'] ) ) ? $new_instance['gcc_var'] : '';
		return $instance;
	}
	
}
	
	
if ( ! function_exists('bg_gcc_custom_taxonomy') ) {

// Register Custom Taxonomy
function bg_gcc_custom_taxonomy()  {

	$labels = array(
		'name'                       => 'GET Custom Content Variables',
		'singular_name'              => 'GET Custom Content Variable',
		'menu_name'                  => 'Custom Content Variables',
		'all_items'                  => 'All Variables',
		'parent_item'                => 'Parent Variable',
		'parent_item_colon'          => 'Parent Variable:',
		'new_item_name'              => 'New Variable Name',
		'add_new_item'               => 'Add New Variable',
		'edit_item'                  => 'Edit Variable',
		'update_item'                => 'Update Variable',
		'separate_items_with_commas' => 'Separate Variables with commas',
		'search_items'               => 'Search Variables',
		'add_or_remove_items'        => 'Add or remove Variables',
		'choose_from_most_used'      => 'Choose from the most used Variables',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'rewrite'                    => false,
	);
	register_taxonomy( 'bg_gcc_vars', 'bg_gcc_values', $args );

}

// Hook into the 'init' action
add_action( 'init', 'bg_gcc_custom_taxonomy', 0 );

}

if ( ! function_exists('make_bg_gcc_post_type') ) {

// Register Custom Post Type
function make_bg_gcc_post_type() {

	$labels = array(
		'name'                => 'Values',
		'singular_name'       => 'Value',
		'menu_name'           => 'Get Custom Content',
		'parent_item_colon'   => 'Parent Value',
		'all_items'           => 'All Values',
		'view_item'           => 'View Value',
		'add_new_item'        => 'Add New Value',
		'add_new'             => 'New Value',
		'edit_item'           => 'Edit Value',
		'update_item'         => 'Update Value',
		'search_items'        => 'Search values',
		'not_found'           => 'No values found',
		'not_found_in_trash'  => 'No values found in Trash',
	);
	$args = array(
		'label'               => 'bg_gcc_values',
		'description'         => 'Values for Get Custom Post Types',
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', ),
		'taxonomies'          => array( 'bgg_gcc_vars' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => '',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'rewrite'             => false,
		'capability_type'     => 'page',
	);
	register_post_type( 'bg_gcc_values', $args );

}

// Hook into the 'init' action
add_action( 'init', 'make_bg_gcc_post_type', 0 );

}

function gcc_add_custom_box() {
    add_meta_box(
            'gcc_donate_box',
            'Support GET Custom Content',
            'gcc_inner_custom_box',
            'bg_gcc_values',
			'side',
			'high'
        );
    }
add_action( 'add_meta_boxes', 'gcc_add_custom_box' );

function gcc_inner_custom_box( $post ) {

  // Add an nonce field so we can check for it later.
  //wp_nonce_field( 'gcc_inner_custom_box', 'gcc_inner_custom_box_nonce' );
	?>
	<p>Thank you for using GET Custom Content!</p>
	<p>If you find this free plugin useful, please consider making a contribution to support this plugin and others by <a href="http://bryangentry.us/" target="_blank">Bryan Gentry</a>. <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MRKES4XBYNDPU" title="Donate Now">Donate Now</a></p>
	<p><strong>Need assistance</strong> with this plugin? Fill out my <a href="http://bryangentry.us/contact-me/" target="_blank">contact form</a> or post in the plugin forum on WordPress.org.</p>
<?php	
  }

  
  add_action( 'bg_gcc_vars_add_form_fields', 'edit_bg_gcc_vars_fields', 10, 2);

function edit_bg_gcc_vars_fields() {
	?>
	<script>
	jQuery('#tag-name').after('<p><span id="tagname-warning" style="color:#ee0000;"></span><strong>Note: You cannot use WordPress Reserved Names for your variable name.</strong></p>');
	jQuery('#tag-name').keyup( function() {
		enteredName = jQuery('#tag-name').val();
		if ( enteredName == "attachment" || enteredName == "attachment_id" || enteredName == "author" || enteredName == "author_name" || enteredName == "calendar" || enteredName == "cat" || enteredName == "category" || enteredName == "category__and" || enteredName == "category__in" || enteredName == "category__not_in" || enteredName == "category_name" || enteredName == "comments_per_page" || enteredName == "comments_popup" || enteredName == "customize_messenger_channel" || enteredName == "customized" || enteredName == "cpage" || enteredName == "day" || enteredName == "debug" || enteredName == "error" || enteredName == "exact" || enteredName == "feed" || enteredName == "hour" || enteredName == "link_category" || enteredName == "m" || enteredName == "minute" || enteredName == "monthnum" || enteredName == "more" || enteredName == "name" || enteredName == "nav_menu" || enteredName == "nonce" || enteredName == "nopaging" || enteredName == "offset" || enteredName == "order" || enteredName == "orderby" || enteredName == "p" || enteredName == "page" || enteredName == "page_id" || enteredName == "paged" || enteredName == "pagename" || enteredName == "pb" || enteredName == "perm" || enteredName == "post" || enteredName == "post__in" || enteredName == "post__not_in" || enteredName == "post_format" || enteredName == "post_mime_type" || enteredName == "post_status" || enteredName == "post_tag" || enteredName == "post_type" || enteredName == "posts" || enteredName == "posts_per_archive_page" || enteredName == "posts_per_page" || enteredName == "preview" || enteredName == "robots" || enteredName == "s" || enteredName == "search" || enteredName == "second" || enteredName == "sentence" || enteredName == "showposts" || enteredName == "static" || enteredName == "subpost" || enteredName == "subpost_id" || enteredName == "tag" || enteredName == "tag__and" || enteredName == "tag__in" || enteredName == "tag__not_in" || enteredName == "tag_id" || enteredName == "tag_slug__and" || enteredName == "tag_slug__in" || enteredName == "taxonomy" || enteredName == "tb" || enteredName == "term" || enteredName == "theme" || enteredName == "type" || enteredName == "w" || enteredName == "withcomments" || enteredName == "withoutcomments" || enteredName == "year" ) {
			jQuery('#tagname-warning').text('Warning: "'+enteredName+'" is a reserved term in WordPress. Using this in your URL will cause URLs to not work. Please use a different name for this variable. ');
		} else {
			jQuery('#tagname-warning').text('');
		}
	
	});
	</script>
	<?php
}
  
  
?>