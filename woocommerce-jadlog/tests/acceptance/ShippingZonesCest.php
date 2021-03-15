<?php

class ShippingZonesCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->resizeWindow(1200, 960);
    }

    public function jadlogShippingMethodMustBeAvailable(AcceptanceTester $I)
    {
        $I->loginAs('admin', 'admin');
        $I->amOnAdminPage('/admin.php?page=wc-settings&tab=shipping');

        $I->click('Adicionar área de entrega');
        $I->fillField(['name' => 'zone_name'], 'Zona de testes');

        $I->click('Adicionar método de entrega');
        $I->selectOption('select[name=add_method_id]', 'Jadlog');
        $I->click('Adicionar método de entrega', '.wc-backbone-modal');
        $I->see('Jadlog');
        $I->see('Modalidades Package, Expresso e Pickup');

        $I->fillField('input[placeholder="Selecione as regiões desta área"]', 'Argentina');
        $I->see('Buenos Aires');
        $I->pressKey('#select2-zone_locations-results li', \Facebook\WebDriver\WebDriverKeys::ENTER);

        $I->click('Salvar alterações');
        $I->click('Áreas de entrega');
        $I->seeLink('Zona de testes');
    }
}
