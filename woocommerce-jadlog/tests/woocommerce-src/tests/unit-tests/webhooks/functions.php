<?php
/**
 * Unit tests for Webhook functions.
 * @package WooCommerce\Tests\Webhook
 */

/**
 * Class Functions.
 * @package WooCommerce\Tests\Webhook
 * @since 2.3
 */
class WC_Tests_Webhook_Functions extends WC_Unit_Test_Case {

	/**
	 * Data provider for test_wc_is_webhook_valid_topic.
	 *
	 * @since 3.2.0
	 */
	public function data_provider_test_wc_is_webhook_valid_topic() {
		return array(
			array( true, wc_is_webhook_valid_topic( 'action.woocommerce_add_to_cart' ) ),
			array( true, wc_is_webhook_valid_topic( 'action.wc_add_to_cart' ) ),
			array( true, wc_is_webhook_valid_topic( 'product.created' ) ),
			array( true, wc_is_webhook_valid_topic( 'product.updated' ) ),
			array( true, wc_is_webhook_valid_topic( 'product.deleted' ) ),
			array( true, wc_is_webhook_valid_topic( 'product.restored' ) ),
			array( true, wc_is_webhook_valid_topic( 'order.created' ) ),
			array( true, wc_is_webhook_valid_topic( 'order.updated' ) ),
			array( true, wc_is_webhook_valid_topic( 'order.deleted' ) ),
			array( true, wc_is_webhook_valid_topic( 'order.restored' ) ),
			array( true, wc_is_webhook_valid_topic( 'customer.created' ) ),
			array( true, wc_is_webhook_valid_topic( 'customer.updated' ) ),
			array( true, wc_is_webhook_valid_topic( 'customer.deleted' ) ),
			array( true, wc_is_webhook_valid_topic( 'coupon.created' ) ),
			array( true, wc_is_webhook_valid_topic( 'coupon.updated' ) ),
			array( true, wc_is_webhook_valid_topic( 'coupon.deleted' ) ),
			array( true, wc_is_webhook_valid_topic( 'coupon.restored' ) ),
			array( false, wc_is_webhook_valid_topic( 'coupon.upgraded' ) ),
			array( false, wc_is_webhook_valid_topic( 'wc.product.updated' ) ),
			array( false, wc_is_webhook_valid_topic( 'missingdot' ) ),
			array( false, wc_is_webhook_valid_topic( 'with space' ) ),
			array( false, wc_is_webhook_valid_topic( 'action.woocommerce_login_credentials' ) ),
		);
	}

	/**
	 * Test wc_is_webhook_valid_topic().
	 *
	 * @dataProvider data_provider_test_wc_is_webhook_valid_topic
	 * @since 3.2.0
	 * @param bool  $assert Expected value.
	 * @param array $values Values to test.
	 */
	public function test_wc_is_webhook_valid_topic( $assert, $values ) {
		$this->assertEquals( $assert, $values );
	}

	/**
	 * Data provider for test_wc_is_webhook_valid_status.
	 *
	 * @since 3.5.3
	 */
	public function data_provider_test_wc_is_webhook_valid_status() {
		return array(
			array( true, wc_is_webhook_valid_status( 'active' ) ),
			array( true, wc_is_webhook_valid_status( 'paused' ) ),
			array( true, wc_is_webhook_valid_status( 'disabled' ) ),
			array( false, wc_is_webhook_valid_status( 'pending' ) ),
		);
	}

	/**
	 * Test wc_is_webhook_valid_status
	 *
	 * @dataProvider data_provider_test_wc_is_webhook_valid_status
	 * @since 3.5.3
	 * @param bool  $assert Expected outcome.
	 * @param array $values Values to test.
	 * @return void
	 */
	public function test_wc_is_webhook_valid_status( $assert, $values ) {
		$this->assertEquals( $assert, $values );
	}

	/**
	 * Test wc_get_webhook_statuses().
	 *
	 * @since 3.2.0
	 */
	public function test_wc_get_webhook_statuses() {
		$expected = array(
			'active'   => 'Active',
			'paused'   => 'Paused',
			'disabled' => 'Disabled',
		);

		$this->assertEquals( $expected, wc_get_webhook_statuses() );
	}

