<?php 

class ShippingPriceInCartCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->resizeWindow(1200, 960);
    }

    public function showShippingPriceInCart(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->click('Shop');
        $I->click('.add_to_cart_button');
        $I->click('.added_to_cart');
        $I->click('.shipping-calculator-button');
        $I->waitForElementClickable('#calc_shipping_postcode');
        $I->fillField('#calc_shipping_postcode', '01311-300');
        $I->pressKey('#calc_shipping_postcode', \Facebook\WebDriver\WebDriverKeys::ENTER);
        $I->waitForElement('#shipping_method');
        $shipping_methods = $I->grabTextFrom('#shipping_method');

        $I->assertRegExp('/Jadlog Expresso - \d+ dias? (útil|úteis): +R\$ *[\d\.]+,\d\d/', $shipping_methods);
        $I->assertRegExp('/Jadlog Package - \d+ dias? (útil|úteis): +R\$ *[\d\.]+,\d\d/', $shipping_methods);
        $I->assertRegExp('/Retire no ponto Pickup Jadlog .+ - \d+ dias? (útil|úteis): +R\$ *[\d\.]+,\d\d/', $shipping_methods);

        $I->waitForElementClickable('.checkout-button');
        $I->pressKey('.checkout-button', \Facebook\WebDriver\WebDriverKeys::ENTER);

        $I->seeElement('.woocommerce-billing-fields > h3');
        $I->seeElement('#order_review_heading');
        $I->seeElement('#place_order');

        $I->waitForElement('#shipping_method');
        $shipping_methods = $I->grabTextFrom('#shipping_method');

        $I->assertRegExp('/Jadlog Expresso - \d+ dias? (útil|úteis): +R\$ *[\d\.]+,\d\d/', $shipping_methods);
        $I->assertRegExp('/Jadlog Package - \d+ dias? (útil|úteis): +R\$ *[\d\.]+,\d\d/', $shipping_methods);
        $I->assertRegExp('/Retire no ponto Pickup Jadlog .+ - \d+ dias? (útil|úteis): +R\$ *[\d\.]+,\d\d/', $shipping_methods);
    }
}
