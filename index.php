<?php

/**
 * APT Illegal Copy Checker
 * Before using this file, make sure that the file "ssl.encrypted" is in template root directory
 * ssl.encrypted is serialized and encrypted array. This array looks like this
	array(
		"theme_name" => "Theme name",
		"theme_slug" => "theme-slug",
		"version" => "1.0.0",
		"site" => false // or 'http://www.client-website.com'
	)
 */
class apt_illegal_copy_checker {

	##############################################################################
	##							Obfuscated String 								##
	##############################################################################

	private $obfuscated_strings = array(
		// 0 'get_template_directory'
		'23rCUB7tP054/LqDPhv+8V6Yu7bQIXcFhA0SIy4sHp8=',
		// 1 '/ssl.encrypted'
		'ZTaY5yfof7AY0dZW6Fl4wg==',
		// 2 'wp_die'
		'PWxf5BxCs95YkmDlcFRLGw==',
		// 3 'There is something wrong. Please contact theme developer<br>Error message : '
		'fOs1U6mh+cOCGqbYDJ1GprzA+/uFTGjMw+CgjqljJDRYv81+ZYCeYb3CsZijgzpDuMTxN74yYc5ftdepaTyXnfxbac80OLRWI3TRUZaKlkg='
	);

	##############################################################################
	##						Encrypte & Decrypt Variable							##
	##############################################################################

	// Encrypted ssl.encrypted file path
	private $ssl_encrypted_file_path;

	// SSL password
	private $ssl_password = '*B[1[taq*$k!9t(AhnRg|P h>*{B3(p@9Z@H1h#sb6?0cFT4FJjh>=h*UaE@T~/[';

	// SSL encrypt method
	private $ssl_method = 'aes128';

	/**
	 * Decrypted data
	 * the following is its structure
	 *
		array(
			"theme_name" => "Theme name",
			"theme_slug" => "theme-slug",
			"version" => "1.0.0",
			"site" => "http://www.client-website.com"
		)
	 * @var array
	 */
	private $data;

	/**
	 * handle WordPress action hooks
	 */
	function __construct() {
		// Setup wp_filesystem api
		require_once ABSPATH . 'wp-admin/includes/file.php';
		if (!WP_Filesystem()) {
			$this->show_error('WP Filesystem API couldn\'t connect the server');
		}

		// decrypte obfuscated strings
		$this->decrypte_strings($this->obfuscated_strings);

		// Setup variable
		$this->ssl_encrypted_file_path = $this->obfuscated_strings[0]() . $this->obfuscated_strings[1];
		
		/**
		 * DO SOME STUFFS HERE
		 */
		add_action( 'wp_head', array($this, 'check_illegal_copy') );
	}

	/**
	 * Read the file "ssl.encrypted", decrypt and unserialize data to $this->data
	 * @return void
	 */
	private function read_ssl_encrypted() {
		// Read raw data from file system
		$raw_data = $GLOBALS['wp_filesystem']->get_contents($this->ssl_encrypted_file_path);
		// Make sure that all string we get is single line
		$raw_data = preg_replace("/\r\n|\r|\n/",'', $raw_data);
		// Decrypte raw data
		$this->data = unserialize(openssl_decrypt($raw_data, $this->ssl_method, $this->ssl_password));
	}

	/**
	 * Save encrypted-serialized $this->data to the file "ssl.encrypted"
	 * @return void
	 */
	private function save_ssl_encrypted() {
		$GLOBALS['wp_filesystem']->put_contents($this->ssl_encrypted_file_path, openssl_encrypt(serialize($this->data), $this->ssl_method, $this->ssl_password));
	}

	/**
	 * Main function to check for illegal copy
	 * @return void
	 */
	public function check_illegal_copy() {

		// chaeck if the file ssl.encrypted exist
		if(!file_exists($this->ssl_encrypted_file_path)){
			$this->show_error('file not exist');
		}

		// Read ssl.encrypted
		$this->read_ssl_encrypted();

		// Check if valid SSL encrypted
		if(!$this->is_ssl_encrypted_valid()) {
			$wp_theme = wp_get_theme();
			$this->show_error('file is invalid');
		}

		// Check if it is a fresh copy
		if($this->data["site"] === false) {
			$this->data["site"] = get_site_url();
			$this->save_ssl_encrypted();
		} else {
			// Check if Illegal copy
			if($this->data['site'] !== get_site_url()) {
				$this->show_error('Illegal');
			}
		}
	}

	/**
	 * Check if SSL data is valid
	 * @return boolean
	 */
	private function is_ssl_encrypted_valid() {
		$wp_theme = wp_get_theme();
		if(
			isset($this->data['theme_name']) &&
			$this->data['theme_name'] === $wp_theme->get('Name') &&
			isset($this->data['version']) &&
			$this->data['version'] === $wp_theme->get('Version') &&
			isset($this->data['theme_slug']) &&
			$this->data['theme_slug'] === get_option('stylesheet')
		) {
			return true;
		}
		return false;
	}

	private function show_error($message) {
		$this->obfuscated_strings[2]($this->obfuscated_strings[3]. $message);
	}

	/**
	 * Descrypt any arbitrary encrypted string
	 * @param  string $string encrypted string
	 * @return string         decrypted string
	 */
	private function decrypte_string($string) {
		return openssl_decrypt($string, $this->ssl_method, $this->ssl_password);
	}

	private function decrypte_strings(&$array) {
		foreach ($array as $key => $value) {
			$array[$key] = $this->decrypte_string($value);
		}
	}

}

new apt_illegal_copy_checker();