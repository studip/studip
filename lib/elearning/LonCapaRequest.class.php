<?php
/**
 *
 * This class is used to communicate with LonCapa
 *
 * @depends curl
 * @modulegroup  elearning_interface_modules
 * @module       LonCapaContentModule
 * @package  ELearning-Interface
 */
class LonCapaRequest
{
    /**
     * options for curl
     * @var array
     */
    protected $options;
    /**
     * curl resource
     * @var resource
     */
    protected $ch;

    /**
     * LonCapaRequest constructor.
     */
    public function __construct()
    {
        $this->ch = curl_init();
        $this->initOptions();
    }

    /**
     * initializes curl options
     */
    public function initOptions()
    {
        $this->options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            //CURLOPT_CAINFO => '',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ];
    }

    /**
     * close connection
     */
    public function __destruct()
    {
        curl_close($this->ch);
    }

    /**
     * set curl options
     * @param $key
     * @param $value
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * do a curl request on the given url and return the result if successfull
     *
     * @param $url string
     * @param array $postfields
     * @return string
     */
    public function request($url, $postfields = null)
    {
        $result = $this->sendRequest($url, $postfields);
        if ($result['statusCode'] == 200) {
            return $result['response'];
        } else {
            // TODO: fehlermeldung wäre schöner
            return null;
        }
    }

    /**
     * do a curl request on the given url and return the result if successfull
     * @param $url string
     * @param array $postfields
     * @return array
     */
    protected function sendRequest($url, $postfields = null)
    {
        $options = $this->options;
        $options[CURLOPT_URL] = $url;

        if ($postfields) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $postfields;
        }

        curl_setopt_array($this->ch, $options);
        $response = curl_exec($this->ch);

        $statusCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $last_error = curl_error($this->ch);
            Log::error(__CLASS__ . ' curl_exec failed: ' . $last_error);
        }
        return compact('statusCode', 'response');
    }
}
