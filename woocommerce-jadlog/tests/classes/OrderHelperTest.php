<?php
namespace WooCommerce\Jadlog\Classes;

class OrderHelperTest extends \WP_UnitTestCase {

    private $subject, $order;

    public function setUp() {
        parent::setUp();
        include_once("OrderHelper.php");

        $this->order = new \WC_Order();
        $this->subject = new OrderHelper($this->order);
    }


    public function test_get_cpf_or_cnpj_when_there_is_only_cpf() {
        $this->order->update_meta_data('_billing_cpf', '111.222.333-44');
        $this->order->delete_meta_data('_biliing_cnpj');
        $this->assertEquals('111.222.333-44', $this->subject->get_cpf_or_cnpj());
    }

    public function test_get_cpf_or_cnpj_when_there_is_only_cnpj() {
        $this->order->update_meta_data('_billing_cnpj', '11.222.333/0001-44');
        $this->order->delete_meta_data('_biliing_cpf');
        $this->assertEquals('11.222.333/0001-44', $this->subject->get_cpf_or_cnpj());
    }

    public function test_get_cpf_or_cnpj_when_there_is_nothing() {
        $this->order->delete_meta_data('_biliing_cpf');
        $this->order->delete_meta_data('_biliing_cnpj');
        $this->assertEquals('', $this->subject->get_cpf_or_cnpj());
    }

    public function test_get_cpf_or_cnpj_when_there_is_cpf_and_cnpj() {
        $this->order->update_meta_data('_billing_cpf',  '111.222.333-44');
        $this->order->update_meta_data('_billing_cnpj', '11.222.333/0001-44');

        $this->order->update_meta_data('_billing_persontype', '1');
        $this->assertEquals('111.222.333-44', $this->subject->get_cpf_or_cnpj());

        $this->order->update_meta_data('_billing_persontype', '2');
        $this->assertEquals('11.222.333/0001-44', $this->subject->get_cpf_or_cnpj());

        $this->order->delete_meta_data('_billing_persontype');
        $this->assertEquals('111.222.333-44', $this->subject->get_cpf_or_cnpj());
    }
}
