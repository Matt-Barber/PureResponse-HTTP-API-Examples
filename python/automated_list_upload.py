'''
An example class for creating, replacing, and appending contact lists
Python 3.4
category Data Gathering
author Matt Barber
created_at 14/09/2015
'''
# Relies on the requests module - install with pip install requests
# Github : http://docs.python-requests.org/en/latest/
import requests
import csv


class Pure360Http():
    LIST_UPLOAD_META = 'http://response.pure360.com/interface/list_upload_meta.php'
    LIST_UPLOAD_DATA = 'http://response.pure360.com/interface/list_upload_data.php'

    def __init__(self):
        pass

    def create_list(self, profileName, token, responseType, responseUri, listName, filename):
        '''Creates a new contact list in the given profile (secured by the token)

        :param profileName, a string representing the profile name
        :param token, a string representing the security token (given in the account details)
        :param responseType, a string of either HTTP, EMAIL or REST
        :param responseUri, a string representing where to send the upload notification
        :param listName, a string representing the name of the list in the platform
        :param filename, a string of the file path and name of the file to upload

        :rtype string
        '''
        params = {
            'profileName': profileName,
            'token': token,
            'responseType': responseType,
            'responseUri': responseUri,
            'listName': listName,
            'transactionType': 'CREATE'
        }
       return self.process_list(params, filename)

    def replace_list(self, profileName, token, responseType, responseUri, listName, filename):
        '''Replaces a contact list in the given profile (secured by the token)

        :param profileName, a string representing the profile name
        :param token, a string representing the security token (given in the account details)
        :param responseType, a string of either HTTP, EMAIL or REST
        :param responseUri, a string representing where to send the upload notification
        :param filename, a string of the file path and name of the file to upload

        :rtype string
        '''
        params = {
            'profileName': profileName,
            'token': token,
            'responseType': responseType,
            'responseUri': responseUri,
            'listName': listName,
            'transactionType': 'REPLACE'
        }
        return self.process_list(params, filename)

    def append_list(self, profileName, token, responseType, responseUri, listName, filename):
        '''Appends a contact list in the given profile (secured by the token)

        :param profileName, a string representing the profile name
        :param token, a string representing the security token (given in the account details)
        :param responseType, a string of either HTTP, EMAIL or REST
        :param responseUri, a string representing where to send the upload notification
        :param filename, a string of the file path and name of the file to upload

        :rtype string
        '''
        params = {
            'profileName': profileName,
            'token': token,
            'responseType': responseType,
            'responseUri': responseUri,
            'listName': listName,
            'transactionType': 'APPEND'
        }
        return self.process_list(params, filename)

    def process_list(self, params, filename):
        '''Processes a list upload

        :param params a dictionary of query parameters to upload
        '''
        with open(filename, 'r') as pointer:
            params.update(
                self.__get_header_index(pointer.readline(), ',', '\"')
            )
        # Extract the transactionId from the return string
        transactionId = self.__web_request(
            params,
            self.LIST_UPLOAD_META
        ).split(':').pop().strip()

        params = {
            'profileName': params['profileName'],
            'transactionId': transactionId
        }
        upload_file = {
            'name': 'upload.csv',
            'source': 'file',
            'data': filename,
            'type': 'text/csv'
        }
        return self.__web_request(params, self.LIST_UPLOAD_DATA, upload_file)

    def __get_header_index(self, line, terminator, encloser):
        '''This method reads the CSV line using the standard library, a comprehension
        is used to convert this into a dictionary of key value pairs where the key is the header,
        and the value is the index that column resides in in the file.

        :param line, a string representation of a line from the CSV file
        :param terminator, a string representing the termination of fields in a line
        :param encloser, a string denoting the symbol that encloses multiple values in a field

        :rtype dictionary
        '''
        values = list(
            csv.reader(
                [line],
                delimiter=terminator,
                quotechar=encloser
            )
        ).pop()
        indexed_data = {
            # currently only accounts for emailCol and not mobileCol
            'COL_{}'.format(field.strip()) if 'email' not in field else 'emailCol' : idx
            for field, idx in zip(
                values,
                range(0, len(line))
            )
        }
        return indexed_data

    def __web_request(self, payload, url, upload_file=None):
        '''This method sends the web request to the endpoint with the query parameters
        and optionally an upload file.

        :param payload, a dictionary of key value pairs that make up the query parameters
        :param url, a string represnting an endpoint
        :param upload_file, a dictionary representing an upload file

        :rtype string
        '''
        if upload_file is not None:
            # Configure the file for upload
            send_file = {
                'file': (
                    (upload_file['name'], upload_file['data'])
                    if upload_file['source'] is 'string' else
                    (
                        upload_file['name'],
                        open(upload_file['data'], 'rb'),
                        upload_file['type'],
                        {'Expires': '0'}
                    )
                )
            }
            req = requests.post(url, data=payload, files=send_file)
        else:
            req = requests.post(url, data=payload)
        return req.text
