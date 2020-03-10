<?php

declare(strict_types=1);

namespace Kreyu\SyliusPaymentFeePlugin\Form\Extension;

use Kreyu\SyliusPaymentFeePlugin\Form\Type\CalculatorChoiceType;
use Kreyu\SyliusPaymentFeePlugin\Model\Calculator\CalculatorInterface;
use Kreyu\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;
use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodType;
use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Registry\FormTypeRegistryInterface;
use Sylius\Bundle\TaxationBundle\Form\Type\TaxCategoryChoiceType;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Webmozart\Assert\Assert;

final class PaymentMethodTypeExtension extends AbstractTypeExtension
{
	/** @var ServiceRegistryInterface */
	private $calculatorRegistry;

	/** @var FormTypeRegistryInterface */
	private $formTypeRegistry;

	public function __construct(
		ServiceRegistryInterface $calculatorRegistry,
		FormTypeRegistryInterface $formTypeRegistry
	) {
		$this->calculatorRegistry = $calculatorRegistry;
		$this->formTypeRegistry = $formTypeRegistry;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->addEventSubscriber(new AddCodeFormSubscriber())
			->add('taxCategory', TaxCategoryChoiceType::class)
			->add('calculator', CalculatorChoiceType::class, [
				'label' => 'kreyu_sylius_payment_fee_plugin.form.payment_method.calculator',
			])
			->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
				$method = $event->getData();

				if (null === $method || null === $method->getId()) {
					return;
				}

				if ($method instanceof PaymentMethodWithFeeInterface && $method->getCalculator() !== null) {
					$this->addConfigurationField($event->getForm(), $method->getCalculator());
				}
			})
			->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
				$data = $event->getData();

				if (!is_array($data) || empty($data) || !array_key_exists('calculator', $data)) {
					return;
				}

				$this->addConfigurationField($event->getForm(), $data['calculator']);
			});

		$prototypes = [];

		/**
		 * @var string $name
		 * @var CalculatorInterface $calculator
		 */
		foreach ($this->calculatorRegistry->all() as $name => $calculator) {
			Assert::isInstanceOf($calculator, CalculatorInterface::class);

			$calculatorType = $calculator->getType();

			if (!$this->formTypeRegistry->has($calculatorType, 'default')) {
				continue;
			}

			$form = $builder->create(
				'calculatorConfiguration',
				$this->formTypeRegistry->get($calculatorType, 'default')
			);

			$prototypes['calculators'][$name] = $form->getForm();
		}

		$builder->setAttribute('prototypes', $prototypes);
	}

	private function addConfigurationField(FormInterface $form, string $calculatorName): void
	{
		$calculator = $this->calculatorRegistry->get($calculatorName);

		Assert::isInstanceOf($calculator, CalculatorInterface::class);

		$calculatorType = $calculator->getType();

		if (!$this->formTypeRegistry->has($calculatorType, 'default')) {
			return;
		}

		$form->add('calculatorConfiguration', $this->formTypeRegistry->get($calculatorType, 'default'));
	}

	public function buildView(FormView $view, FormInterface $form, array $options): void
	{
		$view->vars['prototypes'] = [];

		foreach ($form->getConfig()->getAttribute('prototypes') as $group => $prototypes) {
			foreach ($prototypes as $type => $prototype) {
				$view->vars['prototypes'][$group . '_' . $type] = $prototype->createView($view);
			}
		}
	}

	public function getExtendedType()
	{
		return PaymentMethodType::class;
	}

	public static function getExtendedTypes(): iterable
	{
		return [
			PaymentMethodType::class,
		];
	}
}
