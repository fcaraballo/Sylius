<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\Form\Type\Shipping;

use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType as BaseShippingMethodType;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A select form which allows the user to select
 * a method that supports given shippables aware.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class ShippingMethodChoiceType extends BaseShippingMethodType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $methodsResolver = $this->resolver;
        $repository = $this->repository;

        $choiceList = function (Options $options) use ($methodsResolver, $repository) {
            if (isset($options['subject'])) {
                $methods = $methodsResolver->getSupportedMethods($options['subject'], $options['criteria']);
            } else {
                $methods = $repository->findBy($options['criteria']);
            }

            if ($options['channel']) {
                $filteredMethods = array();
                foreach($methods as $method) {
                    if ($options['channel']->hasShippingMethod($method)) {
                        $filteredMethods[] = $method;
                    }
                }

                $methods = $filteredMethods;
            }

            return new ObjectChoiceList($methods, null, array(), null, 'id');
        };

        $resolver
            ->setDefaults(array(
                'choice_list' => $choiceList,
                'criteria'    => array(),
                'channel'     => null
            ))
            ->setAllowedTypes('channel', [ChannelInterface::class, 'null'])
        ;
    }
}
