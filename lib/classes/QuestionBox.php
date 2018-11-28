<?php
/**
 * A question box is used to display a confirmation dialog for a specific
 * action, quite similar to the message boxes.
 *
 * A question box consists at least of a question and some approval parameters
 * that will be sent when the question is approved. You may pass additional
 * parameters that are sent when the question is disapproved and both urls for
 * approving and disapproving may be set.
 *
 * @author  Jan-Hendrik Willms
 * @license GPL2 or any later version
 * @since   Stud.IP 4.2
 */
class QuestionBox implements LayoutMessage
{
    /**
     * Creates a question object that should be confirmed.
     *
     * If you want to pass additional parameters, include them in the url. They
     * will be extract from the url.
     *
     * @param string $question    The question that should be confirmed
     * @param string $accept_url  URL to send the acceptance request to
     * @param string $decline_url URL to send the declination request to
     * @return QuestionBox instance to allow chaining
     */
    public static function create($question, $accept_url = '', $decline_url = '')
    {
        return new static(formatReady($question), $accept_url, $decline_url);
    }

    /**
     * Creates a question object that should be confirmed. The question may
     * contain HTML.
     *
     * If you want to pass additional parameters, include them in the url. They
     * will be extract from the url.
     *
     * @param string $question    The question that should be confirmed
     * @param string $accept_url  URL to send the acceptance request to
     * @param string $decline_url URL to send the declination request to
     * @return QuestionBox instance to allow chaining
     */
    public static function createHTML($question, $accept_url = '', $decline_url = '')
    {
        return new static($question, $accept_url, $decline_url);
    }

    protected $question;
    protected $accept_url;
    protected $accept_parameters = [];
    protected $decline_url;
    protected $decline_parameters = [];
    protected $include_ticket = false;

    /**
     * Constructs the object. Protected to enforce the use of our static helper
     * functions.
     *
     * @param string $question The question that should be confirmed
     */
    protected function __construct($question, $accept_url, $decline_url)
    {
        $this->question = $question;
        $this->setAcceptURL($accept_url);
        $this->setDeclineURL($decline_url);
    }

    /**
     * Set the url the acceptance request is sent to.
     *
     * @param string $url
     * @param array  $parameters
     * @return QuestionBox instance to allow chaining
     */
    public function setAcceptURL($url, array $parameters = [])
    {
        $parameters = array_merge(
            $this->extractURLParameters($url),
            $parameters
        );

        $this->accept_url        = $url;
        $this->accept_parameters = $parameters;
        return $this;
    }

    /**
     * Set the url the declination url is sent to.
     *
     * @param string $url
     * @param array  $parameters
     * @return QuestionBox instance to allow chaining
     */
    public function setDeclineURL($url, array $parameters = [])
    {
        $parameters = array_merge(
            $this->extractURLParameters($url),
            $parameters
        );

        $this->decline_url        = $url;
        $this->decline_parameters = $parameters;
        return $this;
    }

    /**
     * Sets boths url for acceptance and declination to the same url.
     *
     * @param string $url
     * @return QuestionBox instance to allow chaining
     */
    public function setBaseURL($url)
    {
        $this->setAcceptURL($url);
        $this->setDeclineURL($url);
        return $this;
    }

    /**
     * Defines whether a stud.ip ticket should be included in the question.
     *
     * @param bool $name
     * @return QuestionBox instance to allow chaining
     */
    public function includeTicket($name = 'studip_ticket')
    {
        $this->include_ticket = $name;
        return $this;
    }

    /**
     * Renders the question box as html.
     *
     * @return string
     */
    public function __toString()
    {
        // Include fresh ticket
        if ($this->include_ticket) {
            $this->accept_parameters[$this->include_ticket] = get_ticket();
        }

        return $GLOBALS['template_factory']->render('shared/question-box', [
            'question' => $this->question,

            'accept_url'        => $this->accept_url,
            'accept_parameters' => $this->accept_parameters,

            'decline_url'        => $this->decline_url,
            'decline_parameters' => $this->decline_parameters,
        ]);
    }

    /**
     * Extracts parameters from a url.
     *
     * @param string $url
     * @return array
     */
    protected function extractURLParameters($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $parameters);
        return $parameters;
    }
}
