<?php
/**
 * Toggl SPL Autoloader
 *
 *  Allow class autoloading using SPL. Only loads classes that begin with 'toggl' and is
 * case insensitive.
 *
 * @file
 * @link http://php.net/manual/en/function.spl-autoload-register.php
 * @author Timothy M. Crider <timcrider@gmail.com>
 */

/**
 * Use current directory of the autoloader file as the base toggl directory
 */
if (!defined('TOGGL_DIR')) {
	define('TOGGL_DIR', dirname(__FILE__).'/');
}

/**
 * Autoloader function
 */
function togglAutoload($class) {
	// Do not try to load non toggl classes
	if (!preg_match('/^toggl/i', $class)) {
		return false;
	}

	$tryFile = sprintf("%s%s.php", TOGGL_DIR, $class);
	return (file_exists($tryFile) && is_readable($tryFile)) ? include $tryFile : false;
}

/**
 * Register the autoloader with PHP
 */
spl_autoload_register('togglAutoload');
