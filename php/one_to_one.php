<?php
/*
 * An example class for sending a one to one message
 * PHP Version 5.6+
 * @category Data Gathering
 * @author Matt Barber
 * @created_at 15/09/2015
 */

  class Pure360Http {
      const ONE_TO_ONE_URL = 'http://response.pure360.com/interface/common/one2OneCreate.php';
    /**
     *  Sends a one to one message
     * @param   userName        string  Represents an API system username (ending *.sys)
     * @param   password        string  Represents an API system password
     * @param   contentType     string  Represents either EMAIL or SMS
     * @param   recipient       string  The contact detail to send to
     * @param   message_params  array   An associative array detailing the message
     *  if message_messageName provided as a key then use an existing message
     *  otherwise for email this must contain:
     *      message_bodyPlain       The plain text version of the email
     *      message_bodyHtml        The html content of the email
     *      message_subject         The subject line to use for the email
     *      message_trackHtmlInd    Y or N tracks html clicks / opens
     *      message_trackPlainInd   Y or N tracks plain clicks / opens
     *  for sms this must contain:
     *      message_bodySms         The content of the sms
     *  message_params can also contain custom data for the message perosnalisation
     *  customData  An array of key value pairs
     * @param   deliveryDtTm    string  Represents the delivery datetime as dd/mm/yyyy hh:mm:ss
     * @param   json            string  Boolean representing whether to return a json structure
     **/
    public function send (
        $userName,
        $password,
        $contentType,
        $recipient,
        $message_params,
        $deliveryDtTm=null,
        $json='true'
    )
    {
        if (!in_array($contentType, ['EMAIL', 'SMS']))
        {
            throw new Exception('contentType must be either EMAIL or SMS');
        }
        else if($contentType === 'SMS' && $this->is_email($recipient))
        {
            throw new Exception('Cannot send SMS to Email Address');
        }
        else if($contentType === 'EMAIL' && !$this->is_email($recipient))
        {
            throw new Exception('Cannot send EMAIL to Mobile Number');
        }
        $deliveryDtTm = (isset($deliveryDtTm)) ? $deliveryDtTm : date('d/m/Y H:i:s');
        $params = [
            'userName' => $userName,
            'password' => $password,
            'message_contentType' => $contentType,
            'toAddress' => $recipient,
            'deliveryDtTm' => $deliveryDtTm,
            'json' => $json
        ];
        if(!isset($message_params['message_messageName']))
        {
            if($params['message_contentType'] === 'EMAIL')
            {
                $keys = [
                    'message_bodyPlain',
                    'message_bodyHtml',
                    'message_subject',
                    'message_trackHtmlInd',
                    'message_trackPlainInd'
                ];
                if(count($keys) !== count(
                    array_intersect($keys, $message_params))
                {
                    throw new Exception(
                        'If not supplying a message name ensure you provide the required keys'
                    );
                }
            }
            else if(!isset($message_params['message_bodySms']))
            {
                throw new Exception(
                    'If not supplying a message name ensure you provide the required keys'
                );

            }
        }
        $params = array_merge($params, $message_params);
        return $this->web_request($params, SELF::ONE_TO_ONE_URL);
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
