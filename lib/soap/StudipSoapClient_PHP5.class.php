<?
# Lifter002: TODO
# Lifter010: TODO
/**
* Adapter for using php5 ext:soap with Ilias3Soap
*
*
* @author   Andre Noack <noack@data-quest.de>
* @access   public
* @package  ELearning-Interface
*/
class StudipSoapClient
{
    var $soap_client;
    var $error;

    function __construct($path)
    {
        try {
            $this->soap_client = new SoapClient($path, ['trace' => 0]);
        } catch (SoapFault $fault) {
            $this->error = "<b>Soap Constructor Error</b><br>" . $fault->faultcode . ": ".$fault->faultstring."<br><br>";
        }
    }

    function _call($method, $params)
    {
        $result = false;
        if ($this->soap_client instanceOf SoapClient) {
            $this->faultstring = "";
            try {
                $result = $this->soap_client->__soapCall($method, $params);
            } catch  (SoapFault $fault) {
                $this->faultstring = $fault->faultstring;
                if (!in_array(mb_strtolower($this->faultstring), ["session not valid","session invalid", "session idled"])) {
                    $this->error .= "<hr><font size=\"-1\"><b>" . sprintf(_("SOAP-Fehler, Funktion \"%s\":"), $method) . "</b> " . $fault->faultstring . " (" .  $fault->faultcode . ")<br>".print_r($params,1).'</font><hr>';
                    error_log($this->error);
                }
                $this->soap_client->fault = true;
                return false;
            }
            if (is_object($result)) $result = (array)$result;
            if (is_array($result)){
                foreach($result as $index => $one){
                    if (is_object($one)) $result[$index] = (array)$one;
                }
            }
            $this->soap_client->fault = false;
        }
        return $result;
    }

    function getError()
    {
         $error = $this->error;
         $this->error = "";
         if ($error != "")
             return $error;
        else
            return false;
    }
}
?>
