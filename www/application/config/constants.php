<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define("IS_TEST", strpos($_SERVER['SERVER_NAME'], 'scmreview') > 0);
include_once(APPPATH.'version.php');

define("SESS_ADMIN_USER_ID", "admin_user_id");
define("SESS_USER_ID", "user_id");
define("SESS_TEAM_ID", "team_id");

define("USER_TYPE_ADMIN", 99);
define("USER_TYPE_USER", 1);

define("IMG_SIZE_SM", 50);
define("IMG_SIZE_MD", 160);
define("IMG_SIZE_LG", 230);

define("INVITE_TYPE_PROJECT", 'project');
define("INVITE_TYPE_TEAM", 'team');

define("FILE_TYPE_SCREEN", 1);
define("FILE_TYPE_VIDEO", 2);

define("DEFAULT_LIMIT", 20);
define("DEFAULT_PAGE", 0);

define("ACTIVITY_TYPE_PROJECT_ADD", 1);
define("ACTIVITY_TYPE_PROJECT_UPDATE", 2);
define("ACTIVITY_TYPE_PROJECT_DELETE", 3);
define("ACTIVITY_TYPE_SCREEN_ADD", 4);
define("ACTIVITY_TYPE_SCREEN_UPDATE", 5);
define("ACTIVITY_TYPE_SCREEN_DELETE", 6);
define("ACTIVITY_TYPE_VIDEO_ADD", 7);
define("ACTIVITY_TYPE_VIDEO_UPDATE", 8);
define("ACTIVITY_TYPE_VIDEO_DELETE", 9);
define("ACTIVITY_TYPE_USER_TEAM_JOIN", 10);
define("ACTIVITY_TYPE_USER_PROJECT_JOIN", 11);


/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/* End of file constants.php */
/* Location: ./application/config/constants.php */