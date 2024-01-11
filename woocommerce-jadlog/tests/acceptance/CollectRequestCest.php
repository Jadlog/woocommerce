<?php

class CollectRequestCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->resizeWindow(1200, 960);
    }

    public function manageCollectRequest(AcceptanceTester $I)
    {
        $I->loginAs('admin', 'admin');

        $I->amOnAdminPage('/admin.php?page=jadlog');
        $I->see('João Silva');

        // Incluir
        update_option(
            'wc_settings_tab_jadlog_url_inclusao_pedidos',
            'https://'.$_ENV['MOCK_SERVER'].'/embarcador/api/pedido/incluir');
        $I->click('Enviar');
        $I->fillField(['name' => 'nr_doc'], '1234');
        $I->click('div.ui-dialog-buttonset > button:nth-child(2)');
        $I->waitForAlert();
        $I->seeInPopup('Shipment ID: 222');
        $I->seeInPopup('Solicitação de coleta: 111');
        $I->acceptPopup();
        $I->see('Pedido de coleta enviado');

        // Consultar
        update_option(
            'wc_settings_tab_jadlog_url_consulta_pedidos',
            'https://'.$_ENV['MOCK_SERVER'].'/embarcador/api/tracking/consultar');
        $I->amOnAdminPage('/admin.php?page=jadlog');
        $I->click('Consultar');
        $I->waitForElementVisible('#tracking-dialog', 5);
        $I->see('ENTREGUE');
        $I->see('333');
        $I->see('222');
        $I->see('20/01/2021');
        $I->see('90001234567890');
        $I->see('R$ 125,34');
        $I->see('5,6 kg');
        $I->see('José da Silva');
        $I->see('111.222.333-44');
        $I->see('21/01/2021');
        $I->see('Saiu para entrega');
        $I->see('Matriz');
        $I->click('Fechar');

        // Cancelar
        update_option(
            'wc_settings_tab_jadlog_url_cancelamento_pedidos',
            'https://'.$_ENV['MOCK_SERVER'].'/embarcador/api/pedido/cancelar');
        $I->click('Cancelar');
        $I->waitForAlert();
        $I->seeInPopup('Deseja cancelar');
        $I->acceptPopup();
        $I->waitForAlert();
        $I->seeInPopup('Pedido de coleta cancelado');
        $I->acceptPopup();
        $I->see('Pedido de coleta cancelado');
    }
}

