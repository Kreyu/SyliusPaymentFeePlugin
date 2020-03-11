<h1 align="center">Payment Fee Plugin</h1>

## Features

* Charge extra fee for using payment method.
* Typical usage: Cash on Delivery.
* Taxes are implemented the same way as taxes for shipping fees.

<p align="center">
	<img src="https://raw.githubusercontent.com/kreyu/SyliusPaymentFeePlugin/master/doc/admin.png"/>
</p>

## Requirements

- PHP ^7.2
- Sylius ^1.4

## Installation

Require the plugin using the Composer:

```shell script
$ composer require kreyu/sylius-payment-fee-plugin
```

Register the plugin in the application's `bundles.php` file:

```php
<?php

return [
    // ...
    Kreyu\SyliusPaymentFeePlugin\KreyuSyliusPaymentFeePlugin::class => ['all' => true],
];
```

Extend the `PaymentMethod` entity to use this plugin's interfaces and trait:

```php
<?php

use Kreyu\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeTrait;
use Kreyu\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;

class PaymentMethod extends BasePaymentMethod implements PaymentMethodWithFeeInterface
{
    use PaymentMethodWithFeeTrait;
}
```

Next, extend the `Order` entity to use this plugin's interfaces and traits:

```php
<?php

use Kreyu\SyliusPaymentFeePlugin\Model\OrderWithPaymentTotalInterface;
use Kreyu\SyliusPaymentFeePlugin\Model\OrderWithPaymentTotalTrait;
use Sylius\Component\Core\Model\Order as BaseOrder;

class Order extends BaseOrder implements OrderWithPaymentTotalInterface
{
    use OrderWithPaymentTotalTrait;
}
```

For more information about customizing models, see [Sylius docs - Customizing Models](https://docs.sylius.com/en/1.4/customization/model.html)

### Customizing admin templates

For more information about overriding the templates, see [Symfony docs - Overriding templates](https://symfony.com/doc/current/bundles/override.html#templates)

To display the additional form fields in admin panel, override the `@SyliusAdmin/PaymentMethod/_form.html.twig` template:

```twig
{# templates/bundles/SyliusAdminBundle/PaymentMethod/_form.html.twig #}

{% include '@!SyliusAdmin/PaymentMethod/_form.html.twig' %}

{% include 'KreyuSyliusPaymentFeePlugin:Admin/PaymentMethod:_form.html.twig' %}
```

To display the payment fee total in the order details in admin panel, override the `@SyliusAdmin/Order/Show/Summary/_totals.html.twig` template:

```twig
{# templates/bundles/SyliusAdminBundle/Order/Show/Summary/_totals.html.twig #}

{% include '@!SyliusAdmin/Order/Show/Summary/_totals.html.twig' %}

{% include 'KreyuSyliusPaymentFeePlugin:Admin/Order/Show/Summary:_totals_payment_fee.html.twig' %}
```

### Integration with SyliusShopBundle

If you're using the [SyliusShopBundle](https://github.com/Sylius/Sylius/tree/master/src/Sylius/Bundle/ShopBundle), you may want to override the bundle templates.  
Because shop templates does not use Sonata Block events nor the twig blocks, we have to copy the entire templates.    
For more information, take a look at the [test application ShopBundle templates](tests/Application/templates/bundles/SyliusShopBundle) to see what has to be changed and how.

### Integration with SyliusShopApiBundle

First, override the `TotalsView` view model, to add the `payment` property:

```php
<?php

namespace App\View\Cart;

use Sylius\ShopApiPlugin\View\Cart\TotalsView as BaseTotalsView;

/**
 * @author Sebastian Wróblewski <kontakt@swroblewski.pl>
 */
class TotalsView extends BaseTotalsView
{
    /** @var int */
    public $payment;
}
```

Register the overriden view in the `config/packages/_sylius_shop_api.yaml` configuration file:

```yaml
imports:
    - { resource: '@SyliusShopApiPlugin/Resources/config/app/config.yml' }
    - { resource: '@SyliusShopApiPlugin/Resources/config/app/sylius_mailer.yml' }

parameters:
    sylius.shop_api.view.totals.class: App\View\Cart\TotalsView
```

Then, decorate the `TotalViewFactory`, to provide data for the added `payment` field:

```php
<?php

namespace App\ViewFactory\Cart;

use App\View\Cart\TotalsView;
use Kreyu\SyliusPaymentFeePlugin\Model\OrderWithPaymentTotalInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\ShopApiPlugin\Factory\Cart\TotalViewFactoryInterface;
use Sylius\ShopApiPlugin\View\Cart\TotalsView as BaseTotalsView;

class TotalViewFactory implements TotalViewFactoryInterface
{
    /** @var TotalViewFactoryInterface */
    private $decoratedFactory;

    public function __construct(TotalViewFactoryInterface $decoratedFactory)
    {
        $this->decoratedFactory = $decoratedFactory;
    }

    /**
     * {@inheritDoc}
     *
     * @param OrderWithPaymentTotalInterface $cart
     */
    public function create(OrderInterface $cart): BaseTotalsView
    {
        /** @var TotalsView $totalsView - sylius.shop_api.view.totals.class */
        $totalsView = $this->decoratedFactory->create($cart);

        $totalsView->payment = $cart->getPaymentTotal();

        return $totalsView;
    }
}
```

Last but not least, register the factory as the service:

```yaml
services:
    app.view_factory.total_view_factory:
        class: App\ViewFactory\Cart\TotalViewFactory
        decorates: sylius.shop_api_plugin.factory.total_view_factory
        arguments:
            - '@app.view_factory.total_view_factory.inner'
```

### License

This library is under the MIT license.

### Credits

Developed initially by [manGoweb](https://www.mangoweb.eu/)  
Forked and maintained by [Sebastian Wróblewski <Kreyu>](https://swroblewski.pl)
