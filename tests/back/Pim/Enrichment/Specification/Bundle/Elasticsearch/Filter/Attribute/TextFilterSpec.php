<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\AbstractAttributeFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\TextFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;

class TextFilterSpec extends ObjectBehavior
{
    function let(ElasticsearchFilterValidator $filterValidator)
    {
        $this->beConstructedWith(
            $filterValidator,
            ['pim_catalog_text'],
            ['STARTS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN', 'EMPTY', 'NOT EMPTY', '!=']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractAttributeFilter::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            'STARTS WITH',
            'CONTAINS',
            'DOES NOT CONTAIN',
            '=',
            'IN',
            'EMPTY',
            'NOT EMPTY',
            '!=',
        ]);
        $this->supportsOperator('STARTS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_equals(
        $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $filterValidator->validateLocaleForAttribute('name', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('name', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter([
                'term' => [
                    'values.name-text.ecommerce.en_US' => 'Sony',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::EQUALS, 'Sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_equal(
        $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $filterValidator->validateLocaleForAttribute('name', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('name', 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot([
                'term' => [
                    'values.name-text.ecommerce.en_US' => 'Sony',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter([
                'exists' => ['field' => 'values.name-text.ecommerce.en_US'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::NOT_EQUAL, 'Sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_empty(
        $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $filterValidator->validateLocaleForAttribute('name', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('name', 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot([
                'exists' => [
                    'field' => 'values.name-text.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['attributes_for_this_level' => ['name']]],
                        ['terms' => ['attributes_of_ancestors' => ['name']]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::IS_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $filterValidator->validateLocaleForAttribute('name', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('name', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter([
                'exists' => [
                    'field' => 'values.name-text.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::IS_NOT_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_contains(
        $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $filterValidator->validateLocaleForAttribute('name', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('name', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter([
                'query_string' => [
                    'default_field' => 'values.name-text.ecommerce.en_US',
                    'query'         => '*sony*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::CONTAINS, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_does_not_contain(
        $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $filterValidator->validateLocaleForAttribute('name', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('name', 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'query_string' => [
                    'default_field' => 'values.name-text.ecommerce.en_US',
                    'query'         => '*sony*',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.name-text.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::DOES_NOT_CONTAIN, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_starts_with(
        $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $filterValidator->validateLocaleForAttribute('name', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('name', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter([
                'query_string' => [
                    'default_field' => 'values.name-text.ecommerce.en_US',
                    'query'         => 'sony*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::STARTS_WITH, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $name)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string(
        $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $filterValidator->validateLocaleForAttribute('name', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('name', 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'name',
                TextFilter::class,
                123
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 123, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_value_is_null(
        ElasticsearchFilterValidator $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $filterValidator->validateLocaleForAttribute('name', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('name', 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'name',
                TextFilter::class,
                123
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 123, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');

        $filterValidator->validateLocaleForAttribute('name', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('name', 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                TextFilter::class
            )
        )->during('addAttributeFilter', [$name, Operators::IN_CHILDREN_LIST, 'Sony', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');
        $name->isLocaleSpecific()->willReturn(true);
        $name->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "name" expects a locale, none given.');
        $filterValidator->validateLocaleForAttribute('name', 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'name',
                TextFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $filterValidator,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');
        $name->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "name" does not expect a scope, "ecommerce" given.');
        $filterValidator->validateLocaleForAttribute('name', 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'name',
                TextFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);
    }
}
