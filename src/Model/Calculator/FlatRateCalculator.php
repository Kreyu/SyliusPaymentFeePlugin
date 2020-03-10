<?php

declare(strict_types=1);

namespace Kreyu\SyliusPaymentFeePlugin\Model\Calculator;

use Sylius\Component\Core\Exception\MissingChannelConfigurationException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class FlatRateCalculator implements CalculatorInterface
{
	public function calculate(PaymentInterface $subject, array $configuration): ?int
	{
		$order = $subject->getOrder();

		Assert::isInstanceOf($order, OrderInterface::class);
		Assert::notNull($order->getChannel());

		$channelCode = $order->getChannel()->getCode();

		if (!isset($configuration[$channelCode])) {
			throw new MissingChannelConfigurationException(
				sprintf(
					'Channel %s has no amount defined for payment method %s',
					$order->getChannel()->getName(),
					$subject->getMethod() ? $subject->getMethod()->getName() : 'null'
				)
			);
		}

		return (int) $configuration[$channelCode]['amount'];
	}

	public function getType(): string
	{
		return 'kreyu_payment_fee_flat_rate';
	}
}
