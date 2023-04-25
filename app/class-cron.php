<?php

namespace App;

use Exception;
use WC_Product_Simple;

class Cron {

	public function __construct() {
		add_action( 'update_products', [ $this, 'update_products' ] );
	}

	/**
	 * @return void
	 */
	public function register_cron_task(): void {
		wp_schedule_event( time(), 'hourly', 'update_products' );
	}

	/**
	 * @return void
	 */
	public function remove_cron_task(): void {
		wp_clear_scheduled_hook( 'update_products' );
	}

	/**
	 * @return void
	 * @throws \WC_Data_Exception
	 */
	public function update_products(): void {
		try {
			$request = wp_remote_get( 'https://wp.webspark.dev/wp-api/products',
				[
					'timeout' => 20,
				] );

			if ( is_wp_error( $request ) ) {
				wp_die();
			}

			$products = json_decode( $request['body'] );

			if ( $products->error ) {
				wp_die();
			}

			$products_request = $products->data;

			$this->update_products_list( $products_request );

			$this->delete_invalid_products($products_request);
		} catch ( Exception $e ) {
			wp_die();
		}
	}

	/**
	 * @param  array  $products_request
	 *
	 * @return void
	 * @throws \WC_Data_Exception
	 */
	private function update_products_list( array $products_request ): void {
		foreach ( $products_request as $data ) {
			$product_id = wc_get_product_id_by_sku( $data->sku );
			$product    = new WC_Product_Simple();

			if ( isset( $product_id ) && $product_id !== 0 ) {
				$product = wc_get_product( $product_id );
			} else {
				$product->set_sku( $data->sku );
			}

			$product->set_name( $data->name );
			$product->set_regular_price( $data->price );
			$product->set_description( $data->description );
			$product->set_stock_quantity( $data->in_stock );
			$product->save();
		}
	}

	/**
	 * @param  array  $products_request
	 *
	 * @return void
	 */
	private function delete_invalid_products( array $products_request ): void {
		$args = [
			'post_type' => 'product',
			'posts_per_page' => - 1,
		];

		$products = get_posts( $args );

		$products_sku = array_map( function ( $product ) {
			return get_post_meta( $product->ID, '_sku', true );
		}, $products );

		$products_sku_request = array_map( function ( $product ) {
			return $product->sku;
		}, $products_request );

		$invalid_products_sku = array_diff( $products_sku_request,
			$products_sku );

		$invalid_products_id = array_map( function ( $sku ) {
			return wc_get_product_id_by_sku( $sku );
		}, $invalid_products_sku );

		foreach ( $invalid_products_id as $product_id ) {
			wp_delete_post( $product_id );
		}
	}
}