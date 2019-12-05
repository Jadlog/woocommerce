<?php
/**
 * Created by PhpStorm.
 * User: quinho
 * Date: 26/10/17
 * Time: 14:54
 */
/**
 * Class JadLogMyPudo
 */
class JadLogMyPudo {

    /**
     * Constructor for your shipping class
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->url      = get_option('wc_settings_tab_jadlog_my_pudo');
        $this->key      = get_option('wc_settings_tab_jadlog_key_pudo');

        include_once('postal-code-helper.php');
    }

    /**
     * This function is used to get the city name.
     *
     * @access public
     * @param array $postalcode
     * @return void
     */
    public function getPudos($postalcode = null) {

        $postalCodeHelper = new PostalCodeHelper();

        $postalcode = str_replace('-', '', $postalcode);
        $city = $postalCodeHelper->getCityName($postalcode);
        $city = empty($city) ? ' ' : $city; //if cannot get city, uses string with spaces - API bug

        $url       = $this->url;
        $url      .= '?carrier=JAD&key='.urlencode($this->key);
        $url      .= '&address=&city='.urlencode($city);
        $url      .= '&zipCode='.urlencode($postalcode);
        $url      .= '&countrycode=BRA&requestID=12&date_from=&max_pudo_number=100';
        $url      .= '&max_distance_search=&weight=&category=&holiday_tolerant=';
        $response = wp_remote_get($url);
        $body     = wp_remote_retrieve_body($response);

        $xml = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);

        return $array;
    }

    /**
     * This function is used to get the city name.
     *
     * @access public
     * @param array $postalcode
     * @return void
     */
    public function getLatLongPudo($pudo_id = null) {

        $postalCodeHelper = new PostalCodeHelper();

        $postalcode = str_replace('-', '', $postalcode);
        $city = $postalCodeHelper->getCityName($postalcode);
        $city = empty($city) ? ' ' : $city; //if cannot get city, uses string with spaces - API bug

        $url       = $this->url;
        $url      .= '?carrier=JAD&key='.urlencode($this->key);
        $url      .= '&address=&city='.urlencode($city);
        $url      .= '&zipCode='.urlencode($postalcode);
        $url      .= '&countrycode=BRA&requestID=12&date_from=&max_pudo_number=100';
        $url      .= '&max_distance_search=&weight=&category=&holiday_tolerant=';
        $response = wp_remote_get($url);
        $body     = wp_remote_retrieve_body($response);

        $xml = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);

        return $array;

    }

}
