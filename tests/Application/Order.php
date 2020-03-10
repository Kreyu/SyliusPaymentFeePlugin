<?php

declare(strict_types=1);

namespace Tests\Kreyu\SyliusPaymentFeePlugin\Application;

use Kreyu\SyliusPaymentFeePlugin\Model\OrderWithPaymentTotalInterface;
use Kreyu\SyliusPaymentFeePlugin\Model\OrderWithPaymentTotalTrait;
use Sylius\Component\Core\Model\Order as BaseOrder;

class Order extends BaseOrder implements OrderWithPaymentTotalInterface
{
	use OrderWithPaymentTotalTrait;
}
