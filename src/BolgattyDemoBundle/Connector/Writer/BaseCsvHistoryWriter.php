<?php

namespace BolgattyDemoBundle\Connector\Writer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;
use BolgattyDemoBundle\Services\BolgattyDemoConnector;

$classLoader = new \BolgattyDemoBundle\Listener\ClassDefinationForCompatibility();
$classLoader->customLoaderSystem();

class BaseCsvHistoryWriter extends \AbstractItemMediaWriter implements
    \ItemWriterInterface,
    \InitializableInterface,
    \FlushableInterface,
    \StepExecutionAwareInterface
{
    /** @var \FlatItemBufferFlusher */
    protected $flusher;

    /** @var \BufferFactory */
    protected $bufferFactory;

    /** @var \FlatItemBuffer */
    protected $flatRowBuffer;

    /** @var \AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var \FileExporterPathGeneratorInterface */
    protected $fileExporterPath;

    /** @var string[] */
    protected $mediaAttributeTypes;

    /** @var ConnectorService */
    protected $connectorService;

    /** @var Filesystem */
    protected $localFs;

    /** @var \StepExecution */
    protected $stepExecution;

    /** @var \AttributeConverterInterface */
    protected $localizer;

    /** @var bool */
    protected $withHeader = false;

    /** @var bool */
    protected $newData = false;

    /** @var \JobParameters $parameters */
    protected $parameters;

    /** @var array */
    protected $writtenFiles = [];

    /** @var bool */
    protected $exportVariantAfterModel;

    /** @var bool */
    protected $productExportStarted = false;

    /** @var int */
    protected $variantProductCount = 0;

    public $locale = 'en_US';

    /**
     * @param \BufferFactory                      $bufferFactory
     * @param \FlatItemBufferFlusher              $flusher
     * @param \AttributeRepositoryInterface       $attributeRepository
     * @param \FileExporterPathGeneratorInterface $fileExporterPath
     * @param \AttributeConverterInterface        $localizer
     * @param array                               $mediaAttributeTypes
     * @param BolgattyDemoConnector               $connectorService
     * @param string                              $locale
     */
    public function __construct(
        \BufferFactory $bufferFactory,
        \FlatItemBufferFlusher $flusher,
        \AttributeRepositoryInterface $attributeRepository,
        \FileExporterPathGeneratorInterface $fileExporterPath,
        \AttributeConverterInterface $localizer,
        array $mediaAttributeTypes,
        BolgattyDemoConnector $connectorService,
        $locale 
    ) {
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
        $this->attributeRepository = $attributeRepository;
        $this->fileExporterPath = $fileExporterPath;
        $this->mediaAttributeTypes = $mediaAttributeTypes;
        $this->localizer = $localizer;
        $this->localFs = new Filesystem();
        $this->connectorService = $connectorService;
        $this->locale = $locale;
    }

    /**
      * {@inheritdoc}
      */
    public function initialize()
    {
        if (null === $this->flatRowBuffer) {
            $this->flatRowBuffer = $this->bufferFactory->create();
        }

        $this->parameters = $this->stepExecution->getJobParameters();
        
        $this->connectorService->setStepExecution($this->stepExecution);
        
        if ($this->parameters->get('filters')['structure']['locale']) {
            $this->locale = $this->parameters->get('filters')['structure']['locale'][0];
        }
        
        if ($this->parameters->has('exportVariantAfterModel')
            && $this->parameters->get('exportVariantAfterModel')
        ) {
            $this->exportVariantAfterModel = true;
        }

        $this->setConverterOptions($this->parameters);

        if ($this->parameters->has('withHeader')
            && $this->parameters->get('withHeader')
        ) {
            $this->withHeader = true;
        }

        $exportDirectory = dirname($this->getPath());

        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }
    }

    /**
      * {@inheritdoc}
      */
    public function write(array $items)
    {
        $flatItems = [];
        foreach ($items as $item) {
            $prodHeader = [];
            // code to format product
            $formatedProductVersionData = $this->formatProductData($item);

            // merge data into single array
            if (!empty($formatedProductVersionData)) {
                foreach ($formatedProductVersionData as $formatedProductData) {
                    if (is_array($formatedProductData) && !empty($formatedProductData)) {
                        foreach ($formatedProductData as $data) {
                            array_push($flatItems, $data);
                        }
                    }
                }
            }
        }

        $options = [];
        $options['withHeader'] = $this->withHeader;

        $this->flatRowBuffer->write($flatItems, $options);
    }


    /**
    * Format product data
    */
    public function formatProductData($product)
    {
        $productVersionsData = [];
        // attribute need to skip from product history export data
        $skippedAttr = ['code','identifier','family','family_variant','parent','groups','categories','enabled','values','created','updated','associations','quantified_associations','commonAttributes','allVariantAttributes'];

        foreach ($product as $key => $value) {
            if (in_array($key, $skippedAttr)) {
                continue;
            }

            $identifier = $this->getItemIdentifier($product);

            if (is_array($value) && sizeof($value) > 1) {
                while ($value) {
                    $formatterItem = [];
                    $versiosData = current($value);
                    $prodMulVerData = [];
                    // skip same fields for duplicate array fields
                    $verSkipKey = ['author','entity','logged_at','version','X_SELL-products','UPSELL-products','SUBSTITUTION-products','PACK-products'];

                    foreach ($versiosData as $key => $data) {
                        if (in_array($key, $verSkipKey)) {
                            continue;
                        }

                        // set attribute label instead of code
                        $label = $key;
                        $attrKeyData = explode('-', $key);
                        $label = $this->connectorService->getAttributeLabelByCode($attrKeyData[0]);
                        $label = $this->connectorService->getUpdatedLable($label,$attrKeyData);

                        // set field
                        $formatterItem['SKU Code'] = $identifier;
                        $formatterItem['Entity'] = $versiosData['entity'];
                        $formatterItem['Version Number'] = $versiosData['version'];
                        $formatterItem['User Name'] = $versiosData['author'];
                        $formatterItem['Logged At'] = $versiosData['logged_at'];
                        $formatterItem['Attribute Name'] = $label;

                        $keys = array_keys($formatterItem);

                        /* metric unit or currency case */
                        // added metric unit or currency with attribute value
                        if (sizeof($attrKeyData) > 1 && in_array($attrKeyData[0], $keys)) {
                            $key = $attrKeyData[0];
                            $oldData = $formatterItem[$key];
                            $formatterItem['Attribute Old Value'] = strip_tags($oldData.' '.$data['old']);
                            $formatterItem['Attribute New Value'] = strip_tags($oldData.' '.$data['new']);
                            $prodMulVerData[] = $formatterItem;
                            continue;
                        }

                        /* simple attribute case */
                        if (is_array($data) && (sizeof($data) > 1)) {
                            $formatterItem['Attribute Old Value'] = strip_tags($data['old']);
                            $formatterItem['Attribute New Value'] = strip_tags($data['new']);
                        } else {
                            $formatterItem[$key] = $data;
                        }

                        $prodMulVerData[] = $formatterItem;
                    }
                    $productVersionsData[] = $prodMulVerData;

                    if (!next($value)) {
                        break;
                    }
                }
            }
            // product version history code for the first time product create
            else {
                $formatterItem = [];
                $prodMulVerData = [];
                // skip same fields for duplicate array fields
                $verSkipKey = ['author','entity','logged_at','version','X_SELL-products','UPSELL-products','SUBSTITUTION-products','PACK-products'];

                $key = array_key_first($value);

                $value = $value[$key];

                foreach ($value as $key => $data) {
                    if (in_array($key, $verSkipKey)) {
                        continue;
                    }

                    // set attribute label instead of code
                    $label = $key;
                    $attrKeyData = explode('-', $key);
                    $label = $this->connectorService->getAttributeLabelByCode($attrKeyData[0]);
                    $label = $this->connectorService->getUpdatedLable($label,$attrKeyData);

                    // set field
                    $formatterItem['SKU Code'] = $identifier;
                    $formatterItem['Entity'] = $value['entity'];
                    $formatterItem['Version Number'] = $value['version'];
                    $formatterItem['User Name'] = $value['author'];
                    $formatterItem['Logged At'] = $value['logged_at'];
                    $formatterItem['Attribute Name'] = $label;

                    $keys = array_keys($formatterItem);

                    /* metric unit or currency case */
                    // added metric unit or currency with attribute value
                    if (sizeof($attrKeyData) > 1 && in_array($attrKeyData[0], $keys)) {
                        $key = $attrKeyData[0];
                        $oldData = $formatterItem[$key];
                        $formatterItem['Attribute Old Value'] = strip_tags($oldData.' '.$data['old']);
                        $formatterItem['Attribute New Value'] = strip_tags($oldData.' '.$data['new']);
                        $prodMulVerData[] = $formatterItem;
                        continue;
                    }

                    /* simple attribute case */
                    if (is_array($data) && (sizeof($data) > 1)) {
                        $formatterItem['Attribute Old Value'] = strip_tags($data['old']);
                        $formatterItem['Attribute New Value'] = strip_tags($data['new']);
                    } else {
                        $formatterItem[$key] = $data;
                    }

                    $prodMulVerData[] = $formatterItem;
                }

                $productVersionsData[] = $prodMulVerData;
            }
        }

        return $productVersionsData;
    }

    /**
    * Flush items into a file
    */
    public function flush()
    {
        if ($this->stepExecution->getStepName() === 'csv_product_model_history_export'
            && $this->exportVariantAfterModel
        ) {
            $this->stepExecution->incrementSummaryInfo('write', - $this->variantProductCount);
        }
        $this->negateExtraWrite();
        $this->flusher->setStepExecution($this->stepExecution);
        $writtenFiles = $this->flusher->flush(
            $this->flatRowBuffer,
            $this->getWriterConfiguration(),
            $this->getPath(),
            ($this->parameters->has('linesPerFile') ? $this->parameters->get('linesPerFile') : -1)
        );

        foreach ($writtenFiles as $writtenFile) {
            $this->writtenFiles[$writtenFile] = basename($writtenFile);
        }
    }

    /**
    * Get the file path in which to write the data
    *
    * @param array $placeholders
    *
    * @return string
    */
    public function getPath(array $placeholders = [])
    {
        $filePath = '';
        if ($this->parameters != null && $this->parameters->has('filePath')
            && $this->parameters->get('filePath')
        ) {
            $filePath = $this->parameters->get('filePath');
        }

        if (false !== strpos($filePath, '%')) {
            $datetime = $this->stepExecution->getStartTime()->format($this->datetimeFormat);
            $defaultPlaceholders = ['%datetime%' => $datetime, '%job_label%' => ''];
            $jobExecution = $this->stepExecution->getJobExecution();

            if (isset($placeholders['%job_label%'])) {
                $placeholders['%job_label%'] = $this->sanitize($placeholders['%job_label%']);
            } elseif (null !== $jobExecution->getJobInstance()) {
                $defaultPlaceholders['%job_label%'] = $this->sanitize($jobExecution->getJobInstance()->getLabel());
            }
            $replacePairs = array_merge($defaultPlaceholders, $placeholders);
            $filePath = strtr($filePath, $replacePairs);
        }

        return $filePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(\StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    protected function getWriterConfiguration()
    {
        return [
            'type'           => 'csv',
            'fieldDelimiter' => $this->parameters->get('delimiter'),
            'fieldEnclosure' => $this->parameters->get('enclosure'),
            'shouldAddBOM'   => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemIdentifier(array $product)
    {
        return isset($product['identifier']) ? $product['identifier'] : $product['code'];
    }

    /**
    * Replace [^A-Za-z0-9\.] from a string by '_'
    *
    * @param string $value
    *
    * @return string
    */
    protected function sanitize($value)
    {
        return preg_replace('#[^A-Za-z0-9\.]#', '_', $value);
    }

    /**
     * @return array
     */
    protected function setConverterOptions(\JobParameters $parameters)
    {
        if ($parameters->has('decimalSeparator')) {
            $this->converterOptions['decimal_separator'] = $parameters->get('decimalSeparator');
        }

        if ($parameters->has('dateFormat')) {
            $this->converterOptions['date_format'] = $parameters->get('dateFormat');
        }

        if ($parameters->has('ui_locale')) {
            $this->converterOptions['locale'] = $parameters->get('ui_locale');
        }
    }

    /**
     * Remove extra write
     */
    protected function negateExtraWrite()
    {
        /* correct write summary log in same file for product as well as models */
        if ($this->flatRowBuffer->key()
            && !$this->productExportStarted
        ) {
            $this->stepExecution->incrementSummaryInfo('write', -1*$this->flatRowBuffer->key());
            $this->productExportStarted = true;
        }
    }
}
