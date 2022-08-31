<?php

namespace BolgattyDemoBundle\Connector\Reader;

use Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use BolgattyDemoBundle\Services\BolgattyDemoConnector;
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

$classLoader = new \BolgattyDemoBundle\Listener\ClassDefinationForCompatibility();
$classLoader->customLoaderSystem();

class ProductHistoryCsvReader extends ProductReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var MetricConverter */
    protected $metricConverter;

    /** @var \VersionRepositoryInterface */
    protected $versionRepository;

    /** @var \FQCNResolver */
    protected $FQCNResolver;

    public $connectorService;

    /**
     * @param ProductQueryBuilderFactoryInterface   $pqbFactory
     * @param ChannelRepositoryInterface            $channelRepository
     * @param MetricConverter                       $metricConverter
     * @param BolgattyDemoConnector                 $connectorService
     * @param \VersionRepositoryInterface           $versionRepository
     * @param \FQCNResolver                         $FQCNResolver
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        MetricConverter $metricConverter,
        BolgattyDemoConnector $connectorService,
        \VersionRepositoryInterface $versionRepository,
        \FQCNResolver $FQCNResolver
    ) {
        parent::__construct($pqbFactory, $channelRepository, $metricConverter);

        $this->connectorService     = $connectorService;
        $this->versionRepository = $versionRepository;
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

        // get all users
        $users = $this->getAllUsers();

        // get filter user name
        $this->userName = $this->getSelectedUser($this->user, $users);

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
        $currenProductCode = $type = '';
        $isVariant = false;
        if (method_exists($product, 'getCode')) {
            $type =  'product_model';
            $currenProductCode = $product->getCode();
        } else {
            $type =  'product';
            $currenProductCode = $product->getIdentifier();
            $isVariant = $product->getParent() ? true : false;
        }

        $allProductDetailsWithLogEntry = $this->versionRepository->findBy(
            [
                'resourceName' => $this->FQCNResolver->getFQCN($type),
            ]
        );

        $type = ($type == 'product_model') ? 'Product Model' : 'Product';

        $productVersioningHistory = [];

        foreach ($allProductDetailsWithLogEntry as $productDetailsWithLogEntry) {
            $snapshot = $productDetailsWithLogEntry->getSnapshot();

            // skip if not have changes history
            if (empty($snapshot) || ($snapshot == null)) {
                continue;
            }

            $code = $snapshot['code'] ?? $snapshot['sku'];

            if ($code == $currenProductCode) {
                // get product changes in product
                $changeset = $productDetailsWithLogEntry->getChangeset();
                $version = $productDetailsWithLogEntry->getVersion();
                $author = $productDetailsWithLogEntry->getAuthor();
                $loggedAt = $productDetailsWithLogEntry->getLoggedAt();

                // get author full username
                $fullName = $author != "system" ? $this->getUser($author) : $author;

                // write version history data for all user, system user & selected user
                if (($this->userName == "All") || (ucfirst($fullName) == $this->userName)) {

                    // set parent for variant products
                    $parentCode = null;
                    if ($isVariant) {
                        $parentCode =  $product->getParent()->getCode() ? $product->getParent()->getCode() : null;
                    }

                    // set data
                    $data = [
                        // 'parent_code' => $parentCode,
                        'entity' => $type,
                        'version' => $version,
                        'author' => ucfirst($fullName),
                        'logged_at' => $loggedAt->format('Y-m-d')." ".$loggedAt->format('h:i:s A')
                    ];
                } else {
                    continue;
                }

                $productVersioningHistory[$version] = array_merge($data, $changeset);
                // short latest version wise history
                krsort($productVersioningHistory);
            }
        }

        return $productVersioningHistory;
    }

    public function getSelectedUser($userId, $users)
    {
        foreach ($users as $id => $username) {
            if ($userId == $id) {
                return $username;
            }
        }
    }

    public function getAllUsers()
    {
        $jsonData = $this->connectorService->getAllUsers()->getContent();

        $usersObj = json_decode($jsonData);

        return $usersObj->data;
    }

    public function getUser($username)
    {
        $jsonData = $this->connectorService->getUserByUserName($username);
        $jsonData = $jsonData->getContent();
        $userObj = json_decode($jsonData);

        return $userObj->data->$username;
    }
    
}
