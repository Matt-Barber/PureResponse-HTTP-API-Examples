<?php
// An example class for creating, replacing, and appending contact lists
// PHP 5.5
// category Data Gathering
// author Matt Barber
// created_at 14/09/2015

    class Pure360Http
    {
        const LIST_UPLOAD_META = 'http://response.pure360.com/interface/list_upload_meta.php';
        const LIST_UPLOAD_DATA = 'http://response.pure360.com/interface/list_upload_data.php';

        public function __construct()
        {
        }
        /**
         * Creates a new contact list in the given profile (secured by the token)
         * @param profileName   string      The name of the profile to create the list in
         * @param token         string      Security token securing the list uploads for the profile
         * @param responseType  string      The type of response for the upload notification
         * @param responseUri   string      Where to send the notification
         * @param listName      string      The name of the list to create in the platform
         * @param filename      string      filepath and name of the file to upload
        **/
        public function createList(
            $profileName,
            $token,
            $responseType,
            $responseUri,
            $listName,
            $filename
        )
        {
            $params = [
                'profileName' => $profileName,
                'token' => $token,
                'responseType' => $responseType,
                'responseUri' => $responseUri,
                'listName' => $listName,
                'transactionType' => 'CREATE'
            ];
            return $this->processList($params, $filename);
        }
        /**
         * Replaces a contact list in the given profile (secured by the token)
         * @param profileName   string      The name of the profile to create the list in
         * @param token         string      Security token securing the list uploads for the profile
         * @param responseType  string      The type of response for the upload notification
         * @param responseUri   string      Where to send the notification
         * @param listName      string      The name of the list to replace in the platform
         * @param filename      string      filepath and name of the file to upload
        **/
        public function replaceList(
            $profileName,
            $token,
            $responseType,
            $responseUri,
            $listName,
            $filename
        )
        {
            $params = [
                'profileName' => $profileName,
                'token' => $token,
                'responseType' => $responseType,
                'responseUri' => $responseUri,
                'listName' => $listName,
                'transactionType' => 'REPLACE'
            ];
            return $this->processList($params, $filename);
        }

        /**
         * Appends (updates / inserts to) a contact list in the given profile (secured by the token)
         * @param profileName   string      The name of the profile to create the list in
         * @param token         string      Security token securing the list uploads for the profile
         * @param responseType  string      The type of response for the upload notification
         * @param responseUri   string      Where to send the notification
         * @param listName      string      The name of the list to append in the platform
         * @param filename      string      filepath and name of the file to upload
        **/
        public function appendList(
            $profileName,
            $token,
            $responseType,
            $responseUri,
            $listName,
            $filename
        )
        {
            $params = [
                'profileName' => $profileName,
                'token' => $token,
                'responseType' => $responseType,
                'responseUri' => $responseUri,
                'listName' => $listName,
                'transactionType' => 'APPEND'
            ];
            return $this->processList($params, $filename);
        }
        /**
         * Fed by the public classes this uploads the contact list
         * @param params    array       Array of key value pairs that make up the query parameters
         * @param filename  string      filepath and name of the file to upload
        **/
        private function processList($params, $filename)
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
            return $this->webRequest($params, self::LIST_UPLOAD_DATA, $filename);
        }
        /**
         * Converts the first line of a CSV into the required meta data
         * @param filename  string  filepath and name of the file to upload
        **/
        private function getHeaderIndexes($filename)
        {
            $meta_data = [];
            if (($handle = fopen($filename, 'r')) !== FALSE)
            {
                $line = array_flip(fgetcsv($handle, 1000, ','));
                foreach ($line as $key => $value)
                {
                    if (
                        (stristr($key, 'email')!==FALSE) &&
                        (!isset($meta_data['emailCol']))
                    )
                    {
                        $meta_data['emailCol'] = $value;
                    }
                    else
                    {
                        $meta_data["COL_{$key}"] = $value;
                    }
                }
            }
            fclose($handle);
            return $meta_data;
        }

        /**
         * This sends the web request using curl and CURLFile to the endpoint
         * with the query parameters
         * @param payload       array   Query parameters
         * @param url           string  End point
         * @param upload_file   string  filepath and name of the file to upload
        **/
        private function webRequest($payload, $url, $upload_file=null)
        {
            $curl = curl_init();
            if($upload_file !== null)
            {
                // Converts the filename and path to a CURLFile
                if(stristr($upload_file, './') !== FALSE)
                    $upload_file = realpath('./') . '/' . basename($upload_file);
                $payload['file'] = new CURLFile("{$upload_file}");
            }
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
