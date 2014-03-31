<?php
/*
Plugin Name: Myjour Widget
Plugin URI: http://myjour.com/
Description: The Myjour widget shows related content to the article from Myjour.
Version: 1.0
Author: Myjour
Author URI: http://myjour.com/
*/

if(!class_exists('Myjour_Widget_Func'))
{
    register_activation_hook( __FILE__, array('Myjour_Widget_Func', 'install') );
    add_filter( 'the_content', array('Myjour_Widget_Func', 'myjour_the_content_filter'), 20 );
    add_action( 'plugin_action_links_' . plugin_basename(__FILE__), array('Myjour_Widget_Func', 'global_adminbar_filter_plugin_actions') );

    class Myjour_Widget_Func {
		
        public function __construct() {
            add_action( 'admin_menu', array(&$this, 'admin_menus') );
            add_action( 'admin_init', array(&$this, 'myjour_app_meta_box_init') );
            add_action( 'save_post', array(&$this, 'myjour_app_meta_box_save') );
        }
        
        public function install(){            
            $option_name = self::optname();
			$new_value = array(
				'data-like-ref' => '.itemFullText',
				'data-channel' => 'myjour',
				'data-size' => 3,
                'css' => '',
                'auto_show_in_post' => 1
			);
            $new_value = serialize($new_value);

			if ( get_option( $option_name ) !== false ) {
				// The option already exists, so we just update it.
				update_option( $option_name, $new_value );
			} else {
				// The option hasn't been added yet. We'll add it with $autoload set to 'no'.
				$deprecated = '';
				$autoload = 'no';
				add_option( $option_name, $new_value, $deprecated, $autoload );                
			}           
        }
        
        public function global_adminbar_filter_plugin_actions($links){
            $new_links = array();
            $adminlink = get_bloginfo('url').'/wp-admin/';
            $new_links[] = '<a href="'.$adminlink.'options-general.php?page=myjour_widget">Settings</a>';
            return array_merge($links,$new_links );
        }
                
        public function optname(){
            return 'myjour-widget-options';
        }
        
        public function admin_menus(){
            add_options_page('Myjour Widget', 'Myjour Widget', 'manage_options', 'myjour_widget', array(&$this, 'myjour_widget_settings'));
        }
        
        public function myjour_widget_settings(){
            $optval = self::optname();
            $thisPageURL = get_admin_url()."options-general.php?page=myjour_widget";
            
            if( isset($_POST['processval']) && $_POST['processval'] == 'updatesettings'){
                $data_link_ref = $_POST['sb_data_like_ref'];
                // if( empty($data_link_ref) ){ $data_link_ref = '.itemFullText'; }
                
                $data_channel = $_POST['sb_data_channel'];
                // if( empty($data_channel) ){ $data_channel = 'myjour'; }
                
                $data_size = intval($_POST['sb_data_size']);
                if( $data_size <= 0 ){ $data_size = 3; }
                
                $css = trim($_POST['sb_inline_css']);
                $auto_show_in_post = ( isset($_POST['sb_data_box_active']) ) ? intval( $_POST['sb_data_box_active'] ) : 0;
                
                $new_value = array(
                    'data-like-ref' => $data_link_ref,
                    'data-channel' => $data_channel,
                    'data-size' => $data_size,
                    'css' => $css,
                    'auto_show_in_post' => $auto_show_in_post
                );
                $new_value = serialize($new_value);

                if ( get_option( $optval ) !== false ) {
                    // The option already exists, so we just update it.
                    update_option( $optval, $new_value );
                } else {
                    // The option hasn't been added yet. We'll add it with $autoload set to 'no'.
                    $deprecated = '';
                    $autoload = 'no';
                    add_option( $optval, $new_value, $deprecated, $autoload );                
                }
                echo "<script>location.href = '".$thisPageURL."';</script>";
                die;
            }
            
            $optval_arr = unserialize( get_option( $optval ) );
            include_once('myjour-widget-settings.php');
        }
        
        public function myjour_widget_shortcode($atts){
            $optval_arr = unserialize( get_option( self::optname() ) );
            extract( shortcode_atts( array(
                'data_like_ref' => stripslashes($optval_arr['data-like-ref']),
                'data_channel' => stripslashes($optval_arr['data-channel']),
                'data_size' => stripslashes($optval_arr['data-size']),
                'css' => stripslashes($optval_arr['css'])
            ), $atts ) );
            
            $h = self::getJsCode($data_like_ref, $data_channel, $data_size, $css);
            
            return $h;
        }
        
        public function getJsCode($data_like_ref, $data_channel, $data_size, $css){
            $h = (empty($css)) ? '' : '<style>'.$css.'</style>';
            
            $h .= '<div style="clear: both;"><script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//myjour.com/static/widget/all.js"; fjs.parentNode.insertBefore(js, fjs); }(document, \'script\', \'myjour-jssdk\'));</script> <div data-like-ref="'.$data_like_ref.'" data-channel="'.$data_channel.'" data-size="'.$data_size.'" class="myjour-latest-articles"><ul></ul></div></div><br clear="all" />';
            
            return $h;
        }
        
        public function myjour_app_meta_box_init(){
            $default_val = unserialize( get_option( self::optname() ) );
            if( isset($default_val['auto_show_in_post']) && $default_val['auto_show_in_post'] == 1 ){
                $args = array(
                    'public' => true
                );
                $output = 'objects'; // names or objects
                $post_types = get_post_types( $args, $output );    
                $type_not_allow = array('attachment', 'revision', 'nav_menu_item', 'action', 'order', 'theme');       
                foreach ( $post_types  as $post_type ) {                            
                    // $post_type->name
                    if( in_array($post_type->name, $type_not_allow) === false ){
                        add_meta_box('myjour_app_meta_box', 'Myjour Widget settings', array(&$this, 'myjour_app_meta_box_html'), $post_type->name, 'side', 'default');
                    }
                }
            }
        }
        
        public function myjour_app_meta_box_html(){
            global $post, $wpdb;
            $meta = get_post_meta($post->ID, '_myjour-post-article-settings', TRUE);  
            
            if( !empty($meta) ){
                $optval_arr = unserialize($meta);
                $data_like_ref = stripslashes($optval_arr['data-like-ref']);
                $data_channel = stripslashes($optval_arr['data-channel']);
                $data_size = stripslashes($optval_arr['data-size']);
                $active = intval( stripslashes($optval_arr['active']) );
            }else{
                $optval_arr = unserialize( get_option( self::optname() ) );
                $data_like_ref = stripslashes($optval_arr['data-like-ref']);
                $data_channel = stripslashes($optval_arr['data-channel']);
                $data_size = stripslashes($optval_arr['data-size']);
                $active = 0;
            }      
            
            echo '<p><label for="sb_data_like_ref">';
                    _e( "Data like ref CSS" );
            echo '</label> ';
            echo '<input type="text" id="sb_data_like_ref" name="sb_data_like_ref" value="' . esc_attr( $data_like_ref ) . '" size="25" /></p>';
            
            echo '<p><label for="sb_data_channel">';
                    _e( "Data channel" );
            echo '</label><br />';
            echo '<input type="text" id="sb_data_channel" name="sb_data_channel" value="' . esc_attr( $data_channel ) . '" size="25" /></p>';
            
            echo '<p><label for="sb_data_size">';
                    _e( "Data size" );
            echo '</label><br /> ';
            echo '<input type="text" id="sb_data_size" name="sb_data_size" value="' . esc_attr( $data_size ) . '" size="5" maxlength="3" /></p>';
            
            echo '<p><label>';
                    _e( "Active" );
            echo '</label><br /> ';
            echo '<input type="checkbox" name="sb_data_active" value="1" '.( ($active == 1) ? 'checked="checked"' : '' ).' /></p>';
      
            echo '<input type="hidden" name="myjour_app_meta_box_is_secure" value="' . wp_create_nonce(__FILE__) . '" />';
        }
        
        public function myjour_app_meta_box_save($post_id){
            // make sure data came from our meta box
            if (!wp_verify_nonce($_POST['myjour_app_meta_box_is_secure'],__FILE__)) return $post_id;
            
            $default_val = unserialize( get_option( self::optname() ) );
            if( isset($default_val['auto_show_in_post']) && $default_val['auto_show_in_post'] == 1 ){
                if( isset($_POST['myjour_app_meta_box_is_secure']) ){
                    $new_data = serialize( array('data-like-ref' => $_POST['sb_data_like_ref'], 'data-channel' => $_POST['sb_data_channel'], 'data-size' => $_POST['sb_data_size'], 'active' => (($_POST['sb_data_active'] == 1) ? 1 : 0) ) );
                    update_post_meta($post_id, '_myjour-post-article-settings', $new_data);
                }
            }
            return $post_id;
        }
        
        public function myjour_the_content_filter($content){
            global $post;
            if ( is_single() OR is_page() ){
                $meta = get_post_meta($post->ID, '_myjour-post-article-settings', TRUE);  
                $default_val = unserialize( get_option( self::optname() ) );
                $active = 0;
                if( isset($default_val['auto_show_in_post']) && $default_val['auto_show_in_post'] == 1 ){
                                    
                    if( !empty($meta) ){
                        $optval_arr = unserialize($meta);
                        $data_like_ref = stripslashes($optval_arr['data-like-ref']);
                        $data_channel = stripslashes($optval_arr['data-channel']);
                        $data_size = stripslashes($optval_arr['data-size']);
                        $active = intval( stripslashes($optval_arr['active']) );
                    }else{
                        $optval_arr = $default_val;
                        $data_like_ref = stripslashes($optval_arr['data-like-ref']);
                        $data_channel = stripslashes($optval_arr['data-channel']);
                        $data_size = stripslashes($optval_arr['data-size']);
                    }
                    
                }
                
                $h = '';
                if( $active == 1 ){
                    $h = self::getJsCode($data_like_ref, $data_channel, $data_size, '');
                }
                
                return $content.$h;
            }else{
                return $content;
            }
        }
    }
    
    new Myjour_Widget_Func();
    
    add_shortcode( 'myjour-widget', array( 'Myjour_Widget_Func', 'myjour_widget_shortcode' ) );
}

