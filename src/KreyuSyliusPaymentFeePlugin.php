<?php

declare(strict_types=1);

namespace Kreyu\SyliusPaymentFeePlugin;

use Kreyu\SyliusPaymentFeePlugin\DependencyInjection\Compiler\RegisterFeeCalculatorsPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KreyuSyliusPaymentFeePlugin extends Bundle
{
	use SyliusPluginTrait;

	public function build(ContainerBuilder $container)
	{
		$container->addCompilerPass(new RegisterFeeCalculatorsPass);
	}
}
