'''
An example class for signing a recipient up to a list, and opting out of a profile
Python 3.4
category Data Gathering
author Matt Barber
created_at 14/09/2015
'''
# Relies on the requests module - install with pip install requests
# Github : http://docs.python-requests.org/en/latest/
import requests
import re

class Pure360Http():
    LIST_URL = 'http://response.pure360.com/interface/list.php'

    def signup(self, accName, listName, recipient, customData={}, doubleOptin='FALSE'):
        '''Performs a signup to a list
        :param accName, a string representing the profile name
        :param listName, a string representing the list name
        :param recipient, a string representing the recipient contact detail
        :param customData, a dictionary representing the custom fields and values for the list
        :param doubleOptin, a string (bool) will passively opt recipient in if set to TRUE

        :rtype string
        '''
        params = {
            'accName': accName,
            'listName': listName,
            'doubleOptin': doubleOptin,
            'successUrl': 'NO-REDIRECT',
            'errorUrl': 'NO-REDIRECT'
        }
        params.update(customData)
        params.update(
            {'email': recipient} if self.is_email(recipient)
            else {'mobile': recipient}
        )
        return self.__web_request(params, self.LIST_URL)

    def optout(self, accName, recipient):
        '''Opts a recipient out of a whole profile
        :param accName, a string representing the profile name
        :param recipient, a string representing the recipient contact detail

        :rtype string
        '''
        params = {
            'accName': accName,
            'mode': 'OPTOUT'
        }
        params.update(
            {'email': recipient} if self.is_email(recipient)
            else {'mobile': recipient}
        )
        return self.__web_request(params, self.LIST_URL)

    def is_email(self, recipient):
        '''Checks if the recipient is a mobile or email
        :param recipient, a string representing the recipient contact detail

        :rtype boolean
        '''
        try:
            int(recipient)
            return False
        except ValueError:
            if not re.match(r"[^@]+@[^@]+\.[^@]+", recipient):
                raise ValueError('Not an email address or mobile number')
            else:
                return True


    def __web_request(self, payload, url, upload_file=None):
        '''This method sends the web request to the endpoint with the query parameters

        :param payload, a dictionary of key value pairs that make up the query parameters
        :param url, a string representing an endpoint

        :rtype string
        '''
        req = requests.post(url, data=payload)
        return req.text
