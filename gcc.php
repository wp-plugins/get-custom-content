<?php
/*
Plugin Name: GET Custom Content
Plugin URI: http://bryangentry.us/get-custom-content-wordpress-plugin/
Description: Add customized content to your WordPress website using GET variables in the URL
Version: 1.1.1
Author: bgentry
Author URI: http://bryangentry.us
License: GPL2
*/

add_shortcode('bg_gcc', 'bg_get_cc');

function bg_get_cc_find_value_to_use ( $content, $queryvar ) {
    
        $term = get_term_by( 'name', $queryvar, 'bg_gcc_vars');
	if ( isset($_GET[$queryvar]) ) {
			if ( strpos( $content, '-value-'.$_GET[$queryvar] ) !==false ) {
				$contentSplit = explode('-value-'.$_GET[$queryvar], $content);
				$value = $contentSplit[1];
			}
                        elseif ( strpos ($content, '_value_') !==false ) {
				$value = strip_tags($_GET[$queryvar]);
			}
                        //the user isn't overriding or inserting the value, so let's look up the actual value
                        else {
                                //make sure the variable given actually exists
				if ( $term ) {
                                        //it exists, so let's find out whether we are inserting the query variable's value
                                    if ( strpos( $term->description, '_value_' ) ) {
                                            $value = strip_tags($_GET[$queryvar]);
                                        }
                                        else {
                                            //we're not just inserting the GET value, so let's look up the pre-defined value
                                            $valuePost = get_page_by_title( $_GET[$queryvar], 'OBJECT', 'bg_gcc_values' );
                                            if ( $valuePost!==NULL and has_term( $term->name, 'bg_gcc_vars', $valuePost ) ) {
                                                    //this value has been defined, so let's use its content
                                                    $value = $valuePost->post_content;
                                            }
                                            elseif ( $value == NULL ) {
                                                    //it doesn't exist, so we'll just use the default
                                                    $value = bgg_get_default_for_variable( $content, $queryvar );
                                            }
                                        }
                                } else {
                                    //um, this GCC variable doesn't even exist. These are not the droids you are looking for. Move along.
                                    return NULL;
                                }
				
			} 
                       
                  } else {
                            $value = bgg_get_default_for_variable( $content, $queryvar );
			}
    
              return $value;          
    
}


function choose_display_of_custom_content( $content, $queryvar, $value, $term ) {
    //now let's insert and / or format the value
    
                        if ( isset($value )) {
                            if ( strpos ($content, '_value_') !==false ) {
                                $value = strip_tags($_GET[$queryvar]);
                                $return = str_replace('_value_', $value, $content);
                            }
                            elseif ( strpos( $term->description, '_value_' ) !==false ) {
                                
                                $value = strip_tags($_GET[$queryvar]);
                                $return = str_replace('_value_', $value, $term->description);
                            } else {
                                $return = $value;
                            }
                        }
                  return $return;
}


function bg_get_cc( $atts, $content = null ) {
	$queryvar = $atts['variable'];
        $term = get_term_by( 'name', $queryvar, 'bg_gcc_vars');
        $value = bg_get_cc_find_value_to_use( $content, $queryvar );
                        
        $return = choose_display_of_custom_content( $content, $queryvar, $value, $term );
                
	return do_shortcode( wpautop( $return) );	
}

        
function bgg_get_default_for_variable( $content, $queryvar ) {
				//the value is not defined, let's see if we have the default
				if ( strpos( $content, '-value-default' ) ) {
					$contentSplit = explode('-value-default', $content);
					$return = wpautop( $contentSplit[1] );
                                        
				} else {
					$default = get_posts( 
										array(
											'posts_per_page'=> 1
											,'post_type' => 'bg_gcc_values'
											,'bg_gcc_vars' => $queryvar
											,'meta_key' => 'bg_gcc_value_default_status'
											,'meta_value' => true
											)
										);
					if ( is_array( $default ) ) {
						$return = wpautop( $default[0]->post_content );
					}
                                        
				}
    
return $return;    
}

//register the GCC widget
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
                    $queryvar = $term->name;
                    $value = bg_get_cc_find_value_to_use( $instance['override'], $queryvar );
                  
                    $content = choose_display_of_custom_content( $instance['override'], $queryvar, $value, $term );
                    
		} 
                if ( isset ( $content )) {
                    
                    $title= apply_filters( 'widget_title', $instance['title'] );
                    echo $args['before_widget'];
                    if (!empty($title) ) {
			echo $args['before_title'] . $title . $args['after_title'];
                    }
                    echo $content;
                    echo $args['after_widget'];
                    
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
                            
                            <p>
		<label for="<?php echo $this->get_field_id( 'override' ); ?>">Want to override the defined content for any variable/value combinations in this widget? Enter the override values here:<br/> <textarea class="widefat" id="<?php echo $this->get_field_id( 'override' );?>" name="<?php echo $this->get_field_name( 'override' );?>" >
<?php echo esc_attr( $instance['override'] ); ?>
                    </textarea>
		</p>
                            
                            
			<p><small><strong>Love this plugin?</strong> <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MRKES4XBYNDPU" title="Donate Now">Donate Now</a> to help support this and other plugins by <a href="http://bryangentry.us/" target="_blank">Bryan Gentry</a></small></p>
			<?php
		}
		
		
	}
	
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['gcc_var'] = ( ! empty( $new_instance['gcc_var'] ) ) ? $new_instance['gcc_var'] : '';
                $instance['override'] = ( ! empty( $new_instance['override'] ) ) ? $new_instance['override'] : '';
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


//register, make, and save the custom box
function gcc_add_custom_box() {
    add_meta_box(
            'gcc_donate_box',
            'Make this the Default?',
            'gcc_inner_custom_box',
            'bg_gcc_values',
			'side',
			'high'
        );
    }
add_action( 'add_meta_boxes', 'gcc_add_custom_box' );

function gcc_inner_custom_box( $post ) {

	wp_nonce_field( 'bggcc_meta_box', 'bggcc_meta_box_nonce' );
	$value = get_post_meta( $post->ID, 'bg_gcc_value_default_status', true );
	$checked = ( $value == true ) ? " checked" : "";
	echo '<label for="bggcc_default_field">';
	_e( 'Do you want this to be the DEFAULT content that will be displayed when its associated variable is not defined?', 'bggcc_textdomain' );
	echo '</label> ';
	echo '<input type="checkbox" id="bggcc_default_field" name="bggcc_default_field" value="true" ' . $checked . ' />';
        
	?>
                        <p><strong>Support GET Custom Content!</strong></p>
	<p>If you find this free plugin useful, please consider making a contribution to support this plugin and others by <a href="http://bryangentry.us/" target="_blank">Bryan Gentry</a>. <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MRKES4XBYNDPU" title="Donate Now">Donate Now</a></p>
	<p><strong>Need assistance</strong> with this plugin? Fill out my <a href="http://bryangentry.us/contact-me/" target="_blank">contact form</a> or post in the plugin forum on WordPress.org.</p>
<?php	
  }
  
  
  function bggcc_save_meta_box_data( $post_id ) {
	if ( ! isset( $_POST['bggcc_meta_box_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['bggcc_meta_box_nonce'], 'bggcc_meta_box' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}
	
	// Make sure that it is set.
	if ( ! isset( $_POST['bggcc_default_field'] ) ) {
		return;
	}	

	// Update the meta field in the database.
	update_post_meta( $post_id, 'bg_gcc_value_default_status', true );
}
add_action( 'save_post', 'bggcc_save_meta_box_data' );


  
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