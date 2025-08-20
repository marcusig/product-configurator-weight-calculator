<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class MKL_PC_Weight_Calc {
	public function __construct() {
		// add_filter( 'mkl_pc_choice_default_settings', array( $this, 'configurator_choice_options' ) );
		add_filter( 'tmpl-pc-configurator-choice-item-label', array( $this, 'choice_label' ) );
		add_action( 'mkl_pc_frontend_configurator_footer_form', array( $this, 'output_weight_markup' ), 8 );
		add_filter( 'mkl_product_configurator_get_front_end_data', array( $this, 'add_base_weight' ), 20, 2 );
		add_action( 'mkl_pc_frontend_templates_after', array( $this, 'add_calculations_script' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ), 100 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'wc_cart_add_item_data' ), 10, 3 ); 
		add_filter( 'woocommerce_get_item_data', array( $this, 'wc_cart_get_item_data' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_weight_to_order' ), 20, 4 );
		
	}
	
	/**
	 * Add the weight to the order summary
	 */
	public function add_weight_to_order( $item, $cart_item_key, $values, $order ) { 
		if ( isset( $values['pc_total_weight'] ) ) {
			$item->add_meta_data( __( 'Weight', 'product-configurator-weight-calculator' ), $values['pc_total_weight'] . ' g' , false );
		}
	}

	/**
	 * Display the weight to the cart / checkout summary
	 */
	public function wc_cart_get_item_data( $data, $cart_item ) { 
		if ( isset( $cart_item['pc_total_weight'] ) ) { 
			$data[] = array( 
				'key' => __( 'Weight', 'product-configurator-weight-calculator' ),
				'value' => $cart_item['pc_total_weight'] .' g',
			);			
		}
		return $data;
	}

	/**
	 * Saves the weight to the cart / checkout summary
	 */
	public function wc_cart_add_item_data( $cart_item_data, $product_id, $variation_id ) {
		if ( mkl_pc_is_configurable( $product_id ) && isset( $_POST[ 'pc-total-weight' ] ) ) {
			$cart_item_data['pc_total_weight'] = floatval( $_POST[ 'pc-total-weight' ] );
		}
		return $cart_item_data;
	}

	// Add the setting to the configurator admin
	// public function configurator_choice_options( $options ) {
	// 	$options['weight'] = array(
	// 		'label' => __( 'Weight', 'product-configurator-weight-calculator' ),
	// 		'type' => 'text',
	// 		'priority' => 40
	// 	);
	// 	return $options;
	// }

	// Add the weight in the choice label
	public function choice_label( $label ) {
		$label .= ' <# if ( data.weight ) { #><span class="choice-weight" data-weight="{{data.weight}}"><# if ( data.weight > 0 ) { #><# } #> '
		. '<span class="weight-label">' . __( 'Weight:', 'product-configurator-weight-calculator' ) . '</span>'
		.' {{data.weight}} g'
		.'</span> <# } #>';
		return $label;
	}

	// Add the weight in the footer
	public function output_weight_markup() {
		echo '<p class="extra-weight">';
		echo '<span class="extra-weight-label">' . __( 'Weight:', 'product-configurator-weight-calculator' ) . '</span>';
		echo ' <span class="pc-weight"> - </span>';
		echo '</p>';
	}

	public function add_base_weight( $info, $product ) {
		$w = $product->get_weight();
		if ( $w ) $info['product_info']['base_weight'] = $w * 1000;
		return $info;
	}

	public function add_calculations_script() {
		?>
		<script type="text/javascript">
		var PC = PC || {};
		
		(function($){
			// wp.hooks.addAction( 'PC.fe.configurator.choice-item.render', 'test', function( view ) {
			// 	console.log("zzz", view);
			// 	if ( view.$el.is( '.is-text' ) ) {
			// 		var $input = view.$el.find('button').after( '<input type="text">' );
			// 		$input.on( 'change', function( e ) {
			// 			view.model.set( 'custom_value', $input.val() );
			// 		} );
			// 	}
			// } );
			$(document).ready(function() {
				$( 'form.cart' ).append( $( '<input type="hidden" name="pc-total-weight" id="pc-total-weight" />' ) );
				wp.hooks.addAction( 'PC.fe.choice.change', 'mkl/product_configurator', function( model ) {
					setConfigWeight();
				});
				wp.hooks.addAction( 'PC.fe.open', 'mkl/product_configurator', function( model ) {
					setConfigWeight();
				});
			});
	
			function setConfigWeight() {
				if ( PC.fe.currentProductData ) {
					var product_info = PC.fe.currentProductData.product_info;
				} else if ( PC.productData && PC.productData.product_info ) {
					var product_info = PC.productData.product_info;
				} else {
					console.log( 'The product data was not found' );
					return;
				}
					
					var total = parseFloat( product_info.base_weight ) || 0;
				$( '.layer_choices li.active' ).each( function( index, el ) {
					var weight = parseFloat( $(el).find('.choice-weight' ).data('weight' ) );
					if ( weight ) total += parseFloat( weight );
				});

				$( '#pc-total-weight' ).val( total );

				if ( total > 0 ) {
					$( '.pc-weight' ).html( total + ' g' );
					$( '.extra-weight' ).addClass( 'show' );
				} else if ( total < 0 ) {
					$( '.pc-weight' ).html( total + ' g' );
					$( '.extra-weight' ).addClass( 'show' );
				} else {
					$( '.pc-weight' ).html( '' );
					$( '.extra-weight' ).removeClass( 'show' );
				}
			}
		})(jQuery);
	
		</script>
<?php	
	}

	public function add_styles() {

		$styles = '
		.mkl_pc .mkl_pc_container footer .extra-weight {
			visibility: hidden;
			height: 0;
			margin: 0;
			padding: 0;
			overflow: hidden;
			opacity: 0;
			transition: height 0.3s, margin 0.3s, padding 0.3s, opacity 0.3s;
		}
		.mkl_pc .mkl_pc_container .choice-weight {
			display: block;
		}
		.mkl_pc .mkl_pc_container .mkl_pc_toolbar section.choices .layer_choices li > button span.choice-price {
			margin-left: 0;
			padding-left: 0;
		}
		.mkl_pc .mkl_pc_container footer .extra-weight.show {
			visibility: visible;
			position: relative;
			padding-right: 10px;
			height: 3em;
			padding: 10px;
			overflow: visible;
			display: inline-block;
			opacity: 1;
			margin: 0 0 0 0;
		}
		@media (max-width: 660px) {
			.mkl_pc .mkl_pc_container footer .extra-weight.show {
			    padding: 0;
				text-align: left;
				line-height: 1;
			}
			.mkl_pc .mkl_pc_container footer .form span.extra-weight-label {
				font-size: 12px;
				text-transform: uppercase;
				line-height: 1;
				display: block;
				text-align: left;				
			}
			.mkl_pc .mkl_pc_container footer .form span.pc-weight {
				text-align: left;
				font-size: 1.2em;
			}
			.mkl_pc .mkl_pc_container footer .extra-weight.show,
			.mkl_pc .mkl_pc_container footer .extra-cost.show {
				margin-right: 16px;
			}
		}';

		wp_add_inline_style( 'mlk_pc/css', apply_filters( 'mkl-extra-weight-styles', $styles ) );
	}	
}
