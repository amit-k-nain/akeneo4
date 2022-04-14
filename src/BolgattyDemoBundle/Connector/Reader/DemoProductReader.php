<?php

namespace BolgattyDemoBundle\Connector\Reader;

use Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use BolgattyDemoBundle\Services\VersionHistoryExportConnector;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\ProductReader;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Csv\ProductModelReader;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
// use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use BolgattyDemoBundle\Services\BolgattyDemoConnector;

$classLoader = new \BolgattyDemoBundle\Listener\ClassDefinationForCompatibility();
$classLoader->customLoaderSystem();

class DemoProductReader extends ProductReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var MetricConverter */
    protected $metricConverter;

    /** @var \FQCNResolver */
    protected $FQCNResolver;

    public $connectorService;

    protected $updater;

    protected $saver;

    /**
     * @param ProductQueryBuilderFactoryInterface   $pqbFactory
     * @param ChannelRepositoryInterface            $channelRepository
     * @param MetricConverter                       $metricConverter
     * @param BolgattyDemoConnector                 $connectorService
     * @param \FQCNResolver                         $FQCNResolver
     * @param $updater
     * @param $saver
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        MetricConverter $metricConverter,
        BolgattyDemoConnector $connectorService,
        \FQCNResolver $FQCNResolver,
        $updater,
        $saver
    ) {
        parent::__construct($pqbFactory, $channelRepository, $metricConverter);
        $this->pqbFactory = $pqbFactory;
        $this->connectorService = $connectorService;
        $this->FQCNResolver = $FQCNResolver;
        $this->updater = $updater;
        $this->saver = $saver;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $channel = $this->getConfiguredChannel();
        $filters = $this->getConfiguredFilters();
        
        // get job filters
        $this->parameters = $this->stepExecution->getJobParameters();

        // get selecter user id
        $this->user = ($this->parameters->has('user')
        && $this->parameters->get('user') != "") ? $this->parameters->get('user') : 0;

        $this->products = $this->getProductsCursor($filters, $channel);

        $this->firstRead = true;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $product = null;

        if ($this->products->valid()) {
            if (!$this->firstRead) {
                $this->products->next();
            }
            $product = $this->products->current();
        }
        
        if (null !== $product) {
            $productHistory = $this->getProdductVersionHistory($product);
            if (!empty($productHistory)) {
                $product->versionHistory = $productHistory;
            }
            $this->stepExecution->incrementSummaryInfo('read');
        }
        
        $this->firstRead = false;

        return $product;
    }

    public function getProdductVersionHistory($product)
    {
        foreach ($product->getValues() as $key => $value) {
            $key = explode('-',$key);
            $attribute = $this->connectorService->getAttributeByCode($key[0] ?? $key[0]);
            if($attribute->getType() === "pim_catalog_multiselect" || $attribute->getType() === "pim_catalog_simpleselect") {
                $currentData = $value->getData();
                $newValue = $this->connectorService->getAttrOptionByAttrCode($attribute->getId());
                $newValue = $newValue[array_rand($newValue)];
                if(gettype($currentData) == "array") {
                    array_push($currentData,$newValue);
                } else {
                    $currentData = $newValue;
                }

                $this->updater->setData($product, $key[0], $currentData, ['locale' => null, 'scope' => null]);

                $this->saver->save($product);
            }
        }

        return $product;
    }

    function generateRandomString($length = 15) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
    
}
