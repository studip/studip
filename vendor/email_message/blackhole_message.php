<?php
/*
 * blackhole_message.php
 *
 * no escape :)
 * inject into StudipMail to send all mail to /dev/null
 */


class blackhole_message_class extends email_message_class
{

    /* (non-PHPdoc)
     * @see vendor/email_message/email_message_class::SendMail()
     */
    function SendMail($to,$subject,$body,$headers,$return_path)
    {
    }

}
?>