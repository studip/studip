<?php
/**
 * Self-documenting extension to the PHP-XMLRPC server
 *
 * @version $Id: docxmlrpcs.inc,v 1.9 2008/03/07 16:47:06 ggiunta Exp $
 * @author Gaetano Giunta
 * @copyright (c) 2005-2009 G. Giunta
 * @license code licensed under the BSD License: http://phpxmlrpc.sourceforge.net/license.txt
 *
 * @todo use some AJAX magic to implement xmlrpc calls to test/debug methods
 *       without feeding to user the raw xml
 * @todo add some i18n support
 * @todo add a sane way to have a set of hhtp headers to be sent along with every
 *       type of generated documentation (eg. content-type)
 **/

	// requires: xmlrpc.inc, xmlrpcs.inc (version 2.0RC3 or later)

	/**
	* Extends the base xmlrpc server with the capability to generate documentation
	* about the exposed xmlrpc methods.
	* It will take advantage of a new memeber in the dispatch map: signature_docs
	* it is expected to be an array with the same number of memebers as signature,
	* but containing a short description for every parameter.
	*/
	class documenting_xmlrpc_server extends xmlrpc_server
	{
		/// default format for generated documentation: either wsdl or html
		var $default_doctype = 'html';
		var $default_doclang = 'en';
		var $supported_langs = array('en');
		var $supported_doctypes = array('html', 'wsdl');
		/// relative path to the visual xmlrpc editing dialog
		var $editorpath = '';


		function checkAuth()
		{

		    $rules = WebserviceAccessRule::findByApiKey($_SERVER['PHP_AUTH_PW']);
		    if (!count($rules)) {
		        Header("WWW-Authenticate: Basic realm=\"Please enter valid api key as password\"");
                Header("HTTP/1.0 401 Unauthorized");
                die('Please enter valid api key as password');
            }
		}
		/**
		* Override xmlrpc_server service method:
		*   in case of GET requests show docs about implemented methods;
		*   in case of POST received by a form, we use the methodCall input value
		*   as if it had been sent with a tex/xml mimetype
		* @param string $data request data to be parsed, null by default
		* @param bool $return_payload when true the payload will be returned but not echoed to screen
		* @param string $doctype type of documentation to generate: html, wsdl, etc... If empty, use class default
		*/
		function service($data=null, $return_payload=false, $doctype='')
		{
			if($_SERVER['REQUEST_METHOD'] != 'POST')
			{
                $this->checkAuth();
			    if ($doctype == '' || !in_array($doctype, $this->supported_doctypes))
				{
					$doctype = $this->default_doctype;
				}
				// language decoding
				if (isset($_GET['lang']) && in_array(strtolower($_GET['lang']), $this->supported_langs))
				{
					$lang = strtolower($_GET['lang']);
				}
				else
				{
					$lang = $this->default_doclang;
				}

				$docs = generateDocs($this, $doctype, $lang, $this->editorpath);
				if (!$return_payload)
				{
					print $docs;
				}
				return $docs;
			}
			else
			{
				// we break the xmlrpc spec here, and answer to POST requests
				// that have been sent via a standard html form, such as the
				// one that is part of self-generated docs
				if(isset($_SERVER['CONTENT_TYPE'])
					&& $_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded'
					&& isset($_POST['methodCall']))
				{
					$this->checkAuth();
				    if (get_magic_quotes_gpc())
					{
						$_POST['methodCall'] = stripslashes($_POST['methodCall']);
					}
					return parent::service($_POST['methodCall'], $return_payload);
				}
				else
				{
					return parent::service($data, $return_payload);
				}
			}
		}

	}

	/**
	* Generate the documentation about methods exposed by a given server.
	* Note that it will NOT html-escape the user provided documentantation (ie. risky).
	* @param xmlrpcserver $server
	* @param string $doctype type of documentation to generate: html (default), wsdl, etc...
	* @param string $lang language for docs
	* @return string
	*
	* @todo add support for i18n of generated user-readable docs (eg html)
	*/
	function generateDocs($server, $doctype='html', $lang='en', $editorpath='')
	{
		$payload = '';
		switch ($doctype)
		{
			case 'wsdl':
				break;
			case 'html':
				//$i18n = $GLOBALS['xmlrpcdoci18n'][$lang];
				$template = $GLOBALS['xmlrpcdocparts']['html'];
				// in case we have to send custom http headers, do it
				// removed from here, since we only return the payload now...
				//foreach ($template['httpheaders'] as $header)
				//	header($header);

				// method name decoding: is user seeking info about a single method?
				if (isset($_GET['methodName']))
				{
					$_GET['methodName'] = preg_replace('/[^a-zA-Z0-9_.:\/]/', '', $_GET['methodName']);
					$opts = array('lang' => $lang, 'title' => 'Method '.$_GET['methodName']);
					if ($editorpath != '')
					{
						$mstart = xmlrpc_smarty($template['xmlrpcmethodstart'], array('method' => $_GET['methodName']));
						$mend = xmlrpc_smarty($template['xmlrpcmethodend'], array());
						$opts['extras'] = xmlrpc_smarty($template['editorheaders'], array('baseurl' => $editorpath, 'methodcallstart' => $mstart, 'methodcallend' => $mend));
					}
					else
						$opts['extras'] = '';
					$payload .= xmlrpc_smarty($template['docheader'], $opts);
					if ($server->allow_system_funcs)
					{
						$methods = array_merge($server->dmap, $GLOBALS['_xmlrpcs_dmap']);
					}
					else
					{
						$methods = $server->dmap;
					}
					if (!array_key_exists($_GET['methodName'], $methods))
					{
						$payload .= xmlrpc_smarty($template['methodheader'], array('method' => $_GET['methodName'], 'desc' => ''));
						$payload .= xmlrpc_smarty($template['methodnotfound'], array('method' => $_GET['methodName']));
					}
					else
					{
						$payload .= xmlrpc_smarty($template['methodheader'], array('method' => $_GET['methodName'], 'desc' => @$methods[$_GET['methodName']]['docstring']));
						//$payload .= xmlrpc_smarty($template['methodfound']);
						for ($i = 0; $i < count($methods[$_GET['methodName']]['signature']); $i++)
						{
							$val = $methods[$_GET['methodName']]['signature'][$i];
							// NEW: signature_docs array, MIGHT be present - or not...
							$doc = @$methods[$_GET['methodName']]['signature_docs'][$i];
							if (!is_array($doc) || !count($doc))
							{
								$doc = array_fill(0, count($val), '');
							}
							$payload .= xmlrpc_smarty($template['sigheader'], array('signum' => $i+1));
							$out = array_shift($val);
							$outdoc = array_shift($doc);
							for ($j = 0; $j < count($val); $j++)
							{
								$payload .= xmlrpc_smarty($template['sigparam'], array('paramtype' => $val[$j], 'paramdesc' => @$doc[$j]));
							}
							$payload .= xmlrpc_smarty($template['sigfooter'], array('outtype' => $out, 'outdesc' => $outdoc, 'method' => $_GET['methodName']));
						}
						if ($editorpath)
						{
							$payload .= xmlrpc_smarty($template['methodfooter'], array('method' => $_GET['methodName'], 'extras' => xmlrpc_smarty($template['editorlink'], array())));
						}
						else
						{
							$payload .= xmlrpc_smarty($template['methodfooter'], array('method' => $_GET['methodName'], 'extras' => ''));
						}
					}
				}
				else
				{
					// complete api info
					$payload .= xmlrpc_smarty($template['docheader'], array('lang' => $lang, 'title' => 'API Index', 'extras' => ''));
					$payload .= xmlrpc_smarty($template['apiheader']);
					foreach($server->dmap as $key => $val)
					{
						$payload .= xmlrpc_smarty($template['apimethod'], array('method' => $key, 'desc' => @$val['docstring']));
					}
					if($server->allow_system_funcs)
					{
						foreach($GLOBALS['_xmlrpcs_dmap'] as $key => $val)
						{
							$payload .= xmlrpc_smarty($template['apimethod'], array('method' => $key, 'desc' => @$val['docstring']));
						}
					}
					$payload .= xmlrpc_smarty($template['apifooter']);
				}

				$payload .= xmlrpc_smarty($template['docfooter']);

		}
		return $payload;
	}

	/**
	* Dumb (dumb dumb) smarty-like template system
	* @param string $template the template text, using {$var} syntax for substitution
	* @param array $params array of variables to be substituted in template, based on array key
	*
	* @todo introduce support for multilanguage directly here
	* @todo introduce support for nested arrays, so we can coalesce templates
	*/
	function xmlrpc_smarty($template, $params=array())
	{
		foreach ($params as $key => $val)
		{
			$template = str_replace("{\$$key}", $val, $template);
		}
		return $template;
	}

	/**
	* Templates used for building docs
	* The charset is assumed to be UTF-8 for every generated html. Take care
	*/
	$GLOBALS['xmlrpcdocparts'] = array(
		'html' => array(

//'httpheaders' => array(),

'docheader' => '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang}" lang="{$lang}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Script-Type" content="text-javascript" />
<meta name="generator" content="'.$GLOBALS['xmlrpcName'].'" />
<style type="text/css" media="screen">
body {
    font-family: sans-serif;
    color: #000000;
    background-color: #ffffff;
}

h1 {
    font-family: serif;
    color: #000000;
    font-size: 150%;
    text-align: left;
}

h2 {
    font-family: serif;
    color: #000000;
    font-size: 120%;
    text-align: left;
    border-width: 1pt 0pt 0pt 0pt;
    border-style: solid;
    border-color: #8888dd;
}

h3 {
    font-family: serif;
    font-size: 110%
}

table tr td {
    vertical-align: top;
}

table tr th {
    vertical-align: top;
}

table.headerlinks {
    width: 100%;
    border-collapse: collapse;
}

table.headerlinks tr td {
    padding: 2pt;
    margin: 0pt;
    font-variant: small-caps;
    background-color: #ddddff;
    border-width: 0pt 0pt 1pt 0pt;
    border-style: solid;
    border-color: #8888dd;
}

table.headerlinks tr td.prevnext {
    text-align: right;
}

.footer {
    margin-top: 16pt;
    border-width: 1pt 0pt 0pt 0pt;
    border-style: solid;
    border-color: #8888dd;
    font-size: 70%;
    font-style: italic;
    text-align: right;
}

table.metadata {
    left: 0pt;
    margin: 8pt 0pt 0pt 0pt;
    border-collapse: collapse;
}

table.metadata tr td.key {
    font-weight: bold;
    padding: 0pt 4pt 0pt 0pt;
}

table.metadata tr td.value {
    padding: 0pt;
}

table.parameters {
    border: none;
    background-color: #ddddff;
    border-collapse: collapse;
    table-layout: fixed;
    table-layout: auto;
    width: 100%;
}

table.parameters tr th {
    color: #ffffff;
    background-color: #8888dd;
    border: solid 1px #8888dd;
    padding: 2pt;
    font-weight: bold;
}

table.parameters tr td {
    border: solid 1px #8888dd;
    padding: 2pt;
}

table.parameters tr td.value {
    font-family: monospace;
    font-size: 90%;
}

table.parameters tr td.name {
    font-family: monospace;
    font-size: 90%;
}

table.parameters tr td.required {
    /* font-variant: small-caps; */
}

table.inputparameters {
    border: none;
    background-color: #ddddff;
    border-collapse: collapse;
    table-layout: auto;
    width: 95%;
}

table.inputparameters tr th {
    color: #ffffff;
    background-color: #8888dd;
    border: solid 1px #8888dd;
    padding: 2pt;
    font-weight: bold;
}

table.inputparameters tr td {
    border: solid 1px #8888dd;
    padding: 2pt;
}

table.inputparameters tr td.value {
    font-family: monospace;
    font-size: 90%;
}

table.inputparameters tr td.name {
    font-family: monospace;
    font-size: 90%;
}

table.inputparameters tr td.required {
    /* font-variant: small-caps; */
}

table.outputparameters {
    border: none;
    background-color: #ddddff;
    border-collapse: collapse;
    table-layout: auto;
    width: 95%;
}

table.outputparameters tr th {
    color: #ffffff;
    background-color: #8888dd;
    border: solid 1px #8888dd;
    padding: 2pt;
    font-weight: bold;
}

table.outputparameters tr td {
    border: solid 1px #8888dd;
    padding: 2pt;
}

table.outputparameters tr td.value {
    font-family: monospace;
    font-size: 90%;
}

table.outputparameters tr td.name {
    font-family: monospace;
    font-size: 90%;
}

table.outputparameters tr td.required {
    /* font-variant: small-caps; */
}

table.resultcodes {
    border: none;
    background-color: #ddddff;
    border-collapse: collapse;
    table-layout: auto;
    width: 95%;
}

table.resultcodes tr th {
    color: #ffffff;
    background-color: #7777dd;
    border: solid 1px #8888dd;
    padding: 2pt;
    font-weight: bold;
}

table.resultcodes tr td {
    border: solid 1px #8888dd;
    padding: 2pt;
}

table.resultcodes tr td.value {
    font-family: monospace;
    font-size: 90%;
}

table.resultcodes tr.default td.value {
    font-style: italic;
}

table.resultcodes tr td.name {
    font-family: monospace;
    font-size: 90%;
}

table.resultcodes tr td.required {
    /* font-variant: small-caps; */
}

table.element_details {
    width: 100%;
    border: none;
    background-color: #ddddff;
    border-collapse: collapse;
    table-layout: auto;
    width: 95%;
}

table.element_details tr th {
    color: #ffffff;
    background-color: #8888dd;
    border: solid 1px #8888dd;
    padding: 2pt;
    font-weight: bold;
    text-align: left;
    width: 160px;
}

table.element_details tr td {
    border: solid 1px #8888dd;
    padding: 2pt;
}

table.example {
    border: none;
    border-collapse: collapse;
    table-layout: auto;
    width: 95%;
}

table.example tr th {
    background-color: #ddddff;
    text-align: left;
    border: solid 1px #8888dd;
    padding: 2pt;
}

table.example tr td {
    background-color: #ddddff;
    text-align: left;
    border: solid 1px #8888dd;
    padding: 2pt;
}

table.example tr td.header {
    text-align: left;
    border: none;
    padding: 2pt;
    background-color: #ffffff;
}

.xml {
    font-family: monospace;
    font-size: 90%;
    white-space: pre;
}

.xml .decl {
    font-weight: bold;
    color: #008800;
}

.xml .decl .elem .name {
    font-weight: bold;
    color: #008800;
}

.xml .elem .name {
    font-weight: bold;
    color: #000088;
}

.xml .pcdata {
    font-style: italic;
}

.xml .elem .attr .name {
    font-weight: normal;
    color: #000088;
}

.url {
    font-family: monospace;
    font-size: 90%;
    white-space: pre;
}

.url .functionparam .name {
    color: #880000;
    font-weight: bold
}

.url .functionparam .value {
    color: #880000;
    font-weight: bold
}

.url .param .name {
    color: #000088;
    font-weight: bold
}

.url .param .value {
    color: #008800;
    font-weight: bold
}

blockquote {
    margin-top: 10pt;
    margin-bottom: 10pt;
}

pre {
    font-family: monospace;
    font-size: 90%;
}

a:hover {
    background-color: #ddddff;
    text-decoration: underline;
}

a {
    color: #0000bb;
    text-decoration: none;
}

a:visited {
    color: #0000bb;
    text-decoration: none;
}

a img {
    border-style: none;
}

.disabled {
    color: #888888;
}

.active {
    color: #888888;
    font-weight: bold;
}

table.apilist {
    border: none;
    background-color: #ddddff;
    border-collapse: collapse;
    table-layout: fixed;
    table-layout: auto;
    width: 100%;
}

table.apilist tr th {
    color: #ffffff;
    background-color: #8888dd;
    border: solid 1px #8888dd;
    padding: 2pt;
    font-weight: bold;
    text-align: left;
}

table.apilist tr td {
    border: solid 1px #8888dd;
    padding: 2pt;
}

table.functionlist {
    border: none;
    background-color: #ddddff;
    border-collapse: collapse;
    table-layout: fixed;
    table-layout: auto;
    width: 100%;
}

table.functionlist tr th {
    color: #ffffff;
    background-color: #8888dd;
    border: solid 1px #8888dd;
    padding: 2pt;
    font-weight: bold;
    text-align: left;
}

table.functionlist tr td {
    border: solid 1px #8888dd;
    padding: 2pt;
}

table.typelist {
    border: none;
    background-color: #ddddff;
    border-collapse: collapse;
    table-layout: fixed;
    table-layout: auto;
    width: 100%;
}

table.typelist tr th {
    color: #ffffff;
    background-color: #8888dd;
    border: solid 1px #8888dd;
    padding: 2pt;
    font-weight: bold;
    text-align: left;
}

table.typelist tr td {
    border: solid 1px #8888dd;
    padding: 2pt;
}

input {
    border: 1px #8888dd solid;
    font-family: sans-serif;
    font-size: 9pt;
    background-color: #ffffff;
}

input.required {
    border: 2px #8888dd solid;
}

input[name=\'submit\'] {
    margin-top: 5px;
    text-align: center;
    font-weight: bold;
    cursor: hand;
    background-color: #eeeeff;
}

select {
    border: 1px #8888dd solid;
    font-family: sans-serif;
    font-size: 9pt;
    background-color: #ffffff;
}

select.required {
    border: 2px #8888dd solid;
}

td.status {
    font-variant: small-caps;
}

.broken_freeze {
    color: #ff4444;
    font-weight: bold;
}

div.broken_freeze {
    border: dashed 1px #ff4444;
    margin-top: 1em;
    margin-bottom: 1.5em;
    padding: 1em;
}
</style>
{$extras}
<title>{$title}</title>
</head>
<body>',

'docfooter' => '
<div class="footer">Generated using PHP-XMLRPC '.$GLOBALS['xmlrpcVersion'].'</div>
</body></html>',

'apiheader' => '
<h1>API index</h1>
<p>This server defines the following API specification:</p>
<table class="apilist">
<tr><th>Method</th><th>Description</th></tr>',

'apimethod' => '
<tr><td><a href="?methodName={$method}">{$method}</a></td><td>{$desc}</td></tr>',

'apifooter' => '
</table>',

'methodheader' => '
<h1>Method <em>{$method}</em></h1>
<div>{$desc}</div>',

'methodnotfound' => '
<h3>The method {$method} is not part of the API of this server</h3>
',

'sigheader' => '
<h2>Signature {$signum}</h2>
<blockquote>
<h3>Input parameters</h3>
<table class="inputparameters">
<tr><th>Type</th><th>Description</th></tr>',

'sigparam' => '
<tr><td>{$paramtype}</td><td>{$paramdesc}</td></tr>',

'sigfooter' => '
</table>
<h3>Output parameter</h3>
<table class="inputparameters">
<tr><th>Type</th><th>Description</th></tr>
<tr><td>{$outtype}</td><td>{$outdesc}</td></tr>
</table>
</blockquote>',

'methodfooter' => '
<h2>Test method call</h2>
<p>Complete by hand the form below inserting the needed parameters to call this method.<br/>
For a string param use e.g. <pre>&lt;param&gt;&lt;value&gt;&lt;string&gt;Hello&lt;/string&gt;&lt;/value&gt;&lt;/param&gt;</pre></p>
<form action="" method="post"><p>
<textarea id="methodCall" name="methodCall" rows="5" cols="80">
&lt;methodCall&gt;&lt;methodName&gt;{$method}&lt;/methodName&gt;
&lt;params&gt;
&lt;/params&gt;
&lt;/methodCall&gt;
</textarea><br/>
{$extras}
<input type="submit" value="Test"/>
</p></form>',

'editorheaders' => '<script type="text/javascript" src="{$baseurl}xmlrpc_lib.js"></script>
<!--<script type="text/javascript" src="{$baseurl}jsonrpc_lib.js"></script>-->
<script type="text/javascript">
<!--
function runeditor()
{
  //var url = "{$baseurl}visualeditor.php?params={$param_payload}";
  var url = "{$baseurl}visualeditor.php";
  //if (document.frmaction.wstype.value == "1")
  //  url += "&type=jsonrpc";
  var wnd = window.open(url, "_blank", "width=750, height=400, location=0, resizable=1, menubar=0, scrollbars=1");
}
// if javascript version of the lib is found, allow it to send us params
function buildparams(base64data)
{
  if (typeof base64_decode == "function")
  {
    if (base64data == "0") // workaround for bug in base64_encode...
      document.getElementById("methodCall").value = "{$methodcallstart}{$methodcallend}";
    else
      document.getElementById("methodCall").value = "{$methodcallstart}"+base64_decode(base64data)+"{$methodcallend}";
  }
}
//-->
</script>
',

'editorlink' => '<input type="submit" value="Edit" onclick="runeditor(); return false;"/>',

'xmlrpcmethodstart' => '<methodCall><methodName>{$method}</methodName>\n<params>\n',

'xmlrpcmethodend' => '</params>\n</methodCall>',
		),

		'wsdl' => array(
		)
	);

/*
	/// internationalization of docs templates
	$GLOBALS['xmlrpcdoci18n'] = array(
		'en' => array (
			'apiindex' => 'API Index'
		)
	);
*/
?>
