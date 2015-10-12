<?

/**
 *
 * SonicWulf v1.0.0 Gold release PHP Framework
 * 
 * @package SonicWulf
 * @version v1.0.0
 * @author  Cristian Herrera <cristian.herrera@studiowolfree.com>
 * @copyright  Copyright 2015 (c) Studio Wolfree. Coral Springs, FL.
 * @link http://dev.studiowolfree.net
 *
 */

// Error reporting

error_reporting(-1);

// Anti CORS

header('Access-Control-Allow-Origin: http://'.$_SERVER['HTTP_HOST']);

// Preprocessing constants

define('PATH', str_replace('\\', '/', dirname(__FILE__)));
define('PHPV', phpversion());
define('OS', PHP_OS);
define('HOST', $_SERVER['HTTP_HOST']);
define('SWVER', 'SonicWulf Gold 1.0.0');

// Recommended PHP configuration

ini_set('short_open_tag', 'On');
ini_set('display_errors', 'On');
ini_set('expose_php', 'Off');
ini_set('session.name', 'SWSSID');

// Required files

require 'config.php';
require 'inc/Kernel.php';

// Let's boot this up! :D

new Kernel;

global $config; // Straight from the config file, no caching

// Starting session

session_start();

// Afterprocessing constants
// You can change these ones, but just make sure to keep their format

define('LOGFILE', $config['kernel']['logDir'].'/main.log');
define('DUMPFILE', $config['kernel']['logDir'].'/dump{x}.log');