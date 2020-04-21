<?php

/**
 * Class Coupon.
 * @package WooCommerce\Tests\Coupon
 * @group coupons
 */
class WC_Tests_Coupon extends WC_Unit_Test_Case {

	public function tearDown() {
		WC()->cart->empty_cart();
		WC()->cart->remove_coupons();

		parent::tearDown();
	}

	/**
	 * Test the code/id differentiation of the coupon constructor.
	 *
	 * @since 3.2
	 */
	public function test_constructor_code_id() {
		$string_code_1 = 'test';

		// Coupon with a standard string code.
		$coupon_1 = new WC_Coupon();
		$coupon_1->set_code( $string_code_1 );
		$coupon_1->save();

		// Coupon with a string code that is the same as coupon 1's ID.
		$coupon_2 = new WC_Coupon();
		$coupon_2->set_code( (string) $coupon_1->get_id() );
		$coupon_2->save();

		$int_id_1      = $coupon_1->get_id();
		$int_id_2      = $coupon_2->get_id();
		$string_code_2 = $coupon_2->get_code();

		// Test getting a coupon by integer ID.
		$test_coupon = new WC_Coupon( $int_id_1 );
		$this->assertEquals( $int_id_1, $test_coupon->get_id() );
		$test_coupon = new WC_Coupon( $int_id_2 );
		$this->assertEquals( $int_id_2, $test_coupon->get_id() );

		// Test getting a coupon by string code.
		$test_coupon = new WC_Coupon( $string_code_1 );
		$this->assertEquals( $string_code_1, $test_coupon->get_code() );
		$test_coupon = new WC_Coupon( $string_code_2 );
		$this->assertEquals( $string_code_2, $test_coupon->get_code() );

		// Test getting a coupon by string id.
		// Required for backwards compatibility, but will try and initialize coupon by code if possible first.
		$test_coupon = new WC_Coupon( (string) $coupon_2->get_id() );
		$this->assertEquals( $coupon_2->get_id(), $test_coupon->get_id() );

		// Test getting a coupon by coupon object
		$test_coupon = new WC_Coupon( $coupon_1 );
		$this->assertEquals( $test_coupon->get_id(), $coupon_1->get_id() );
	}

	/**
	 * Test add_discount method.
	 *
	 * @since 2.3
	 */
	public function test_add_discount() {

		// Create coupon
		$coupon = WC_Helper_Coupon::create_coupon();

		// Add coupon, test return statement
		$this->assertTrue( WC()->cart->add_discount( $coupon->get_code() ) );

		// Test if total amount of coupons is 1
		$this->assertEquals( 1, count( WC()->cart->get_applied_coupons() ) );
	}

	/**
	 * Test add_discount method.
	 *
	 * @since 2.3
	 */
	public function test_add_discount_duplicate() {

		// Create coupon
		$coupon = WC_Helper_Coupon::create_coupon();

		// Add coupon
		$this->assertTrue( WC()->cart->add_discount( $coupon->get_code() ) );

		// Add coupon again, test return statement
		$this->assertFalse( WC()->cart->add_discount( $coupon->get_code() ) );

		// Test if total amount of coupons is 1
		$this->assertEquals( 1, count( WC()->cart->get_applied_coupons() ) );
	}

	/**
	 * Test fixed cart discount method.
	 *
	 * @since 2.3
	 */
	public function test_fixed_cart_discount() {

		// Create product
		$product = WC_Helper_Product::create_simple_product();
		$product->set_regular_price( 10 );
		$product->save();

		// Create coupon
		$coupon = WC_Helper_Coupon::create_coupon();
		update_post_meta( $coupon->get_id(), 'discount_type', 'fixed_cart' );
		update_post_meta( $coupon->get_id(), 'coupon_amount', '5' );

		// Create a flat rate method
		WC_Helper_Shipping::create_simple_flat_rate();

		// Add product to cart
		WC()->cart->add_to_cart( $product->get_id(), 1 );

		// Add coupon
		WC()->cart->add_discount( $coupon->get_code() );

		// Set the flat_rate shipping method
		WC()->session->set( 'chosen_shipping_methods', array( 'flat_rate' ) );
		WC()->cart->calculate_totals();

		// Test if the cart total amount is equal 15
		$this->assertEquals( 15, WC()->cart->total );
	}

