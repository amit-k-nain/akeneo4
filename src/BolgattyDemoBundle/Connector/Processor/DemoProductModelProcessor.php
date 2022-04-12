<?php

declare(strict_types=1);

namespace BolgattyDemoBundle\Connector\Processor;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use BolgattyDemoBundle\Services\BolgattyDemoConnector;

$classLoader = new \BolgattyDemoBundle\Listener\ClassDefinationForCompatibility();
$classLoader->customLoaderSystem();

/**
 * Product model processor to process and normalize productModel model to the standard format
 */
class DemoProductModelProcessor extends \PimProductProcessor implements \ItemProcessorInterface, \StepExecutionAwareInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var \AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var \ObjectDetacherInterface */
    protected $detacher;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var \BulkMediaFetcher */
    protected $mediaFetcher;

    /** @var \EntityWithFamilyValuesFillerInterface */
    protected $productModelValuesFiller;

    /** @var GetTypeOfVariant */
    protected $getType ='';

    /** @var BolgattyDemoConnector */
    protected $connectorService;

    /**
     * @param NormalizerInterface                    $normalizer
     * @param \AttributeRepositoryInterface          $attributeRepository
     * @param \ObjectDetacherInterface               $detacher
     * @param \BulkMediaFetcher                      $mediaFetcher
     * @param \EntityWithFamilyValuesFillerInterface $productModelValuesFiller
     */
    public function __construct(
        NormalizerInterface $normalizer,
        \AttributeRepositoryInterface $attributeRepository,
        \ObjectDetacherInterface $detacher,
        \BulkMediaFetcher $mediaFetcher,
        $productModelValuesFiller,
        BolgattyDemoConnector $connectorService
    ) {
        $this->normalizer = $normalizer;
        $this->detacher = $detacher;
        $this->attributeRepository = $attributeRepository;
        $this->mediaFetcher = $mediaFetcher;
        $this->productModelValuesFiller = $productModelValuesFiller;
        $this->connectorService = $connectorService;
    }

    /**
     * {@inheritdoc}
     */
    public function process($productModel)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $structure = $parameters->get('filters')['structure'];
        $channel = $this->connectorService->getChannelData($structure['scope']);

        if ($this->productModelValuesFiller && $productModel instanceof \ProductModelInterface) {
            $this->productModelValuesFiller->fillMissingValues($productModel);
        }

        $productStandard = $this->normalizer->normalize(
            $productModel,
            'standard',
            [
                'filter_types' => ['pim.transform.product_value.structured'],
                'channels' => [$channel->getCode()],
                'locales'  => array_intersect(
                    $channel->getLocaleCodes(),
                    $parameters->get('filters')['structure']['locales']
                ),
            ]
        );

        if ($this->areAttributesToFilter($parameters)) {
            $attributesToFilter = $this->getAttributesToFilter($parameters);
            $productStandard['values'] = $this->filterValues($productStandard['values'], $attributesToFilter);
        }

        if ($parameters->has('with_media') && $parameters->get('with_media')) {
            $directory = $this->stepExecution->getJobExecution()->getExecutionContext()
                ->get(\JobInterface::WORKING_DIRECTORY_PARAMETER);

            $this->fetchMedia($productModel, $directory);
        } else {
            $mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();
            $productStandard['values'] = array_filter(
                $productStandard['values'],
                function ($attributeCode) use ($mediaAttributes) {
                    return !in_array($attributeCode, $mediaAttributes);
                },
                ARRAY_FILTER_USE_KEY
            );
        }
        
        return $productStandard;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(\StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
