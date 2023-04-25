<?php

namespace App;

require_once plugin_dir_path( __FILE__ ) . '/class-cron.php';

class Activator {
	/**
	 * @var \App\Cron
	 */
	private Cron $cron;

	public function __construct() {
		$this->cron = new Cron();
		register_activation_hook( WP_WEBSPARK_PLUGIN_FILE,
			[ $this, 'activate_plugin' ] );
		register_deactivation_hook( WP_WEBSPARK_PLUGIN_FILE,
			[ $this, 'deactivate_plugin' ] );
	}

	/**
	 * @return void
	 */
	public function activate_plugin(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				__(
					'The Woocommerce should be installed and activated.',
					'wpsync-webspark'
				)
			);
		} else {
			$this->cron->register_cron_task();
		}
	}

	/**
	 * @return void
	 */
	public function deactivate_plugin(): void {
		$this->cron->remove_cron_task();
	}
}