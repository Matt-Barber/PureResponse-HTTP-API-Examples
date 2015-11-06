<?php
/**
 * An example class for making optin requests to the pure platform
 * PHP Version 5.6+
 * @category API
 * @author Matt Barber <mfmbarber@gmail.com>
**/

class Pure360Http
{
    const LIST_URL = 'http://response.pure360.com/interface/list.php';
    /**
     * Performs a signup to a list
     *
     * @param string $accName     The name of the profile
     * @param string $listName    The name of the list in the profile
     * @param string $recipient   Contact Email / Mobile
     * @param array  $customData  Assoc_array of headers & values for the list
     * @param string $doubleOptin A boolean - passively opt recipient if 'TRUE'
     *
     * @return string
     **/
    public static function signUp(
        $accName,
        $listName,
        $recipient,
        $customData=[],
        $doubleOptin='FALSE'
    ) {
        $params = [
                'accName' => $accName,
                'listName' => $listName,
                'doubleOptin' => $doubleOptin,
                'successUrl' => 'NO-REDIRECT',
                'errorUrl' => 'NO-REDIRECT'
            ];
        $params = array_merge($params, $customData);
        if (SELF::_isEmail($recipient)) {
            $params['email'] = $recipient;
        } else {
            $parms['mobile'] = $recipient;
        }
        return SELF::_webRequest($params, self::LIST_URL);
    }
    /**
     * Opts a recipient out of a whole profile
     *
     * @param string $accName   The name of the profile
     * @param string $recipient Contact Email / Mobile
     *
     * @return string
    **/
    public static function optOut($accName, $recipient)
    {
        $params = [
                'accName' => $accName,
                'mode' => 'OPTOUT'
            ];
        if (SELF::_isEmail($recipient)) {
            $params['email'] = $recipient;
        } else {
            $parms['mobile'] = $recipient;
        }
        return SELF::_webRequest($params, self::LIST_URL);
    }

    /**
     * Checks if the recipient is an email or mobile
     *
     * @param string $recipient Contact Email / Mobile
     *
     * @return boolean
    **/
    private static function _isEmail($recipient)
    {
        if (is_numeric($recipient)) {
            return false;
        } else if (!filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            return true;
        } else {
            throw new Exception("Not an email address or mobile number : ${recipient}");
        }
    }
    /**
     * This sends the web request using curl to the endpoint
     * with the query parameters
     *
     * @param array  $payload Query parameters
     * @param string $url     End point
     *
     * @return string
    **/
    private static function _webRequest($payload, $url)
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload
            ]
        );
        $response = CURL_EXEC($curl);
        if ($response === false) {
            throw new exception(CURL_ERROR($curl), CURL_ERRNO($curl));
        }
        curl_close($curl);
        return $response;

    }
}