	/**
	 * Test wc_load_webhooks().
	 *
	 * @since 3.2.0
	 */
	public function test_wc_load_webhooks() {
		$webhook = $this->create_webhook();
		$this->assertTrue( wc_load_webhooks() );
		$webhook->delete( true );
		$this->assertFalse( wc_load_webhooks() );
	}

	/**
	 * Provide webhook statuses for tests.
	 *
	 * @since 3.5.0
	 */
	public function provider_webhook_statuses() {

		$webhook_statuses = array();

		foreach ( wc_get_webhook_statuses() as $status_key => $status_string ) {
			$webhook_statuses[] = array( $status_key );
		}

		return $webhook_statuses;
	}

	/**
	 * Test the $status param on wc_load_webhooks().
	 *
	 * @dataProvider provider_webhook_statuses
	 * @param string $status The status of the webhook to test.
	 * @since 3.5.0
	 */
	public function test_wc_load_webhooks_status( $status ) {

		$webhook = $this->create_webhook( 'action.woocommerce_some_action', $status );

		$this->assertTrue( wc_load_webhooks( '' ) );
		$this->assertTrue( wc_load_webhooks( $status ) );

		// Find a different, but still valid status.
		$other_status = ( 'active' === $status ) ? 'disabled' : 'active';

		$this->assertFalse( wc_load_webhooks( $other_status ) );

		$webhook->delete( true );
		$this->assertFalse( wc_load_webhooks( $status ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_wc_load_webhooks_status_invalid() {
		wc_load_webhooks( 'invalid_status' );
	}

	/**
	 * Test the $limit param on wc_load_webhooks().
	 *
	 * @since 3.5.0
	 */
	public function test_wc_load_webhooks_limit() {
		global $wp_filter;

		$webhook_one = $this->create_webhook( 'action.woocommerce_one_test' );
		$webhook_two = $this->create_webhook( 'action.woocommerce_two_test' );

		$this->assertTrue( wc_load_webhooks( '', 1 ) );
		$this->assertFalse( isset( $wp_filter['woocommerce_one_test'] ) );
		$this->assertTrue( isset( $wp_filter['woocommerce_two_test'] ) );

		$webhook_two->delete( true );

		$this->assertTrue( wc_load_webhooks( '', 1 ) );
		$this->assertTrue( isset( $wp_filter['woocommerce_one_test'] ) );

		$webhook_one->delete( true );

		$this->assertFalse( wc_load_webhooks( '', 1 ) );
	}

	/**
	 * Test the $status and $limit param on wc_load_webhooks().
	 *
	 * @dataProvider provider_webhook_statuses
	 * @param string $status The status of the webhook to test.
	 */
	public function test_wc_load_webhooks_status_and_limit( $status ) {
		global $wp_filter;

		$action_one  = 'woocommerce_one_test_status_' . $status;
		$webhook_one = $this->create_webhook( 'action.' . $action_one, $status );
		$action_two  = 'woocommerce_two_test_status_' . $status;
		$webhook_two = $this->create_webhook( 'action.' . $action_two, $status );

		$this->assertTrue( wc_load_webhooks( $status, 1 ) );
		$this->assertFalse( isset( $wp_filter[ $action_one ] ) );
		$this->assertTrue( isset( $wp_filter[ $action_two ] ) );

		$webhook_two->delete( true );

		$this->assertTrue( wc_load_webhooks( $status, 1 ) );
		$this->assertTrue( isset( $wp_filter[ $action_one ] ) );

		$webhook_one->delete( true );
		$this->assertFalse( wc_load_webhooks( $status, 1 ) );
	}

	/**
	 * Create and save a webhook for testing.
	 *
	 * @param string $topic The webhook topic for the test.
	 * @param string $status The status of the webhook to be tested.
	 */
	protected function create_webhook( $topic = 'action.woocommerce_some_action', $status = 'active' ) {

		$webhook = new WC_Webhook();
		$webhook->set_props(
			array(
				'status'       => $status,
				'name'         => 'Testing webhook',
				'user_id'      => 0,
				'delivery_url' => 'https://requestb.in/17jajv31',
				'secret'       => 'secret',
				'topic'        => $topic,
				'api_version'  => 2,
			)
		);
		$webhook->save();

		return $webhook;
	}
}
