<?php 

	if (!defined('BASEPATH')) exit('No direct script access allowed');
	
	$config['jwt_key'] = '8eccb5fde9963b95f7f0c891f904a91dd775bf5256dab0e9b9749c519d508d0d';
/*Generated token will expire in 1 minute for sample code
* Increase this value as per requirement for production
*/
	$config['token_timeout'] = 1;
/* End of file jwt.php */
/* Location: ./application/config/jwt.php */