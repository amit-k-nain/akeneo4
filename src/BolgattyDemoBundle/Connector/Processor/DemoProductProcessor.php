<?php

namespace BolgattyDemoBundle\Connector\Processor;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

$classLoader = new \BolgattyDemoBundle\Listener\ClassDefinationForCompatibility();
$classLoader->customLoaderSystem();

/**
 * Product processor to process and normalize entities to the standard format
 */
class DemoProductProcessor extends \PimProductProcessor
{
    public const FIELD_VARIANT_ALL_ATTRIBUTES = 'allVariantAttributes';
    public const FIELD_COMMON_ATTRIBUTES = 'commonAttributes';
    public const FIELD_PARENT = 'parent';

    public $stepExecution;

    public $exportVariantAfterModel;

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $structure = $parameters->get('filters')['structure'];
        $channel = $this->channelRepository->findOneByIdentifier($structure['scope']);

        if (isset($this->productValuesFiller) && $product instanceof \ProductInterface) {
            $this->productValuesFiller->fillMissingValues($product);
        }

        $productStandard = $this->normalizer->normalize(
            $product,
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

            $this->fetchMedia($product, $directory);
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

        if ($this->isVariantProduct($product) && null !== $product->getParent()) {
            $productStandard[self::FIELD_PARENT] = $product->getParent()->getCode();

            if ($product->getParent() && $product->getParent()->getParent()) {
                $productStandard[self::FIELD_PARENT] = $product->getParent()->getParent()->getCode();
            }

            $productStandard[self::FIELD_VARIANT_ALL_ATTRIBUTES] = $this->getVariantAttributes($product);
            $productStandard[self::FIELD_COMMON_ATTRIBUTES] = $this->getCommonAttributes($product);
        }

        return $productStandard;
    }

    /* get variant attributes of product */
    protected function getVariantAttributes($product)
    {
        $result = [];
        if (!empty($product->getFamilyVariant()) && !empty($product->getFamilyVariant()) && $product->getFamilyVariant()->getCode()) {
            $varattr = $product->getFamilyVariant()->getAxes()->getValues();

            foreach ($varattr as $attr) {
                $result[] = $attr->getCode();
            }
        }

        return $result;
    }

    /* get common attributes of product */
    protected function getCommonAttributes($product)
    {
        $result = [];
        if (!empty($product->getFamilyVariant()) && $product->getFamilyVariant() && $product->getFamilyVariant()->getCode()) {
            $varattr = $product->getFamilyVariant()->getAttributes();
            $commonAttributes = $product->getFamilyVariant()->getCommonAttributes();

            foreach ($varattr as $attr) {
                $result[] = $attr->getCode();
            }

            foreach ($commonAttributes as $commanAttr) {
                if (!in_array($commanAttr->getCode(), $result)) {
                    $result[] = $commanAttr->getCode();
                }
            }
        }

        return $result;
    }

    protected function isVariantProduct($product)
    {
        $flag = false;
        if (method_exists($product, 'isVariant')) {
            $flag = $product->isVariant();
        } else {
            $flag = ($product instanceof \VariantProductInterface);
        }

        return $flag;
    }

    /**
    * {@inheritdoc}
    */
    public function setStepExecution(\StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
        $this->exportVariantAfterModel = $stepExecution->getJobParameters()->get('exportVariantAfterModel');
    }
}
