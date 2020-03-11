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

### Customizing the shop templates

If you're using the [SyliusShopBundle](https://github.com/Sylius/Sylius/tree/master/src/Sylius/Bundle/ShopBundle), you may want to override the bundle templates.  
Because shop templates does not use Sonata Block events nor the twig blocks, we have to copy the entire templates.  

```twig
{# templates/bundles/SyliusShopBundle/Cart/Summary/_totals.html.twig #}

<div class="ui segment">
    <table class="ui very basic table">
        {# Rest of the table... #}

        {# Include the bundle template somewhere in this table... #}
        {% include 'KreyuSyliusPaymentFeePlugin:Shop/Cart/Summary:_totals_payment_method_cost.html.twig' %}
        
        {# Rest of the table... #}
    </table>
</div>
```

```twig
{# templates/bundles/SyliusShopBundle/Checkout/SelectPayment/_choice.html.twig #}

<div class="item">
	<div class="field">
		<div class="ui radio checkbox">
			{{ form_widget(form) }}
		</div>
	</div>
	<div class="content">
		<a class="header">{{ form_label(form) }}</a>
		{% if method.description is not null %}
			<div class="description">
				<p>{{ method.description }}</p>
			</div>
		{% endif %}
	</div>

	{% include 'KreyuSyliusPaymentFeePlugin:Shop/Checkout/SelectPayment:_choice_fee.html.twig' with { method: method } %}
</div>
```

```twig
{# templates/bundles/SyliusShopBundle/Checkout/_summary.html.twig #}

<div class="ui segment">
	<table class="ui very basic table" id="sylius-checkout-subtotal">
        {# Rest of the table... #}
 
		<tfoot>
            {# Include the bundle template somewhere in this table tfoot... #}
		    {% include 'KreyuSyliusPaymentFeePlugin:Shop/Checkout:_summary_payment_method_cost.html.twig' %}
		</tfoot>
	</table>
</div>
```

```twig
{# templates/bundles/SyliusShopBundle/Common/Order/Table/_totals.html.twig #}

{# Include the bundle template below the _shipping.html.twig import #}
<tr>
    {% include '@SyliusShop/Common/Order/Table/_shipping.html.twig' with {'order': order} %}
</tr>
<tr>
	{% include 'KreyuSyliusPaymentFeePlugin:Shop/Common/Order/Table:_payment.html.twig' with {'order': order} %}
</tr>
```

### License

This library is under the MIT license.

### Credits

Developed initially by [manGoweb](https://www.mangoweb.eu/)  
Forked and maintained by [Sebastian Wróblewski <Kreyu>](https://swroblewski.pl)
