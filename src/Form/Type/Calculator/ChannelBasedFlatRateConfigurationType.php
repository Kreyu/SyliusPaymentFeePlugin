<?php

declare(strict_types=1);

namespace Kreyu\Sylius\PaymentFeePlugin\Form\Type\Calculator;

use Sylius\Bundle\CoreBundle\Form\Type\ChannelCollectionType;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

final class ChannelBasedFlatRateConfigurationType extends AbstractType
{
	/**
	 * {@inheritDoc}
	 */
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
				'entry_type' => FlatRateConfigurationType::class,
				'entry_options' => function (ChannelInterface $channel): array {
					Assert::notNull($channel->getBaseCurrency());

					return [
						'label' => $channel->getName(),
						'currency' => $channel->getBaseCurrency()->getCode(),
					];
				},
			]
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParent(): string
	{
		return ChannelCollectionType::class;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBlockPrefix(): string
	{
		return 'kreyu_payment_fee_channel_based_payment_calculator_flat_rate';
	}
}
