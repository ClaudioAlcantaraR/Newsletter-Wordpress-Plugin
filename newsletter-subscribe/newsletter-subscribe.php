<?php
/**
 * @package newsletter suscribe
 */
/**
 * Plugin Name:  Newsletter Suscribe
 * Description:  Una simple newsletter que te permite captar correos electronicos de tus visitantes.
 * Version:      1.0.0
 * Author:       Claudio Alcántara
 * Author URI:   https://claudioalcantara.com/
 * 
 * @category Newsletter
 * @package  CA
 * @author   Claudio Alcántara <claudio.dev29@gmail.com>
 * @license  GPLv2 http://www.gnu.org/licenses/gpl-2.0.txt
 * @link     https://claudioalcantara.com
 */

if (!defined('ABSPATH')) {die;}
 
if ( ! function_exists( 'car_form' ) ) {
	function car_form(){
		$dir_file = plugin_dir_url( __FILE__ );
		//Getting de Remote IP
		if (isset($_SERVER["HTTP_CLIENT_IP"])) {
        	$IP = $_SERVER["HTTP_CLIENT_IP"];

    	} elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$IP = $_SERVER["HTTP_X_FORWARDED_FOR"];

		} elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
        	$IP = $_SERVER["HTTP_X_FORWARDED"];

		} elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
        	$IP = $_SERVER["HTTP_FORWARDED_FOR"];
		}
		elseif (isset($_SERVER["HTTP_FORWARDED"])) {
        	$IP = $_SERVER["HTTP_FORWARDED"]; 
		}
		else {
        	$IP = $_SERVER["REMOTE_ADDR"];
		}
		?>
		
    	<div class="newsletter-email-main">
            <form class="newsletter" method="post" name="newsletter" id="newsletter" action="">
                <input type="hidden" name="base" id="base" value="<?php echo $dir_file; ?>">
                <input type="email" id="newsletter_email" name="newsletter_email" class="newsletter_email_field" placeholder="Correo electrónico" required />
				<p><input type="checkbox" name="privacy" class="input-privacy" required><span class="privacy-url">He leido la <a href="#">Política de Privacidad</a></span></p>
                <input type="submit" name="submit" class="newsletter-submit-form"  value="<?php echo esc_html__('Suscribirme','car');?>">
				<input type="hidden" name="ip" value="<?=$IP;?>">
            </form>
            <span id="email_error"></span>
        </div>        
        <div id="ajaxloader"></div>
        <?php if(isset($_POST['submit'])) {

				if($_POST["newsletter_email"] != ""){
					global $wpdb;

					$table_name = $wpdb->prefix . 'newsletter_email';

					$newsletter_email = sanitize_email($_POST["newsletter_email"]);

					if(!preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^",$newsletter_email)){ 
						echo '<script>alert("Invalid email");</script>';

					} else {
						$user_id = $wpdb->get_row("SELECT * FROM " . $table_name . " WHERE email = '".$newsletter_email."' ");
						if(empty($user_id)){
							$success = $wpdb->insert(
								$table_name, 
								array(
									'email' => $newsletter_email,
									'privacy' => 1,
									'created_date' => date('Y-m-d h:i:s'),
									'ip' => $IP
								)
							);
							if($success){
								echo '<p>Correo electrónico registrado con éxito.</p>';
								send_confirm_email();
							
							} else {
								echo '<p>Lo sentimos, algo ha salido mal. Vuelve a intentarlo.</p>';
							}
						} else {
							echo '<p>Lo sentimos, este correo electrónico ya esta registrado.</p>';
						}
					}
				} else {				
					echo '<p>Por favor, escribe tu correo electrónico.</p>';
				}
			}
	}
} 
// Newsletter Form shortcode: [newsletter_email_subscribe]
add_shortcode( 'newsletter_email_subscribe', 'shortcode_car_form' ); 
// The callback function
function shortcode_car_form() {
    ob_start();
    car_form();
    return ob_get_clean();
} 
if ( ! function_exists( 'car_script_method' ) ) {
	function car_script_method() {
		wp_enqueue_style( 'custom-style-css', plugin_dir_url( __FILE__ ) . 'css/style.css',false , '1.0', 'all' );
	}
}
add_action( 'wp_footer', 'car_script_method' );

// Create Newsletter Menu Dashboard Sidepanel Start
function car_create_menu(){
	// Parent page
    add_menu_page('Email Newsletter', 'Newsletter', 'manage_options', 'newsletter', 'car_listing_page', 'dashicons-email-alt' );
}
add_action( 'admin_menu', 'car_create_menu' );
// Create Newsletter Menu Dashboard Sidepanel End