	/**
	 * Test fixed product discount method.
	 *
	 * @since 2.3
	 */
	public function test_fixed_product_discount() {

		// Create product
		$product = WC_Helper_Product::create_simple_product();
		$product->set_regular_price( 10 );
		$product->save();

		// Create coupon
		$coupon = WC_Helper_Coupon::create_coupon();
		update_post_meta( $coupon->get_id(), 'discount_type', 'fixed_product' );
		update_post_meta( $coupon->get_id(), 'coupon_amount', '5' );

		// Create a flat rate method - $10
		WC_Helper_Shipping::create_simple_flat_rate();

		// Add fee - $10
		WC_Helper_Fee::add_cart_fee();

		// Add product to cart
		WC()->cart->add_to_cart( $product->get_id(), 1 );

		// Add coupon
		WC()->cart->add_discount( $coupon->get_code() );

		// Set the flat_rate shipping method
		WC()->session->set( 'chosen_shipping_methods', array( 'flat_rate' ) );
		WC()->cart->calculate_totals();

		// Test if the cart total amount is equal 25
		$this->assertEquals( 25, WC()->cart->total );
	}

	/**
	 * Test percent product discount method.
	 *
	 * @since 2.3
	 */
	public function test_percent_discount() {

		// Create product
		$product = WC_Helper_Product::create_simple_product();
		$product->set_regular_price( 10 );
		$product->save();

		// Create coupon
		$coupon = WC_Helper_Coupon::create_coupon();
		update_post_meta( $coupon->get_id(), 'discount_type', 'percent' );
		update_post_meta( $coupon->get_id(), 'coupon_amount', '5' );

		// Create a flat rate method
		WC_Helper_Shipping::create_simple_flat_rate();

		// Add fee
		WC_Helper_Fee::add_cart_fee();

		// Add product to cart
		WC()->cart->add_to_cart( $product->get_id(), 1 );

		// Add coupon
		WC()->cart->add_discount( $coupon->get_code() );

		// Set the flat_rate shipping method
		WC()->session->set( 'chosen_shipping_methods', array( 'flat_rate' ) );
		WC()->cart->calculate_totals();

		// Test if the cart total amount is equal 29.5
		$this->assertEquals( 29.5, WC()->cart->total );
	}

	/**
	 * Test date setters/getters.
	 *
	 * @since 3.0.0
	 */
	public function test_dates() {
		$valid_coupon = WC_Helper_Coupon::create_coupon();
		$valid_coupon->set_date_expires( time() + 1000 );
		$valid_coupon->set_date_created( time() );
		$valid_coupon->set_date_modified( time() );

		$expired_coupon = WC_Helper_Coupon::create_coupon();
		$expired_coupon->set_date_expires( time() - 10 );
		$expired_coupon->set_date_created( time() - 20 );
		$expired_coupon->set_date_modified( time() - 20 );

		$this->assertInstanceOf( 'WC_DateTime', $valid_coupon->get_date_created() );
		$this->assertTrue( $valid_coupon->is_valid() );
		$this->assertFalse( $expired_coupon->is_valid() );
		$this->assertEquals( $expired_coupon->get_error_message(), $expired_coupon->get_coupon_error( WC_Coupon::E_WC_COUPON_EXPIRED ) );
	}

	/**
	 * Test an item limit for percent discounts.
	 */
	public function test_percent_discount_item_limit() {
		// Create product
		$product = WC_Helper_Product::create_simple_product();
		update_post_meta( $product->get_id(), '_price', '10' );
		update_post_meta( $product->get_id(), '_regular_price', '10' );

		// Create coupon
		$coupon = WC_Helper_Coupon::create_coupon(
			'dummycoupon',
			array(
				'discount_type'          => 'percent',
				'coupon_amount'          => '5',
				'limit_usage_to_x_items' => 1,
			)
		);

		// We need this to have the calculate_totals() method calculate totals.
		if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			define( 'WOOCOMMERCE_CHECKOUT', true );
		}

		// Add 2 products and coupon to cart.
		WC()->cart->add_to_cart( $product->get_id(), 2 );
		WC()->cart->add_discount( $coupon->get_code() );
		WC()->cart->calculate_totals();

		// Test if the cart total amount is equal 19.5 (coupon only applying to one item).
		$this->assertEquals( 19.5, WC()->cart->total );
	}

	public function test_custom_discount_item_limit() {
		// Register custom discount type.
		WC_Helper_Coupon::register_custom_type( __FUNCTION__ );

		// Create product
		$product = WC_Helper_Product::create_simple_product();
		update_post_meta( $product->get_id(), '_price', '10' );
		update_post_meta( $product->get_id(), '_regular_price', '10' );

		// Create coupon
		$coupon = WC_Helper_Coupon::create_coupon(
			'dummycoupon',
			array(
				'discount_type'          => __FUNCTION__,
				'coupon_amount'          => '5',
				'limit_usage_to_x_items' => 1,
			)
		);

		// Add 4 products and coupon to cart.
		WC()->cart->add_to_cart( $product->get_id(), 4 );
		WC()->cart->add_discount( $coupon->get_code() );
		WC()->cart->calculate_totals();

		// Test if the cart total amount is equal 39.5 (coupon only applying to one item).
		$this->assertEquals( 39.5, WC()->cart->total );
	}
}
