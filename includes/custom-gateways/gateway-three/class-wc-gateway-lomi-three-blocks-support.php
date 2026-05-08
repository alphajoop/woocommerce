<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WC_Gateway_Lomi_Three_Blocks_Support extends WC_Gateway_Custom_Lomi_Blocks_Support {

	/**
	 * Payment method id.
	 *
	 * @var string
	 */
	protected $name = 'lomi-three';
}
