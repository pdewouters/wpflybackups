<?php
/**
 * Plugin Name: Wpflybackups
 * Version: 0.1-alpha
 * Description: PLUGIN DESCRIPTION HERE
 * Author: YOUR NAME HERE
 * Author URI: YOUR SITE HERE
 * Plugin URI: PLUGIN SITE HERE
 * Text Domain: wpflybackups
 * Domain Path: /languages
 * @package Wpflybackups
 */

require_once __DIR__ . '/vendor/autoload.php';
use Symfony\Component\Finder\Finder;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
add_action( 'plugins_loaded', function() {
$test = update_option('wpfly_run', '1');
if ( defined('DOING_AJAX') && DOING_AJAX  ) return;
	if( get_option('wpfly_run') !== '1' ){
		return;
	}
	require_once __DIR__ . '/inc/wp-async-request.php';
	require_once __DIR__ . '/inc/wp-background-process.php';
	require_once __DIR__ . '/inc/zip-process.php';

	$finder = new Finder();
	$finder->files();
	//$finder->followLinks();
	$finder->ignoreDotFiles( true );
	$finder->ignoreUnreadableDirs();
	$finder->files()->notName('*.zip');
	$finder->in( '/srv/www/wordpress-default' )->exclude(['backupwordpress-cbaa44a22d-backups','node_modules','uploads']);


	$zip_process = new Zip_Process();

	foreach ( wpfly_get_files($finder) as $file_path ) {

		$zip_process->push_to_queue($file_path);
	}


	$zip_process->save()->dispatch();

	update_option( 'wpfly_run', '-1');
});

function wpfly_get_files($finder){
	foreach ( $finder as $entry ) {

		yield $entry->getPathname();
	}
}

