<?php
/*
 * An example class for making optin requests to the pure platform
 * PHP Version 5.6+
 * @category Data Gathering
 * @author Matt Barber
 * @created_at 15/09/2015
 */

  class Pure360Http {
      const LIST_URL = 'http://response.pure360.com/interface/list.php';
    /**
     *  Performs a signup to a list
     *  @param accName      string     A string representing the profile name
     *  @param listName     string     A string representing the list name
     *  @param recipient    string     A string representing the contact detail
     *  @param customData   array      An associative array of headers and
     *                                 values for the list
     *  @param doubleOptin  string     A string (boolean) with passively opt
     *                                 recipient in if set to TRUE (i.e. send a
     *                                 double optin email)
     **/
    public function signup (
        $accName,
        $listName,
        $recipient,
        $customData=[],
        $doubleOptin='FALSE'
    )
    {
        $params = [
                'accName' => $accName,
                'listName' => $listName,
                'doubleOptin' => $doubleOptin,
                'successUrl' => 'NO-REDIRECT',
                'errorUrl' => 'NO-REDIRECT'
            ];
        $params = array_merge($params, $customData);
        if ($this->is_email($recipient))
        {
            $params['email'] = $recipient;
        }
        else
        {
            $parms['mobile'] = $recipient;
        }
        return $this->web_request($params, self::LIST_URL);
    }
    /**
     * Opts a recipient out of a whole profile
     * @param accName   string  a string representing the profile name
     * @param recipient    string     A string representing the contact detail
    **/
    public function optout ($accName, $recipient)
    {
        $params = [
                'accName' => $accName,
                'mode' => 'OPTOUT'
            ];
        if ($this->is_email($recipient))
        {
            $params['email'] = $recipient;
        }
        else
        {
            $parms['mobile'] = $recipient;
        }
        return $this->web_request($params, self::LIST_URL);
    }

    /**
     * Checks if the recipient is an email or mobile
     * @param recipient    string     A string representing the contact detail
    **/
    private function is_email ($recipient)
    {
        if(is_numeric($recipient))
        {
            return FALSE;
        }
        else if(preg_match('/[^@]+@[^@]+\.[^@]+/', $recipient) === FALSE)
        {
            return TRUE;
        }
        else
        {
            throw new Exception('Not an email address or mobile number');
        }
    }
    /**
     * This sends the web request using curl to the endpoint
     * with the query parameters
     * @param payload       array   Query parameters
     * @param url           string  End point
    **/
    private function webRequest($payload, $url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => FALSE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $payload
        ]);
        $response = CURL_EXEC($curl);
        if ($response === FALSE)
            throw new exception(CURL_ERROR($curl), CURL_ERRNO($curl));
        curl_close($curl);
        return $response;

    }
  }
?>
