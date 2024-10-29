<?php
/*
Plugin Name: ADN Profile
Plugin URI: http://www.voiceoftech.com/swhitley/index.php/adn-status/
Description: Creates a widget to display your App.net status.  Also serves as a sample application for accessing and updating data in App.net.
Version: 1
Author: Shannon Whitley
Author URI: http://voiceoftech.com
*/


/**
 * Adds ADNStatus_Widget widget.
 */
class ADNStatus_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'ADNStatus_widget', // Base ID
			'ADN Status', // Name
			array( 'description' => __( 'Display your App.net status.', 'adnstatus' ), ) // Args
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
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$username = $instance['username'];
		$accesstoken = $instance['accesstoken'];
		$code='available';
		$status='';
		//Standard codes as defined for the user annotation - https://github.com/appdotnet/api-spec/issues/227#issuecomment-10209157
		$codetextArr = array('available'=>'Available', 'busy'=>'Busy', 'away'=>'Away', 'dnd'=>'Do Not Disturb','xa'=>'Extended Away');
		$image = "adnavailable.png";

		try
		{
			//Retrieve the user annotation for org.xmpp.presence
			//Set code, status, and image
			$url = 'https://alpha-api.app.net/stream/0/users/@'.$username.'?include_annotations=1';
			$user = json_decode(wp_remote_retrieve_body(wp_remote_get( $url, array( 'sslverify' => false ) ) ), true);
			foreach ($user['data']['annotations'] as $data) { 
					if($data['type'] == 'org.xmpp.presence')
					{
						$code =  $data['value']['code'];
						$status =  stripslashes($data['value']['status']);
					}
			}
			$codetext = $codetextArr[$code];			
			if($code != 'available')
			{
				$image = "adnbusy.png";
			}
		} catch (Exception $e) {
		}

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		?>

		<img src="<?php echo plugin_dir_url( __FILE__ ).$image ?>" title='App.net Status' /> <a href="http://alpha.app.net/<?php echo $username ?>" target="_blank"><?php echo $username ?></a>
		<br/><?php echo $codetext ?>
		<br/><?php echo $status ?>

		<?php
		//Display update controls to administrators.
		if(current_user_can('manage_options'))
		{
			echo "<hr/><p><input type='hidden' id='adn_accesstoken' value='".$accesstoken."'/>";
			echo "<label for='adn_status_code'>Set Status</label><br/>";
			echo "<select id='adn_status_code' name='adn_status_code'>";
			foreach ($codetextArr as $key => $value) {
				$selected = '';
				if($code == $key)
				{
					$selected='selected="selected"';
				}
				echo "<option value='".$key."' ".$selected.">".$value."</option>";
			}
			echo "</select>";
			echo "<br/><input id='adn_status' name='adn_status' class='widefat' type='text' value='".htmlspecialchars($status, ENT_QUOTES)."' />";
			echo "</p>";
			echo "<p><input id='adn_status_update' type='button' value='Update' onclick='' /></p>";
			echo "<p><a href='javascript:void(0)' onclick='location.reload(true);'>Reload Page</a></p>";
			echo "<p id='adn_result'></p>";
		}
		echo $after_widget;
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
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['username'] = strip_tags( $new_instance['username'] );
		$instance['accesstoken'] = strip_tags( $new_instance['accesstoken'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		//From the App.net application setup.
		$client_id = 'sDE9kUcx8LfRWUqXNmdrsHNqz2DvpNMM';
		//This page displays the App.net access token.
		$callback = 'http://whitleymedia.com/adn/adnstatus';

		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'adnstatus' );
		}
		if ( isset( $instance[ 'username' ] ) ) {
			$username = $instance[ 'username' ];
		}
		if ( isset( $instance[ 'accesstoken' ] ) ) {
			$accesstoken = $instance[ 'accesstoken' ];
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e( 'App.net Username:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo esc_attr( $username ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'accesstoken' ); ?>"><?php _e( 'Access Token:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'accesstoken' ); ?>" name="<?php echo $this->get_field_name( 'accesstoken' ); ?>" type="text" value="<?php echo esc_attr( $accesstoken ); ?>" />
		</p>
		<p>
			<a href="https://alpha.app.net/oauth/authenticate?client_id=<?php echo $client_id ?>&response_type=token&redirect_uri=<?php echo $callback ?>&scope=basic update_profile" target="_blank">Get Access Token</a>
		</p>

		<?php 
	}

} // class ADNStatus_Widget


// register ADNStatus_Widget widget
add_action( 'widgets_init', create_function( '', 'register_widget( "ADNStatus_widget" );' ) );

//Enable the ajax update from the home page.
add_action('init','enqueue_adnstatus_scripts');
add_action( 'wp_ajax_adnstatus-update', 'adnstatus_update_ajax' );


//Javascript Setup
function enqueue_adnstatus_scripts() {

	if(current_user_can('manage_options'))
	{
		wp_enqueue_script( 'json2' );
		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'adnajax', plugin_dir_url( __FILE__ ) . 'ajax.js', array( 'jquery', 'json2' ) );
		wp_localize_script('adnajax', 'ADNStatus', array('ajaxurl'=>admin_url('admin-ajax.php'),'adnStatusNonce'=>wp_create_nonce('adnstatus-nonce')));
	}
}


function adnstatus_update_ajax() {

	$nonce = $_POST['adnStatusNonce'];

	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
	if ( ! wp_verify_nonce( $nonce, 'adnstatus-nonce' ) )
		die ( 'Busted!');

	// ignore the request if the current user doesn't have
	// sufficient permissions
	if ( current_user_can('manage_options') ) {
		// get the submitted parameters
		$accesstoken = $_POST['accesstoken'];
		$status_code = $_POST['status_code'];
		$status = $_POST['status'];

		try
		{
			//Get the required properties (name, locale, timezone, description)
			$headers = array( 'Authorization' => 'Bearer '.$accesstoken, 'Content-Type' => 'application/json' );
			$url = 'https://alpha-api.app.net/stream/0/users/me';
			$user = json_decode(wp_remote_retrieve_body(wp_remote_get( $url, array( 'sslverify' => false, 'headers' => $headers ) ) ), true);

			$data = json_encode(
			array
			(
				'name' => $user['data']['name'],
				'locale' => $user['data']['locale'],
				'timezone' => $user['data']['timezone'],
				'description' => array
					(
						'text' => $user['data']['description']['text']
					),
				'annotations' => array(array
					(
						'type' => 'org.xmpp.presence',
						'value' => array
							(
								'code' => $status_code,
								'status' => $status
							)
					))
			));

			$user = json_decode(wp_remote_retrieve_body(wp_remote_request( $url.'?include_user_annotations=1', array('method' => 'PUT','sslverify' => false, 'headers' => $headers, 'body' => $data ) ) ), true);
			$response = $user['meta']['code'];
		}
		catch(Exception $e){}
		
		// response output
		echo $response;
	}

	// IMPORTANT: don't forget to "exit"
	exit;
}

