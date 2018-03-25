<?php

declare(strict_types=1);

namespace Buzz\Client;

use Buzz\Configuration\ParameterBag;
use Buzz\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractClient
{
    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var ParameterBag
     */
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = new ParameterBag();
        $this->options = $this->doValidateOptions($options);
    }

    protected function getOptionsResolver()
    {
        if (null !== $this->optionsResolver) {
            return $this->optionsResolver;
        }

        $this->optionsResolver = new OptionsResolver();
        $this->configureOptions($this->optionsResolver);

        return $this->optionsResolver;
    }

    /**
     * Validate a set of options and return a new and shiny ParameterBag.
     */
    protected function validateOptions(array $options = []): ParameterBag
    {
        if (empty($options)) {
            return $this->options;
        }

        return $this->doValidateOptions($options);
    }

    /**
     * Validate a set of options and return a new and shiny ParameterBag.
     */
    private function doValidateOptions(array $options = []): ParameterBag
    {
        $parameterBag = $this->options->add($options);
        try {
            $parameters = $this->getOptionsResolver()->resolve($parameterBag->all());
        } catch (\Throwable $e) {
            // Wrap any errors.
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        return new ParameterBag($parameters);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'follow_redirects' => false,
            'max_redirects' => 5,
            'timeout' => 30,
            'verify_peer' => true,
            'verify_host' => true,
            'proxy' => null,
        ]);

        $resolver->setAllowedTypes('follow_redirects', 'boolean');
        $resolver->setAllowedTypes('verify_peer', 'boolean');
        $resolver->setAllowedTypes('verify_host', 'boolean');
        $resolver->setAllowedTypes('max_redirects', 'integer');
        $resolver->setAllowedTypes('timeout', ['integer', 'float']);
        $resolver->setAllowedTypes('proxy', ['null', 'string']);
    }
}
