'''
An example class for sending a one to one message
Python 3.4
category Data Gathering
author Matt Barber
created_at 14/09/2015
'''
# Relies on the requests module - install with pip install requests
# Github : http://docs.python-requests.org/en/latest/
import requests
import re
import datetime

class Pure360Http():
    ONE_TO_ONE_URL = 'http://response.pure360.com/interface/common/one2OneCreate.php'

    def send(
        self,
        userName,
        password,
        contentType,
        recipient,
        message_params,
        deliveryDtTm=None,
        json='true'
    ):
        '''Sends a one to one message
        :param userName, a string representing an API system username (ending *.sys)
        :param password, a string representing an API system password
        :param contentType, a string representing either EMAIL or SMS
        :param recipient, a string of the contact detail to send to
        :param message_params, a dictionary of key value pairs detailing the message
            if message_messageName provided as a key then use an existing message
            otherwise for email this must contain:
                message_bodyPlain       The plain text version of the email
                message_bodyHtml        The html content of the email
                message_subject         The subject line to use for the email
                message_trackHtmlInd    Y or N tracks html clicks / opens
                message_trackPlainInd   Y or N tracks plain clicks / opens
            for sms this must contain:
                message_bodySms         The content of the sms

            message_params can also contain custom data for the message perosnalisation
                customData  A dictionary of key value pairs
         :param deliveryDtTm, a string representing the delivery datetime as dd/mm/yyyy hh:mm:ss
         :param json, a string boolean representing whether to return a json structure
        '''
        if contentType not in ('EMAIL', 'SMS'):
            raise ValueError('contentType must be either EMAIL or SMS')
        elif contentType is 'SMS' and self.is_email(recipient):
            raise ValueError('Cannot send SMS to email address')
        elif contentType is 'EMAIL' and not self.is_email(recipient):
            raise ValueError('Cannot send EMAIL to mobile number')
        deliveryDtTm = (
            deliveryDtTm if deliveryDtTm is not None
        else datetime.datetime.now().strftime('%d/%m/%Y %H:%M:%S')
        )
        params = {
            'userName': userName,
            'password': password,
            'message_contentType': contentType,
            'toAddress': recipient,
            'deliveryDtTm': deliveryDtTm,
            'json': json
        }
        if 'message_messageName' not in message_params:
            if params['message_contentType'] is 'EMAIL':
                if not set(
                    (
                        'message_bodyPlain',
                        'message_bodyHtml',
                        'message_subject',
                        'message_trackHtmlInd',
                        'message_trackPlainInd'
                    )
                ) <= set(message_params):
                    raise AttributeError('If not supplying a message name ensure you provide the required keys')
            elif 'message_bodySms' not in message_params:
                raise AttributeError('If not supplying a message name ensure you provide the required keys')
        params.update(message_params)
        return self.__web_request(params, self.ONE_TO_ONE_URL)

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
