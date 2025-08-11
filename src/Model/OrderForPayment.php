<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model;

use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\Exception\InvalidPayloadException;

readonly class OrderForPayment
{
    /**
     * @param array{
     *     currency: string|null,
     *     amount: int,
     *     order_number: int|string,
     * } $data
     *
     * @throws InvalidPayloadException
     */
    public static function fromArray(array $data): static
    {
        return new self(
            currency: $data['currency']
                      ?? throw new InvalidPayloadException('Currency is required in payment payload'),
            amount: $data['amount']
                    ?? throw new InvalidPayloadException('Amount is required in payment payload'),
            orderNumber: $data['order_number']
                         ?? throw new InvalidPayloadException('Order number is required in payment payload'),
        );
    }

    public function __construct(
        private ?string $currency,
        private int $amount,
        private int | string $orderNumber,
    ) {
    }

    /**
     * @return array{
     *     currency: string|null,
     *     amount: float,
     *     order_number: int|string,
     * }
     */
    public function toArray(): array
    {
        return [
            'currency' => $this->getCurrency(),
            'amount' => $this->getAmount(),
            'order_number' => $this->getOrderNumber(),
        ];
    }

    public function getCurrency(): ?string
    {
        return 'CZK'; // TODO revert $this->currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getOrderNumber(): int | string
    {
        return $this->orderNumber;
    }
}
