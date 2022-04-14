<?php

namespace BolgattyDemoBundle\Services;

use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BolgattyDemoConnector
{
    public const SECTION = 'bolgatty_demo_connector';

    private $em;
    private $attribute;
    private $attributeOption;
    private $category;
    private $router;
    private $liip;
    private $familyVariant;
    private $channel;
    private $productModel;
    private $product;
    private $stepExecution;
    private $settings = [];
    private $imageAttributeCodes = [];
    private $categoryLabels = [];
    protected $uploadDir;
    protected $storer;
    public $locale = 'en_US';

    public function __construct(
        EntityManager $em,
        $attribute,
        $attributeOption,
        $category,
        $router,
        $liip,
        $familyVariant,
        $channel,
        $productModel,
        $product,
        $storer,
        $uploadDir,
        $locale
    ) {
        $this->em = $em;
        $this->attribute = $attribute;
        $this->attributeOption = $attributeOption;
        $this->category = $category;
        $this->router = $router;
        $this->liip = $liip;
        $this->familyVariant = $familyVariant;
        $this->channel = $channel;
        $this->productModel = $productModel;
        $this->product = $product;
        $this->storer = $storer;
        $this->uploadDir = !empty($uploadDir) ? $uploadDir : sys_get_temp_dir();
        $this->locale = $locale;
    }

    /**
     * @param \StepExecution $stepExecution
     */
    public function setStepExecution(\StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function getSelectAttributeCodes($attributeType)
    {
        return $this->attribute->getAttributeCodesByType(
            $attributeType
        );
    }

    public function getOptionByCode($code)
    {
        return $this->attributeOption->findOneByIdentifier(
            $code
        );
    }

    public function getAttrOptionByAttrCode($attrCode)
    {
        $allOptionCode = [];
        $attributeOption = $this->attributeOption->getOptions('en_US',$attrCode);
        if(!empty($attributeOption['results'])){
            foreach ($attributeOption['results'] as $key => $data) {
                $optionObj = $this->attributeOption->getOption($data['id'],$attrCode);
                $attOptionCode = $optionObj->getCode();
                array_push($allOptionCode,$attOptionCode);
            }
        }
        
        return $allOptionCode;
    }

    public function getAttributeLabelByCode($code)
    {
        $attr = $this->getAttributeByCode(
            $code
        );

        if ($attr == null) {
            return $code;
        }

        $locale = $this->getLocale();
        $attr->setLocale($locale);

        return $attr->getTranslation()->getLabel();
    }

    public function getLocale()
    {
        $parameters = $this->stepExecution->getJobParameters()->all();
        if ($parameters['filters']['structure']['locale']) {
            $this->locale = $parameters['filters']['structure']['locale'][0];
        }

        return $this->locale;
    }

    public function getUpdatedLable($label,$key)
    {
        $updatedLabel = $label;

        if (sizeof($key) > 1) {
            unset($key[0]);
            krsort($key);
            foreach ($key as $value) {
                $updatedLabel .= " ".$value;
            }
        }

        return $updatedLabel;
    }

    public function getAttributeByCode($code)
    {
        return $this->attribute->findOneByIdentifier(
            $code
        );
    }

    public function getIdentifierAttr()
    {
        $result = $this->attribute->getIdentifier()->getCode();

        return $result;
    }
}
