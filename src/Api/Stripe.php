<?php
namespace Virton\Api;

use Stripe\{
    Card,
    Charge,
    Customer,
    Token
};

class Stripe
{
    /**
     * Stripe constructor.
     * @param string $token
     */
    public function __construct(string $token)
    {
        \Stripe\Stripe::setApiKey($token);
    }

    /**
     * @param string $token
     * @return mixed
     */
    public function getCardFromToken(string $token)
    {
        return Token::retrieve($token)->card;
    }

    /**
     * @param string $id
     * @return Customer
     */
    public function getCustomer(string $id): Customer
    {
        return Customer::retrieve($id);
    }

    /**
     * @param string[] $params
     * @return Customer
     */
    public function setCustomer(array $params): Customer
    {
        return Customer::create($params);
    }

    /**
     * @param Customer $customer
     * @param string $token
     * @return Card
     */
    public function setCardForCustomer(Customer $customer, string $token): Card
    {
        return $customer->sources->create(['source' => $token]);
    }

    /**
     * @param array $params
     * @return Charge
     */
    public function setCharge(array $params): Charge
    {
        return Charge::create($params);
    }
}
