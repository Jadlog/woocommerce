<?php
namespace WooCommerce\Jadlog\Classes;

class PudoInfoTest extends \Codeception\TestCase\WPTestCase
{
    private $pudo_data;

    public function setUp(): void {
        parent::setUp();

        $this->pudo_data = array('pudoEnderecoList' => array(array()));
    }

    public function test_nao_esta_ativo() {
        $subject = new PudoInfo(null);
        $this->assertTrue($subject->nao_esta_ativo());

        $this->pudo_data['ativo'] = null;
        $subject = new PudoInfo($this->pudo_data);
        $this->assertTrue($subject->nao_esta_ativo());

        $this->pudo_data['ativo'] = 'N';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertTrue($subject->nao_esta_ativo());

        $this->pudo_data['ativo'] = 'S';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertFalse($subject->nao_esta_ativo());
    }

    public function test_get_id() {
        $subject = new PudoInfo(null);
        $this->assertNull($subject->get_id());

        $this->pudo_data['pudoId'] = 123;
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals(123, $subject->get_id());
    }

    public function test_get_name() {
        $subject = new PudoInfo(null);
        $this->assertNull($subject->get_name());

        $this->pudo_data['razao'] = 'Jadlog';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('Jadlog', $subject->get_name());
    }

    public function test_get_latitude() {
        $subject = new PudoInfo(null);
        $this->assertNull($subject->get_latitude());

        $subject = new PudoInfo($this->pudo_data);
        $this->assertNull($subject->get_latitude());

        $this->pudo_data['pudoEnderecoList'][0]['latitude'] = 23.455;
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals(23.455, $subject->get_latitude());
    }

    public function test_get_longitude() {
        $subject = new PudoInfo(null);
        $this->assertNull($subject->get_longitude());

        $subject = new PudoInfo($this->pudo_data);
        $this->assertNull($subject->get_longitude());

        $this->pudo_data['pudoEnderecoList'][0]['longitude'] = -45.8;
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals(-45.8, $subject->get_longitude());
    }

    public function test_get_postcode() {
        $subject = new PudoInfo(null);
        $this->assertNull($subject->get_postcode());

        $subject = new PudoInfo($this->pudo_data);
        $this->assertNull($subject->get_postcode());

        $this->pudo_data['pudoEnderecoList'][0]['cep'] = '10222999';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('10222999', $subject->get_postcode());
    }

    public function test_get_openning_hours() {
        $subject = new PudoInfo(null);
        $this->assertNull($subject->get_openning_hours());

        $this->pudo_data['pudoHorario'] = array('domH1OpenTm' => '0000');
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals(
            array('domH1OpenTm' => '0000'), $subject->get_openning_hours());
    }

    public function test_get_formatted_address() {
        $subject = new PudoInfo(null);
        $this->assertNull($subject->get_formatted_address());

        $this->pudo_data['pudoEnderecoList'][0]['endereco'] = 'Rua das Flores';
        $this->pudo_data['pudoEnderecoList'][0]['numero'] = '';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('Rua das Flores', $subject->get_formatted_address());

        $this->pudo_data['pudoEnderecoList'][0]['numero'] = '90';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('Rua das Flores, 90', $subject->get_formatted_address());

        $this->pudo_data['pudoEnderecoList'][0]['bairro'] = 'V. Ré';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('Rua das Flores, 90 - V. Ré', $subject->get_formatted_address());

        $this->pudo_data['pudoEnderecoList'][0]['bairro'] = 'V. Ré';
        $this->pudo_data['pudoEnderecoList'][0]['compl2'] = 'V. Ré';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('Rua das Flores, 90 - V. Ré', $subject->get_formatted_address());

        $this->pudo_data['pudoEnderecoList'][0]['compl2'] = 'fundos';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('Rua das Flores, 90 - fundos - V. Ré', $subject->get_formatted_address());

        $this->pudo_data['pudoEnderecoList'][0]['cidade'] = 'Laguinho Grande';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('Rua das Flores, 90 - fundos - V. Ré - Laguinho Grande', $subject->get_formatted_address());

        $this->pudo_data['pudoEnderecoList'][0]['cep'] = '9888000';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('Rua das Flores, 90 - fundos - V. Ré - Laguinho Grande - CEP 09888-000', $subject->get_formatted_address());
    }

    public function test_get_formatted_name_and_address() {
        $subject = new PudoInfo(null);
        $this->assertEquals('', $subject->get_formatted_name_and_address());

        $this->pudo_data['razao'] = 'Fábrica Abstrata';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('Fábrica Abstrata', $subject->get_formatted_name_and_address());

        $this->pudo_data['pudoEnderecoList'][0]['cep'] = '1234567890';
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('Fábrica Abstrata ( - CEP 1234567890)', $subject->get_formatted_name_and_address());

        unset($this->pudo_data['razao']);
        $subject = new PudoInfo($this->pudo_data);
        $this->assertEquals('( - CEP 1234567890)', $subject->get_formatted_name_and_address());
    }
}
