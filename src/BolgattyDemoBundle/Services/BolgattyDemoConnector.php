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

    protected $yesNoType = ['Yes', 'yes', 'Y', 'True', '1' ];


    protected function imageStorer($filePath)
    {
        $filePath = $this->getImagePath($filePath);
        if ($filePath != null) {
            $rawFile = new \SplFileInfo($filePath);
            $file = $this->storer->store($rawFile, \FileStorage::CATALOG_STORAGE_ALIAS);
        }

        return $filePath;
    }

    protected function getImagePath($filePath)
    {
        $fileName = explode('/', $filePath);
        $fileName = explode('?', $fileName[count($fileName)-1])[0];

        $localpath = $this->uploadDir."/tmpstorage/".$fileName;

        if (!file_exists(dirname($localpath))) {
            mkdir(dirname($localpath), 0777, true);
        }

        $context = stream_context_create(
            array(
                "http" => array(
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                )
            )
        );

        try {
            $check = file_put_contents($localpath, file_get_contents($filePath, false, $context));
        } catch (\Throwable $th) {
            $this->stepExecution->addWarning('Unable to get image', [], new \DataInvalidItem(['filepath' => $filePath]));
            $localpath = null;
        }

        return $localpath;
    }


    /**
     * Get Catgory Data
     *
     * @param string $code
     * @param string $locale
     * @param string $exportRootCategory
     * @param string $exportCategoryLabel
     *
     * @return array
     */
    public function getCategoryData($code, $locale, $exportRootCategory, $exportCategoryLabel)
    {
        $repository = $this->category;
        $value = $repository->findOneBy(['code' => $code]);
        if ($value->getTranslations()->getValues()) {
            if ($exportCategoryLabel != 'false') {
                foreach ($value->getTranslations()->getValues() as $catdata) {
                    if ($catdata->getLocale() == $locale) {
                        if ($value->isRoot()) {
                            if ($exportRootCategory != 'false') {
                                $this->categoryLabels [] = $catdata->getLabel();
                            }
                        } else {
                            $this->categoryLabels [] = $catdata->getLabel();
                        }
                    }
                }
            } else {
                if ($value->isRoot()) {
                    if ($exportRootCategory != 'false') {
                        $this->categoryLabels [] = $value->getCode();
                    }
                } else {
                    $this->categoryLabels [] = $value->getCode();
                }
            }

            if (!$value->isRoot()) {
                return $this->getCategoryData($value->getParent()->getCode(), $locale, $exportRootCategory, $exportCategoryLabel);
            }
        }

        $categoryLabels = $this->categoryLabels;
        $this->categoryLabels = [];

        return $categoryLabels;
    }

    public function getHostSettings($section = self::SECTION)
    {
        $repo = $this->em->getRepository('OroConfigBundle:ConfigValue');
        if (empty($settings)) {
            $configs = $repo->findBy([
                'section' => $section
                ]);

            $settings = $this->indexHostValuesByName($configs);
        }
        return $settings;
    }

    private function indexHostValuesByName($values)
    {
        $result = [];
        foreach ($values as $value) {
            $result[$value->getName()] = $value->getValue();
        }
        return $result;
    }

    public function getImageAttributeCodes()
    {
        if (empty($this->imageAttributeCodes)) {
            $this->imageAttributeCodes = $this->attribute->getAttributeCodesByType(
                'pim_catalog_image'
            );
        }

        return $this->imageAttributeCodes;
    }

    public function generateImageUrl($filename, $host = null)
    {
        $filename = urldecode($filename);
        $serverDetail = $this->getHostSettings();
        $host = !empty($serverDetail['hostname']) ? $serverDetail['hostname'] : null;
        $scheme = !empty($serverDetail['scheme']) ? $serverDetail['scheme'] : null;
        if ($host) {
            $context = $this->router->getContext();
            $context->setHost($host);
            $context->setScheme($scheme);
        }
        $request = new Request();
        try {
            $url = $this->liip->filterAction($request, $filename, 'preview')->getTargetUrl();
        } catch (\Exception $e) {
            $url  = '';
        }

        return $url;
    }

    protected function formatApiUrl($url)
    {
        $url = str_replace(['http://'], ['https://'], $url);

        return \rtrim($url, '/');
    }

    public function getFamilyByFamilyVariant($familyVariant)
    {
        return $this->familyVariant->findOneByIdentifier($familyVariant);
    }

    public function getChannelData($scope)
    {
        $channelRepo = $this->channel->findOneByIdentifier([
            $scope
            ]);

        return $channelRepo;
    }

    public function getProductModelVariants(string $code): array
    {
        $products = [];
        $productModelRepo = $this->productModel;
        $results = $productModelRepo->createQueryBuilder('m')
                ->select('p.identifier')
                ->leftJoin('m.products', 'p')
                ->where('m.code = :code')
                ->setParameter('code', $code)
                ->getQuery()->getResult();

        if (!empty($results)) {
            foreach ($results as $result) {
                $products[] = $this->getProductByIdentifier($result['identifier']);
            }
        }

        return $products;
    }

    public function getProductByIdentifier($identifier)
    {
        return $this->product->findOneByIdentifier($identifier);
    }
}
