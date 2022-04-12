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

    /**
     * @param ProductQueryBuilderFactoryInterface   $pqbFactory
     * @param ChannelRepositoryInterface            $channelRepository
     * @param MetricConverter                       $metricConverter
     * @param BolgattyDemoConnector                 $connectorService
     * @param \FQCNResolver                         $FQCNResolver
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        MetricConverter $metricConverter,
        BolgattyDemoConnector $connectorService,
        \FQCNResolver $FQCNResolver
    ) {
        parent::__construct($pqbFactory, $channelRepository, $metricConverter);

        $this->connectorService     = $connectorService;
        $this->FQCNResolver = $FQCNResolver;
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
        dump($filters, $channel);
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
        return $product;
    }
}
