<?php

#####################################################################################
#####################################################################################
#####																			#####
######																			#####
######				This encrypt & decrypt use SSL function						#####
######																			#####
######																			#####
#####################################################################################
#####################################################################################

/**
 * Start Timer
 */
$executionStartTime = microtime(true);


/**
 * This variable can be defined by WordPress
 */
$ssl_password = '*B[1[taq*$k!9t(AhnRg|P h>*{B3(p@9Z@H1h#sb6?0cFT4FJjh>=h*UaE@T~/[';


/**
 * Our data
 * @var array
 */
$decrypted_array = array(
	"theme_name" => "Theme name",
	"theme_slug" => "theme-slug",
	"version" => "1.0.0",
	"site" => false // This will be changed to client's site-url after running 'apt_illegal_copy_checker'
);

$decrypted_string = 'some string to encrypte here';

/**
 * Serialize it in order to store it in a file
 * @var string
 */
$serialized = serialize($decrypted_array);

/**
 * SSL encrypt method
 * @var string
 */
$method = 'aes128';

/**
 * Encrypted data
 * @var string
 */
$encrypted = openssl_encrypt($serialized, $method, $ssl_password);

/**
 * Descrypted data
 */
$decrypted = openssl_decrypt($encrypted, $method, $ssl_password);

/**
 * Show output
 */
echo $encrypted;
echo "\n";
var_dump(unserialize($decrypted));



/**
 * End Calculate time execution
 */
$executionEndTime = microtime(true);
$seconds = $executionEndTime - $executionStartTime;
echo "This script took $seconds to execute.\n";