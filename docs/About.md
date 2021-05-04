---
title: "About Minimum Maximum Order By Country"
date: 2021-04-27T12:53:58+01:00
draft: false
---
This Module allows you to set minimum and maximum order values for your store. The values are base on the Cart total figure which excludes postage and packing. All values are base on the default **store** currency, however messages to customers will be displayed in the currency they are using if you allow multiple currencies.

## Where is it used
- When a customer visits a cart
- When a customer logs in and combines an existing cart with items already in the guest cart (if any)
- When a customer tries to check out at all stages of checkout
- When a customer returns from PayPal Express checkout

## What happens
The plug in checks the value in the shopping cart (excluding delivery costs) against the minimum and maximum values set for the **delivery** country.  
**Note:** This may not be the same as the Billing country.

## How is country determined
The delivery country is determine in the following order.
1. The country requested to check shipping costs -  from the shopping cart 
2. The address book entry the user has requested for delivery - from the shopping cart 
3. The delivery address entered - in the checkout process
4. The customers default address - if the customer is logged in and no other information has been entered.
5. The store country - if no other information has been entered.

## How are the minimum and maximum values determined
The minimum and maximum values are determine in the following order
1. A specific entry for the country in the list of countries.
2. Default values

**Note:** If a country has multiple entries (is in more than one list) the results are unpredictable.

## What happens if the value of the purchase is outside of the minimum or maximum values
The cart status is set to unable to check out, the customer is returned to the shopping cart and a message is displayed.
- The default message for under the minimum amount is:  
> A minimum order amount of XXX is required in order to checkout.
- The default message for over the maximum amount is:  
> You have exceeded the maximum order amount of XXX.

Where XXX is the value of the minimum or maximum converted to the customers cart currency.  <br>
These messages can be modified using [zen cart override system](https://docs.zen-cart.com/user/first_steps/overrides/ "Basics - Default files, template default and overrides")

## Installation
For installation instructions please see [Installation Guide](Installation.md)

## Setting up my minimum and maximum values
For editing and maintaining Min/max values please see [Edit Guide](Edit.md)
