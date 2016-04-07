<?php
/**
 * This file is part of the "PHP Composter" package.
 *
 * © 2016 Franz Josef Kaiser
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$vendor = dirname( dirname( __FILE__ ) ) . '/vendor';

if ( ! realpath( "$vendor/" ) )
	die(
		 'Please install dependencies via Composer before running tests: '
		.'`wget https://getcomposer.org/composer.phar`'
	);

require_once "$vendor/autoload.php";

unset( $vendor );