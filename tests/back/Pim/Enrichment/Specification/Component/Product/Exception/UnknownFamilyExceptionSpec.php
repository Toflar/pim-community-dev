<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownFamilyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;

class UnknownFamilyExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('family', 'family_code', self::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UnknownFamilyException::class);
    }

    function it_is_an_invalid_property_exception()
    {
        $this->shouldHaveType(InvalidPropertyException::class);
    }

    function it_is_a_domain_error()
    {
        $this->shouldImplement(DomainErrorInterface::class);
    }

    function it_is_has_a_templated_error_message()
    {
        $this->shouldImplement(TemplatedErrorMessageInterface::class);
    }

    function it_returns_a_message()
    {
        $this->getMessage()->shouldReturn('The family_code family does not exist in your PIM.');
    }

    function it_returns_a_message_template_and_parameters()
    {
        $templatedErrorMessage = $this->getTemplatedErrorMessage();
        $templatedErrorMessage->shouldBeAnInstanceOf(TemplatedErrorMessage::class);
        $templatedErrorMessage->__toString()
            ->shouldReturn('The family_code family does not exist in your PIM.');
    }
}
