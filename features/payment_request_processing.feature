@payment_request_processing
Feature: GPWebPay Payment Request processing
    In order to process payments through GPWebPay
    As a Customer
    I want to be able to complete payments using GPWebPay payment gateway

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "PHP T-Shirt" priced at "$19.99"
        And the store ships everywhere for free
        And the store allows paying with name "GP webpay" and code "gpwebpay" gpwebpay gateway
        And I am a logged in customer

    @ui
    Scenario: Starting a payment with GPWebPay
        Given I have product "PHP T-Shirt" in the cart
        And I specified the billing address as "Ankh Morpork", "Frost Alley", "90210", "United States" for "Jon Snow"
        And I select "Free" shipping method
        And I complete the shipping step
        When I select "GP webpay" payment method
        And I complete the payment step
        Then I should be on the checkout complete step
        And I should see "Thank you!"

    @ui
    Scenario: Handling payment notification from GPWebPay
        Given I have placed an order with "GP webpay" payment method
        And the payment request is in "processing" state
        When GPWebPay sends a successful payment notification
        Then the payment request should be completed
        And the order should be marked as paid

    @ui
    Scenario: Handling failed payment notification from GPWebPay
        Given I have placed an order with "GP webpay" payment method
        And the payment request is in "processing" state
        When GPWebPay sends a failed payment notification
        Then the payment request should be failed
        And the order should remain unpaid