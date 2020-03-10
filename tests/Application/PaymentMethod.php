<?php

declare(strict_types=1);

namespace Tests\Kreyu\SyliusPaymentFeePlugin\Application;

use Kreyu\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;
use Kreyu\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeTrait;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;

class PaymentMethod extends BasePaymentMethod implements PaymentMethodWithFeeInterface
{
	use PaymentMethodWithFeeTrait;
}
