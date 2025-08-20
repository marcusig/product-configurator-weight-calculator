<?php

/**
* Plugin Name: Product Configurator - Weight calculator
* Plugin URI: http://wc-product-configurator.com
* Description: Adds a weight field to the configurator
* Author: Marc Lacroix
* Author URI: http://mklacroix.com
* Version: 1.0.1
*
* Text Domain: product-configurator-weight-calculator
* Domain Path: /languages/
*
* Copyright: © 2015 mklacroix (email : marcus_lacroix@yahoo.fr)
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MKL_PC_WEIGHT_CALC_PATH', dirname(__FILE__) );

add_action( 'plugins_loaded', 'mkl_pc_weight_calc_init' );

require_once 'inc/plugin.php';

function mkl_pc_weight_calc_init() {
	// Only run if the main plugin is available
	if ( !class_exists( 'MKL\PC\Plugin' ) ) return;
	
	new MKL_PC_Weight_Calc();
	
}
