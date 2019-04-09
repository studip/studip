<?php
/**
 * StudipMail.class.php
 *
 * class for constructing and sending emails in Stud.IP
 *
 *
 * @author  André Noack <noack@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
 * @version 1
 * @license GPL2 or any later version
 * @copyright 2009 authors
 */
class StudipMail
{
    /**
     * @var email_message_class
     * @static
     */
    private static $transporter;

    /**
     * @var string
     */
    private $body_text;
    /**
     * @var string
     */
    private $body_html;
    /**
     * @var string
     */
    private $subject;
    /**
     * Array of all attachments, name ist key
     * @var array
     */
    private $attachments = [];
    /**
     * @var string
     */
    private $sender;
    /**
     * Array of all recipients, mail is key
     * @var array
     */
    private $recipients = [];
    /**
     * @var string
     */
    private $reply_to;

    /**
     * Sets the default transporter used in StudipMail::send()
     * @param email_message_class $transporter
     * @return void
     */
    public static function setDefaultTransporter(email_message_class $transporter)
    {
        self::$transporter = $transporter;
    }

    /**
     * gets the default transporter used in StudipMail::send()
     *
     * @return email_message_class
     */
    public static function getDefaultTransporter()
    {
        return self::$transporter;
    }

    /**
     * convenience method for sending a qick, text based email message
     *
     * @param string $recipient
     * @param string $subject
     * @param string $text      Plain text version of the message (required).
     * @param string $html      HTML version of the message (optional).
     * @return bool
     */
    public static function sendMessage($recipient, $subject, $text, $html = null)
    {
        $mail = new StudipMail();
        return $mail->setSubject($subject)
                    ->addRecipient($recipient)
                    ->setBodyText($text)
                    ->setBodyHtml($html)
                    ->send();
    }

    /**
     * convenience method for sending a qick, text based email message
     * to the configured abuse adress
     *
     * @param string $subject
     * @param string $text
     * @return bool
     */
    public static function sendAbuseMessage($subject, $text)
    {
        $mail = new StudipMail();
        $abuse = $mail->getReplyToEmail();
        return $mail->setSubject($subject)
                    ->setReplyToEmail('')
                    ->addRecipient($abuse)
                    ->setBodyText($text)
                    ->send();
    }

    /**
     * sets some default values for sender and reply to from
     * configuration settings. The return path is always set to MAIL_ABUSE
     *
     */
    public function __construct($data = null)
    {
        $mail_localhost = $GLOBALS['MAIL_LOCALHOST'] ?: $_SERVER['SERVER_NAME'];
        $this->setSenderEmail($GLOBALS['MAIL_ENV_FROM'] ?: "wwwrun@{$mail_localhost}");
        $this->setSenderName($GLOBALS['MAIL_FROM'] ?: 'Stud.IP - ' . Config::get()->UNI_NAME_CLEAN);
        $this->setReplyToEmail($GLOBALS['MAIL_ABUSE'] ?: "abuse@{$mail_localhost}");

        if ($data) {
            $this->setData($data);
        }
    }

    /**
     * @param string $mail
     * @return StudipMail provides fluent interface
     */
    public function setSenderEmail($mail)
    {
        $this->sender['mail'] = $mail;
        return $this;
    }

    /**
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->sender['mail'];
    }

    /**
     * @param string $name
     * @return StudipMail provides fluent interface
     */
    public function setSenderName($name)
    {
        $this->sender['name'] = $name;
        return $this;
    }

    /**
     * @return unknown_type
     */
    public function getSenderName()
    {
        return $this->sender['name'];
    }

    /**
     * @param $mail
     * @return StudipMail provides fluent interface
     */
    public function setReplyToEmail($mail)
    {
        $this->reply_to['mail'] = $mail;
        return $this;
    }

    /**
     * @return unknown_type
     */
    public function getReplyToEmail()
    {
        return $this->reply_to['mail'];
    }

    /**
     * @param $name
     * @return StudipMail provides fluent interface
     */
    public function setReplyToName($name)
    {
        $this->reply_to['name'] = $name;
        return $this;
    }

    /**
     * @return unknown_type
     */
    public function getReplyToName()
    {
        return $this->reply_to['name'];
    }

    /**
     * @param $subject
     * @return StudipMail provides fluent interface
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return unknown_type
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param $mail
     * @param $name
     * @param $type
     * @return StudipMail provides fluent interface
     */
    public function addRecipient($mail, $name = '', $type = 'To')
    {
        $type = ucfirst($type);
        $type = in_array($type, ['To', 'Cc', 'Bcc']) ? $type : 'To';
        if (!isset($this->recipients[$mail]) || $this->recipients[$mail]['type'] !== 'To') {
            $this->recipients[$mail] = compact('mail', 'name', 'type');
        }
        return $this;
    }

    /**
     * @param $mail
     * @return StudipMail provides fluent interface
     */
    public function removeRecipient($mail)
    {
        unset($this->recipients[$mail]);
        return $this;
    }

    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param $mail
     * @return unknown_type
     */
    public function isRecipient($mail)
    {
        return isset($this->recipients[$mail]);
    }

