<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woo product fields for linking subscription products to lomi catalog prices.
 */
class WC_Gateway_Lomi_Product_Admin {

	/**
	 * Bootstrap hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'add_product_data_tab' ), 99 );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'render_product_data_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product_meta' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'render_variation_fields' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_variation_meta' ), 10, 2 );
		add_filter( 'manage_edit-product_columns', array( __CLASS__, 'add_product_list_column' ) );
		add_action( 'manage_product_posts_custom_column', array( __CLASS__, 'render_product_list_column' ), 10, 2 );
	}

	/**
	 * Product data tab.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public static function add_product_data_tab( $tabs ) {
		$tabs['lomi'] = array(
			'label'    => __( 'lomi.', 'woo-lomi' ),
			'target'   => 'lomi_product_data',
			'class'    => array( 'show_if_simple', 'show_if_variable', 'show_if_subscription', 'show_if_variable-subscription' ),
			'priority' => 80,
		);

		return $tabs;
	}

	/**
	 * Simple / parent product fields.
	 */
	public static function render_product_data_panel() {
		global $post;

		echo '<div id="lomi_product_data" class="panel woocommerce_options_panel hidden">';
		woocommerce_wp_text_input(
			array(
				'id'          => '_lomi_price_id',
				'label'       => __( 'lomi price ID', 'woo-lomi' ),
				'description' => __( 'UUID of the recurring price in your lomi. catalog. Required for WooCommerce Subscriptions checkout.', 'woo-lomi' ),
				'desc_tip'    => true,
				'value'       => get_post_meta( $post->ID, '_lomi_price_id', true ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => '_lomi_product_id',
				'label'       => __( 'lomi product ID (optional)', 'woo-lomi' ),
				'description' => __( 'UUID of the lomi. catalog product. Optional if the price ID is set — lomi. can derive the product from the price.', 'woo-lomi' ),
				'desc_tip'    => true,
				'value'       => get_post_meta( $post->ID, '_lomi_product_id', true ),
			)
		);
		echo '<p class="form-field">';
		echo '<a href="https://dashboard.lomi.africa" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Open lomi. dashboard to copy price IDs from your catalog.', 'woo-lomi' ) . '</a>';
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Variation-level mapping.
	 *
	 * @param int     $loop           Loop index.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation      Variation post.
	 */
	public static function render_variation_fields( $loop, $variation_data, $variation ) {
		$variation_id = $variation->ID;
		echo '<div class="form-row form-row-full">';
		woocommerce_wp_text_input(
			array(
				'id'            => '_lomi_price_id[' . $loop . ']',
				'name'          => '_lomi_price_id[' . $loop . ']',
				'value'         => get_post_meta( $variation_id, '_lomi_price_id', true ),
				'label'         => __( 'lomi price ID', 'woo-lomi' ),
				'wrapper_class' => 'form-row form-row-full',
				'description'   => __( 'Recurring lomi. price UUID for this variation.', 'woo-lomi' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'            => '_lomi_product_id[' . $loop . ']',
				'name'          => '_lomi_product_id[' . $loop . ']',
				'value'         => get_post_meta( $variation_id, '_lomi_product_id', true ),
				'label'         => __( 'lomi product ID (optional)', 'woo-lomi' ),
				'wrapper_class' => 'form-row form-row-full',
			)
		);
		echo '</div>';
	}

	/**
	 * Save simple / parent product meta.
	 *
	 * @param int $post_id Product ID.
	 */
	public static function save_product_meta( $post_id ) {
		if ( isset( $_POST['_lomi_price_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$price_id = sanitize_text_field( wp_unslash( $_POST['_lomi_price_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $post_id, '_lomi_price_id', $price_id );
		}
		if ( isset( $_POST['_lomi_product_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$product_id = sanitize_text_field( wp_unslash( $_POST['_lomi_product_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $post_id, '_lomi_product_id', $product_id );
		}
	}

	/**
	 * Save variation meta.
	 *
	 * @param int $variation_id Variation ID.
	 * @param int $loop         Loop index.
	 */
	public static function save_variation_meta( $variation_id, $loop ) {
		if ( isset( $_POST['_lomi_price_id'][ $loop ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$price_id = sanitize_text_field( wp_unslash( $_POST['_lomi_price_id'][ $loop ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $variation_id, '_lomi_price_id', $price_id );
		}
		if ( isset( $_POST['_lomi_product_id'][ $loop ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$product_id = sanitize_text_field( wp_unslash( $_POST['_lomi_product_id'][ $loop ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $variation_id, '_lomi_product_id', $product_id );
		}
	}

	/**
	 * Admin list column for subscription products.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function add_product_list_column( $columns ) {
		$columns['lomi_linked'] = __( 'lomi linked', 'woo-lomi' );
		return $columns;
	}

	/**
	 * Render list column.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Product ID.
	 */
	public static function render_product_list_column( $column, $post_id ) {
		if ( 'lomi_linked' !== $column ) {
			return;
		}

		$product = wc_get_product( $post_id );
		if ( ! $product || ! self::product_is_subscription_type( $product ) ) {
			echo '&mdash;';
			return;
		}

		$price_id = self::get_product_lomi_price_id( $product );
		if ( $price_id ) {
			echo '<span style="color:#007017;">' . esc_html__( 'Linked', 'woo-lomi' ) . '</span>';
			return;
		}

		echo '<span style="color:#a00;">' . esc_html__( 'Not linked', 'woo-lomi' ) . '</span>';
	}

	/**
	 * Whether product is a subscription type.
	 *
	 * @param WC_Product $product Product.
	 * @return bool
	 */
	public static function product_is_subscription_type( $product ) {
		return $product->is_type( array( 'subscription', 'variable-subscription', 'subscription_variation' ) );
	}

	/**
	 * Resolve lomi price ID for a product or variation.
	 *
	 * @param WC_Product|int $product Product or ID.
	 * @return string
	 */
	public static function get_product_lomi_price_id( $product ) {
		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
		if ( ! $product ) {
			return '';
		}

		$target_id = $product->get_id();
		if ( $product->is_type( 'variation' ) ) {
			$variation_price = get_post_meta( $target_id, '_lomi_price_id', true );
			if ( $variation_price ) {
				return sanitize_text_field( (string) $variation_price );
			}
			$target_id = $product->get_parent_id();
		}

		return sanitize_text_field( (string) get_post_meta( $target_id, '_lomi_price_id', true ) );
	}

	/**
	 * Resolve lomi product ID for a product or variation.
	 *
	 * @param WC_Product|int $product Product or ID.
	 * @return string
	 */
	public static function get_product_lomi_product_id( $product ) {
		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
		if ( ! $product ) {
			return '';
		}

		$target_id = $product->get_id();
		if ( $product->is_type( 'variation' ) ) {
			$variation_product_id = get_post_meta( $target_id, '_lomi_product_id', true );
			if ( $variation_product_id ) {
				return sanitize_text_field( (string) $variation_product_id );
			}
			$target_id = $product->get_parent_id();
		}

		return sanitize_text_field( (string) get_post_meta( $target_id, '_lomi_product_id', true ) );
	}
}

WC_Gateway_Lomi_Product_Admin::init();
