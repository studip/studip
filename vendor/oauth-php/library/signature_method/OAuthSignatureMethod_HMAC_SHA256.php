<?php

/**
 * OAuth signature implementation using HMAC-SHA256
 *
 * @author André Noack <noack@data-quest.de>
 *
 * The MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


require_once dirname(__FILE__).'/OAuthSignatureMethod.class.php';


class OAuthSignatureMethod_HMAC_SHA256 extends OAuthSignatureMethod
{
	public function name ()
	{
		return 'HMAC-SHA256';
	}


	/**
	 * Calculate the signature using HMAC-SHA1
	 * This function is copyright Andy Smith, 2007.
	 *
	 * @param OAuthRequest request
	 * @param string base_string
	 * @param string consumer_secret
	 * @param string token_secret
	 * @return string
	 */
	function signature ( $request, $base_string, $consumer_secret, $token_secret )
	{
		$key = $request->urlencode($consumer_secret).'&'.$request->urlencode($token_secret);
		$signature = base64_encode(hash_hmac("sha256", $base_string, $key, true));
        return $request->urlencode($signature);
	}


	/**
	 * Check if the request signature corresponds to the one calculated for the request.
	 *
	 * @param OAuthRequest request
	 * @param string base_string	data to be signed, usually the base string, can be a request body
	 * @param string consumer_secret
	 * @param string token_secret
	 * @param string signature		from the request, still urlencoded
	 * @return string
	 */
	public function verify ( $request, $base_string, $consumer_secret, $token_secret, $signature )
	{
		$a = $request->urldecode($signature);
		$b = $request->urldecode($this->signature($request, $base_string, $consumer_secret, $token_secret));

		// We have to compare the decoded values
		$valA  = base64_decode($a);
		$valB  = base64_decode($b);

		// Crude binary comparison
		return rawurlencode($valA) == rawurlencode($valB);
	}
}