//Download Csv File Function Start
add_action("admin_init", "download_csv");
function download_csv(){
	
	if(isset($_POST['download_csv'])) {
		global $wpdb;
		$delimiter = ",";
		$filename = "newsletter_email_" . date('Y-m-d') . ".csv";
		$f = fopen('php://output', 'w');
		$fields = array('ID', 'Correo', 'Fecha alta', 'IP remota');
		fputcsv($f, $fields, $delimiter);
		$query = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."newsletter_email");
		$rowNumber=0;
		foreach($query as $val){
			$rowNumber++;
			$Email = $val->email;
			$Date = $val->created_date;
			$IP_Remote = $val->ip;
			$lineData = array($rowNumber, $Email, $Date, $IP_Remote);	
			fputcsv($f, $lineData, $delimiter);
		}
		//fseek($f, 0);
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $filename . '";');
		fpassthru($f);
		exit;
	}
}
//Download Csv File Function End


// Create Newsletter Email List Function Start
function car_listing_page(){?>
<div class="newsletter-intro wrap">
   <h1 class="my-newsletter">CAR Newsletter</h1>
   <h2>Cómo utilzarla</h2>
   <p class="intro-text">¡Es muy sencillo! Solo copia y pega el siguiente <b>shortcode</b> en tu post, widget o página.</p>
   <div class="copy-container">
       <div><span class="shortcode" id="copy-btn">[newsletter_email_subscribe]</span></div>
       <div>
        	<button class="copy button" onclick="copyToClipboard('#copy-btn')">Copiar</button>
       </div>
   </div>
   <script>
	//Copiar elemento
	function copyToClipboard(e) {
        var $temp = jQuery("<input>")
        jQuery("body").append($temp);
        $temp.val(jQuery(e).text()).select();
        document.execCommand("copy");
        $temp.remove();
    } 
   </script>

    <div class="email-listing">
		<h1>Listado de suscripciones</h1>
    	<form class="exportcsv" method="post" name="exportcsv" id="exportcsv">
            <input type="submit" name="download_csv" class="button button-primary"  value="<?php echo esc_html__('export to csv','car');?>">
        </form>       
		<?php 
		global $wpdb;	
    	$result = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."newsletter_email");?>
    	<table id="myTable" class="table" width="100%" rules="all">    
            <thead>
              <tr>
                <td><strong><?php echo esc_html__('ID','car');?></strong></td>
                <td><strong><?php echo esc_html__('Correo','car');?></strong></td>
                <td><strong><?php echo esc_html__('Privacidad','car');?></strong></td>
                <td><strong><?php echo esc_html__('Fecha alta','car');?></strong></td>
				<td><strong><?php echo esc_html__('IP Remota','car');?></strong></td>
              </tr>
            </thead>
            <tbody>
                <?php foreach($result as $row) { 
                    echo "<tr>
                        <td>$row->id</td>
                        <td>$row->email</td>
                        <td>$row->privacy</td>
                        <td>$row->created_date</td>
						<td>$row->ip</td>
                    </tr>";
                 } ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}

// Admin Panel Css Add Function Start Here
add_action('admin_head', 'car_listing_admin_scripts');
function car_listing_admin_scripts() {
	wp_enqueue_style( 'newsletter-admin-css', plugin_dir_url( __FILE__ ) . 'css/email-list.css',false , '1.0', 'all' );
  	wp_enqueue_style( 'newsletter-datatable-css', plugin_dir_url( __FILE__ ) . 'vendor/datatable/jquery.dataTables.min.css',false , '1.0', 'all' );
  	wp_enqueue_script( 'newsletter-datatable-min-js', plugin_dir_url( __FILE__ ) . 'vendor/datatable/jquery.dataTables.min.js',false, '1.0', 'all' );
	wp_enqueue_script( 'newsletter-admin', plugin_dir_url( __FILE__ ) . 'js/admin-newsletter.js',false, '1.0', 'all' );
}
// Admin Panel Css Add Function End Here

// Send email confirmation
function send_confirm_email(){
	$email = 'claudio.dev29@gmail.com';
    add_filter( 'wp_mail_content_type', function ( $content_type ) {return 'text/html';});
    $headers= [];
    $headers[] = 'From: [site-name] Newsletter <wordpress@localhost.com>'."\r\n";
    $message = "Gracias por registrarte a nuestra newsletter.";
    $subject = "¡Bienvenido!";
    wp_mail( $email, $subject, $message, $headers /* $attachments */ );
    remove_filter( 'wp_mail_content_type', 'set_html_content_type');
}

// Create Database Table Function Start Here
function car_create_table() 
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'newsletter_email';
	$sql = "CREATE TABLE $table_name (
		id int(10) NOT NULL AUTO_INCREMENT,
		email varchar(100) NOT NULL,
		privacy BOOLEAN DEFAULT 1,
		created_date datetime NOT NULL,
		ip varchar(20) NOT NULL,
		PRIMARY KEY  (id)
	);";
 	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
 	dbDelta( $sql );
} 
register_activation_hook( __FILE__, 'car_create_table' );