<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
class StudipSoapClient
{
    var $soap_client;
    var $error;

    function __construct($path)
    {
        require_once("vendor/nusoap/nusoap.php");

        $this->soap_client = new soap_client($path, true);
        $this->soap_client->soap_defencoding = 'UTF-8';
        $this->soap_client->decode_utf8 = false;
        $this->soap_client->setDebugLevel(0);

        $err = $this->soap_client->getError();
        if ($err)
            $this->error = "<b>Soap Constructor Error</b><br>" . $err . "<br><br>";
    }

    function _call($method, $params)
    {
        $this->faultstring = "";
        $result = $this->soap_client->call($method, $params);

        if ($this->soap_client->fault)
        {
            $this->faultstring = $result["faultstring"];
            if (!in_array(mb_strtolower($this->faultstring), ["session not valid","session invalid", "session idled"]))
                $this->error .= "<b>" . sprintf(_("SOAP-Fehler, Funktion \"%s\":"), $method) . "</b> " . $result["faultstring"] . " (" . $result["faultcode"] . ")<br>"; //.implode($params,"-");
        }
        else
        {
            $err = $this->soap_client->getError();
            if ($err)
                $this->error .= "<b>" . sprintf(_("SOAP-Fehler, Funktion \"%s\":"), $method) . "</b> " . $err . "<br>"; //.implode($params,"-") . htmlspecialchars($this->soap_client->response, ENT_QUOTES);
            else
                return $result;
        }
        error_log($this->error);
        return false;
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
