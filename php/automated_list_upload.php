<?php
/**
 * An example class for creating, replacing, and appending contact lists
 * PHP Version 5.6+
 * @category API
 * @author Matt Barber <mfmbarber@gmail.com>
**/

class Pure360Http
{
    const LIST_UPLOAD_META = 'http://response.pure360.com/interface/list_upload_meta.php';
    const LIST_UPLOAD_DATA = 'http://response.pure360.com/interface/list_upload_data.php';

    /**
     * Creates a new contact list in the given profile (secured by the token)
     *
     * @param string $profileName  The name of the profile
     * @param string $token        Security token securing the list uploads
     * @param string $responseType Type of response for the upload notification
     * @param string $responseUri  Where to send the notification
     * @param string $listName     Name of the list to create in the platform
     * @param string $filename     File path and name of the file to upload
     *
     * @return string
    **/
    public static function createList(
        $profileName,
        $token,
        $responseType,
        $responseUri,
        $listName,
        $filename
    ) {
        $params = [
            'profileName' => $profileName,
            'token' => $token,
            'responseType' => $responseType,
            'responseUri' => $responseUri,
            'listName' => $listName,
            'transactionType' => 'CREATE'
        ];
        return SELF::_processList($params, $filename);
    }
    /**
     * Replaces a contact list in the given profile (secured by the token)
     *
     * @param string $profileName  The name of the profile
     * @param string $token        Security token securing the list uploads
     * @param string $responseType Type of response for the upload notification
     * @param string $responseUri  Where to send the notification
     * @param string $listName     Name of the list to create in the platform
     * @param string $filename     File path and name of the file to upload
     *
     * @return string
    **/
    public static function replaceList(
        $profileName,
        $token,
        $responseType,
        $responseUri,
        $listName,
        $filename
    ) {
        $params = [
            'profileName' => $profileName,
            'token' => $token,
            'responseType' => $responseType,
            'responseUri' => $responseUri,
            'listName' => $listName,
            'transactionType' => 'REPLACE'
        ];
        return SELF::_processList($params, $filename);
    }
        /**
     * Appends (updates / inserts to) a contact list in the given profile
     *
     * @param string $profileName  The name of the profile
     * @param string $token        Security token securing the uploads
     * @param string $responseType The type of response for the notification
     * @param string $responseUri  Where to send the notification
     * @param string $listName     Name of the list to create in the platform
     * @param string $filename     File path and name of the file to upload
     *
     * @return string
    **/
    public static function appendList(
        $profileName,
        $token,
        $responseType,
        $responseUri,
        $listName,
        $filename
    ) {
        $params = [
            'profileName' => $profileName,
            'token' => $token,
            'responseType' => $responseType,
            'responseUri' => $responseUri,
            'listName' => $listName,
            'transactionType' => 'APPEND'
        ];
        return SELF::_processList($params, $filename);
    }
    /**
     * Fed by the public classes this uploads the contact list
     *
     * @param array  $params   The query parameters
     * @param string $filename filepath and name of the file to upload
     *
     * @return string
    **/
    private static function _processList($params, $filename)
    {
        $params = array_merge($params, $this->getHeaderIndexes($filename));
        $transactionId = trim(
            array_pop(
                explode(
                    ':', $this->webRequest($params, self::LIST_UPLOAD_META)
                )
            )
        );
        $params = [
            'transactionId' => $transactionId,
            'profileName' => $params['profileName']
        ];
        return SELF::_webRequest($params, self::LIST_UPLOAD_DATA, $filename);
    }
    /**
     * Converts the first line of a CSV into the required meta data
     *
     * @param string $filename filepath and name of the file to upload
     *
     * @return array
    **/
    private static function _getHeaderIndexes($filename)
    {
        $meta_data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            $line = array_flip(fgetcsv($handle, 1000, ','));
            foreach ($line as $key => $value) {
                $has_email = stristr($key, 'email');
                $email_set = isset($meta_data['emailCol']);
                if ($has_email && !$email_set) {
                    $meta_data['emailCol'] = $value;
                } else {
                    $meta_data["COL_{$key}"] = $value;
                }
            }
        }
        fclose($handle);
        return $meta_data;
    }
    /**
     * This sends the web request using curl and CURLFile to the endpoint
     *
     * @param array  $payload     Query parameters
     * @param string $url         End point
     * @param string $upload_file File path and name of the file to upload
     *
     * @return string
    **/
    private static function _webRequest($payload, $url, $upload_file=null)
    {
        $curl = curl_init();
        if ($upload_file !== null) {
            // Converts the filename and path to a CURLFile
            if (stristr($upload_file, './') !== false) {
                $upload_file = realpath('./') . '/' . basename($upload_file);
            }
            $payload['file'] = new CURLFile("{$upload_file}");
        }
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
