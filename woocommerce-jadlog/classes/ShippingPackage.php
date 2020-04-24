<?php
namespace WooCommerce\Jadlog\Classes;

class ShippingPackage {

    public function __construct($wc_package, $modalidade, $options = array()) {
        include_once("Modalidade.php");
        include_once("VolumetricWeight.php");
        include_once('WeightConverter.php');
        include_once('DimensionConverter.php');

        $this->weight_converter =
            $options['weight_converter'] ?? new WeightConverter();
        $this->dimension_converter = 
            $options['dimension_converter'] ?? new DimensionConverter();

        $this->items      = $wc_package['contents'];
        $this->modalidade = $modalidade;
        $sufix = '_'.strtolower(Modalidade::TODOS[$modalidade]);
        $this->calculate_volumetric_weight = get_option('wc_settings_tab_jadlog_calcular_pesos_cubados'.$sufix);

        $this->_consolidate_totals();
    }

    private function _consolidate_totals() {
        $volume = 0.0;
        $weight = 0.0;
        $price  = 0.0;
        foreach ($this->items as $item) {
            $quantity = floatval($item['quantity']);
            $product  = wc_get_product($item['product_id']);

            $width  = $this->dimension_converter->to_meter(floatval($product->get_width()));
            $height = $this->dimension_converter->to_meter(floatval($product->get_height()));
            $length = $this->dimension_converter->to_meter(floatval($product->get_length()));
            $volume = $volume + ($width * $length * $height) * $quantity;

            $weight = $weight + floatval($product->get_weight()) * $quantity;

            $price  = $price + floatval($product->get_price()) * $quantity;
        }

        $this->volume = $volume;
        $this->weight = $this->weight_converter->to_kg($weight);
        $this->price  = $price;
    }

    public function get_price() {
        return $this->price;
    }

    public function get_volume() {
        return $this->volume;
    }

    public function get_weight() {
        return $this->weight;
    }

    public function get_volumetric_weight() {
        if (!isset($this->volumetric_weight)) {
            $volume = $this->get_volume();
            switch($this->calculate_volumetric_weight) {
            case 'PADRAO':
                $volumetric_weight = new VolumetricWeight(Modalidade::modal($this->modalidade), $volume);
                $this->volumetric_weight = $volumetric_weight->get_value();
                break;
            case Modalidade::MODAL_RODOVIARIO:
                $volumetric_weight = new VolumetricWeight(Modalidade::MODAL_RODOVIARIO, $volume);
                $this->volumetric_weight = $volumetric_weight->get_value();
                break;
            case Modalidade::MODAL_AEREO:
                $volumetric_weight = new VolumetricWeight(Modalidade::MODAL_AEREO, $volume);
                $this->volumetric_weight = $volumetric_weight->get_value();
                break;
            case 'NAO_CALCULAR':
                $this->volumetric_weight = null;
                break;
            default:
                $this->volumetric_weight = null;
            }
        }
        return $this->volumetric_weight;
    }

    public function get_effective_weight() {
        if (!isset($this->effective_weight)) {
            $weight            = $this->get_weight();
            $volumetric_weight = $this->get_volumetric_weight();
            $this->effective_weight = empty($volumetric_weight) ? $weight : max($weight, $volumetric_weight);
        }
        return $this->effective_weight;
    }
}
