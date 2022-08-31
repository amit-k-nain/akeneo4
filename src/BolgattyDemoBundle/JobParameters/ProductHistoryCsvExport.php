<?php

declare(strict_types=1);

namespace BolgattyDemoBundle\JobParameters;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Type;
// use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

$classLoader = new \BolgattyDemoBundle\Listener\ClassDefinationForCompatibility();
$classLoader->customLoaderSystem();

/**
 * Constraints for product CSV export
 */
class ProductHistoryCsvExport implements
    \ConstraintCollectionProviderInterface,
    \DefaultValuesProviderInterface
{
    /** @var string[] */
    private $supportedJobNames;
    protected $channelRepository;
    /**
     * @param string[] $supportedJobNames
     */
    public function __construct(
        $channelRepository,
        array $supportedJobNames
    ) {
        $this->channelRepository = $channelRepository;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        $channelData = $this->channelRepository->getFullChannels();
        $defaultChannelCode = isset($channelData[0]) ? $channelData[0]->getCode() : null;
        $defaultLocaleCode = isset($channelData[0]) ? [$channelData[0]->getLocales()->first()->getCode()] : [];
        $currency = isset($channelData[0]) ? $channelData[0]->getCurrencies()->first()->getCode() : null;
        $parameters['filters'] = [
            'data'      => [
                // [
                //     'field'    => 'enabled',
                //     'operator' => \Operators::EQUALS,
                //     'value'    => true,
                // ],
                // [
                //     'field'    => 'completeness',
                //     'operator' => \Operators::GREATER_OR_EQUAL_THAN,
                //     'value'    => 100,
                // ],
                [
                    'field'    => 'categories',
                    'operator' => \Operators::IN_CHILDREN_LIST,
                    'value'    => []
                ]
            ],
            'structure' => [
                'scope'    => $defaultChannelCode,
                'locale'   => $defaultLocaleCode,
                'locales'  => $defaultLocaleCode,
                'currency' => $currency,
            ],
        ];

        // $parameters['with_label'] = false;
        // $parameters['header_with_label'] = false;
        $parameters['withHeader'] = true;
        $parameters['exportProductModelFirst'] = false;
        $parameters['exportVariantAfterModel'] = false;
        $parameters['realTimeVersioning'] = false;
        $parameters['delimiter'] = ';';
        $parameters['enclosure'] = '"';
        $parameters['filePath'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_%job_label%_%datetime%.csv';
        
        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        $constraintFields['user_to_notify'] = new Optional();
        $constraintFields['filters'] = [
            new Collection(
                [
                    'fields'           => [
                        'structure' => [
                            new \FilterStructureLocale(['groups' => ['Default', 'DataFilters']]),
                            new Collection(
                                [
                                    'fields'             => [
                                        'locales'    => new NotBlank(['groups' => ['Default', 'DataFilters']]),
                                        'locale'     => new NotBlank(['groups' => ['Default', 'DataFilters']]),
                                        'scope'      => new NotBlank(['groups' => ['Default', 'DataFilters']]),
                                        'currency'   => new NotBlank(),
                                        'attributes' => new Type(
                                            [
                                                'type'  => 'array',
                                                'groups' => ['Default', 'DataFilters'],
                                            ]
                                        ),


                                    ],
                                    'allowMissingFields' => true,
                                ]
                            ),
                        ],
                    ],
                    'allowExtraFields' => true,
                ]
            ),
        ];
        if ($this->filterData()) {
            $constraintFields['filters'][]= $this->filterData();
        }

        $constraintFields['withHeader']         = new Optional();
        $constraintFields['filePath']           = new NotBlank();
        $constraintFields['delimiter']          = new NotBlank();
        $constraintFields['enclosure']          = new NotBlank();

        return new Collection([
                    'fields' => $constraintFields,
                    'allowMissingFields' => true,
                    'allowExtraFields' => true,
                ]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(\JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }

    public function filterData()
    {
        if (class_exists('\Pim\Component\Connector\Validator\Constraints\FilterData')) {
            return new \Pim\Component\Connector\Validator\Constraints\FilterData(['groups' => ['Default', 'DataFilters']]);
        } elseif (class_exists('\Pim\Component\Connector\Validator\Constraints\ProductFilterData')) {
            return new \Pim\Component\Connector\Validator\Constraints\ProductFilterData(['groups' => ['Default', 'DataFilters']]);
        }
    }
}