/**
 * Adds Myjour_article_Widget widget.
 */
class Myjour_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'myjour_widget', // Base ID
            __('Myjour Widget', 'text_domain'), // Name
            array( 'description' => __( 'Show related articles from Myjour', 'text_domain' ), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $args['before_widget'];
        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];
        echo Myjour_Widget_Func::getJsCode($instance['data_like_ref'], $instance['data_channel'], $instance['data_size'], $instance['css']);
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $optval_arr = unserialize( get_option( Myjour_Widget_Func::optname() ) );
        
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];            
        }
        else {
            $title = __( 'New title', 'text_domain' );
        }      
        
        if ( isset( $instance[ 'data_like_ref' ] ) && !empty($instance[ 'data_like_ref' ]) ) { 
            $data_like_ref = $instance[ 'data_like_ref' ];  
        }else{
            $data_like_ref = stripslashes($optval_arr['data-like-ref']);
        }       
        
        if ( isset( $instance[ 'data_channel' ] ) && !empty($instance[ 'data_channel' ]) ) { 
            $data_channel = $instance[ 'data_channel' ];  
        }else{
            $data_channel = stripslashes($optval_arr['data-channel']);
        }
        
        if ( isset( $instance[ 'data_size' ] ) && intval($instance[ 'data_size' ]) > 0 ) { 
            $data_size = intval($instance[ 'data_size' ]);  
        }else{
            $data_size = stripslashes($optval_arr['data-size']);
        }
        
        if ( isset( $instance[ 'css' ] ) && !empty($instance[ 'css' ]) ) { 
            $css = $instance[ 'css' ];  
        }else{
            $css = stripslashes($optval_arr['css']);
        }
        
        ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
        <label for="<?php echo $this->get_field_id( 'data_like_ref' ); ?>"><?php _e( 'Data like ref CSS:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'data_like_ref' ); ?>" name="<?php echo $this->get_field_name( 'data_like_ref' ); ?>" type="text" value="<?php echo esc_attr( $data_like_ref ); ?>">
        </p>
        <p>
        <label for="<?php echo $this->get_field_id( 'data_channel' ); ?>"><?php _e( 'Data channel:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'data_channel' ); ?>" name="<?php echo $this->get_field_name( 'data_channel' ); ?>" type="text" value="<?php echo esc_attr( $data_channel ); ?>">
        </p>
        <p>
        <label for="<?php echo $this->get_field_id( 'data_size' ); ?>"><?php _e( 'Data size:' ); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id( 'data_size' ); ?>" name="<?php echo $this->get_field_name( 'data_size' ); ?>" type="text" value="<?php echo esc_attr( $data_size ); ?>">
        </p>
        <p>
        <label for="<?php echo $this->get_field_id( 'css' ); ?>"><?php _e( 'Optional CSS:' ); ?></label> 
        <textarea class="widefat" id="<?php echo $this->get_field_id( 'css' ); ?>" name="<?php echo $this->get_field_name( 'css' ); ?>"><?php echo esc_attr( $css ); ?></textarea>
        </p>        
        <?php 
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $optval_arr = unserialize( get_option( Myjour_Widget_Func::optname() ) );
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['data_like_ref'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['data_like_ref'] ) : stripslashes($optval_arr['data-like-ref']);
        $instance['data_channel'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['data_channel'] ) : stripslashes($optval_arr['data-channel']);
        $instance['data_size'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['data_size'] ) : stripslashes($optval_arr['data-size']);
        $instance['css'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['css'] ) : stripslashes($optval_arr['css']);

        return $instance;
    }

} // class Myjour_article_Widget

add_action('widgets_init',
     create_function('', 'return register_widget("Myjour_Widget");')
);
?>