    /**
     * @param $file_name
     * @param $name
     * @param $type
     * @param $disposition
     * @return StudipMail provides fluent interface
     */
    public function addFileAttachment($file_name, $name = '', $type = 'automatic/name', $disposition = 'attachment')
    {
        $name = $name ?: basename($file_name);
        $this->attachments[$name] = compact('file_name', 'name', 'type', 'disposition');
        return $this;
    }

    /**
     * @param $data
     * @param $name
     * @param $type
     * @param $disposition
     * @return StudipMail provides fluent interface
     */
    public function addDataAttachment($data, $name, $type = 'automatic/name', $disposition = 'attachment')
    {
        $this->attachments[$name] = compact('data', 'name', 'type', 'disposition');
        return $this;
    }

    /**
     * @param FileRef $file_ref The FileRef object of a file that shall be added to a mail
     * @return StudipMail provides fluent interface
     */
    public function addStudipAttachment(FileRef $file_ref)
    {
        if (!$file_ref->isNew()) {
            $this->addFileAttachment(
                $file_ref->file->getPath(),
                $file_ref->name
            );
        }
        return $this;
    }

    /**
     * @param $name
     * @return StudipMail provides fluent interface
     */
    public function removeAttachment($name)
    {
        unset($this->attachments[$name]);
        return $this;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param $name
     * @return unknown_type
     */
    public function isAttachment($name)
    {
        return isset($this->attachments[$name]);
    }

    /**
     * @param $body
     * @return StudipMail provides fluent interface
     */
    public function setBodyText($body)
    {
        $this->body_text = $body;
        return $this;
    }

    /**
     * @return unknown_type
     */
    public function getBodyText()
    {
        return $this->body_text;
    }

    /**
     * @param $body
     * @return StudipMail provides fluent interface
     */
    public function setBodyHtml($body)
    {
        $this->body_html = $body;
        return $this;
    }

    /**
     * @return unknown_type
     */
    public function getBodyHtml()
    {
        return $this->body_html;
    }

    /**
     * quotes the given string if it contains any characters
     * reserved for special interpretation in RFC 2822.
     */
    protected static function quoteString($string)
    {
        // list of reserved characters in RFC 2822
        if (strcspn($string, '()<>[]:;@\\,.') < mb_strlen($string)) {
            $string = '"' . addcslashes($string, "\r\"\\") . '"';
        }
        return $string;
    }

    /**
     * send the mail using the given transporter object, or the
     * set default transporter
     *
     * @param email_message_class $transporter
     * @return bool
     */
    public function send(email_message_class $transporter = null)
    {
        if ($transporter === null) {
            $transporter = self::$transporter;
        }
        if ($transporter === null) {
            throw new Exception('no mail transport defined');
        }
        $transporter->ResetMessage();
        $transporter->SetEncodedEmailHeader('From', $this->getSenderEmail(), self::quoteString($this->getSenderName()));
        if($this->getReplyToEmail()){
            $transporter->SetEncodedEmailHeader('Reply-To', $this->getReplyToEmail(), self::quoteString($this->getReplyToName()));
        }
        foreach($this->getRecipients() as $recipient) {
            $recipients_by_type[$recipient['type']][$recipient['mail']] = self::quoteString($recipient['name']);
        }
        foreach($recipients_by_type as $type => $recipients){
            $transporter->SetMultipleEncodedEmailHeader($type, $recipients);
        }
        $transporter->SetEncodedHeader('Subject', $this->getSubject());
        if($this->getBodyHtml()){
            $html_part = '';
            $transporter->CreateQuotedPrintableHTMLPart($this->getBodyHtml(), "", $html_part);
            $text_part = '';
            $text_message = $this->getBodyText();
            if(!$text_message){
                $text_message = _('Diese Nachricht ist im HTML-Format verfasst. Sie benötigen eine E-Mail-Anwendung, die das HTML-Format anzeigen kann.');
            }
            $transporter->CreateQuotedPrintableTextPart($transporter->WrapText($text_message), "", $text_part);
            $transporter->AddAlternativeMultipart($part = [$text_part, $html_part]);
        } else {
            $transporter->AddQuotedPrintableTextPart($this->getBodyText());
        }
        foreach($this->getAttachments() as $attachment){
            $transporter->addFilePart($part = [
                'FileName'     => $attachment['file_name'],
                'Data'         => $attachment['data'],
                'Name'         => $attachment['name'],
                'Content-Type' => $attachment['type'],
                'Disposition'  => $attachment['disposition'],
            ]);
        }
        $error = $transporter->Send();
        if (mb_strlen($error) === 0) {
            return true;
        } else {
            Log::error(get_class($transporter) . '::Send() - ' . $error);
            return false;
        }
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    public function setData($data)
    {
        foreach ($data as $name => $value) {
            $this->$name = $value;
        }
    }
}
