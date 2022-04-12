<?php

namespace BolgattyDemoBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class ClassDefinationForCompatibility
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->checkVersionAndCreateClassAliases();
    }

    public function createUserSystem(ConsoleCommandEvent $event)
    {
        $this->checkVersionAndCreateClassAliases();
    }

    public function customLoaderSystem()
    {
        if (class_exists('Akeneo\Platform\CommunityVersion')) {
            $versionClass = new \Akeneo\Platform\CommunityVersion();
        } elseif (class_exists('Pim\Bundle\CatalogBundle\Version')) {
            $versionClass = new \Pim\Bundle\CatalogBundle\Version();
        }

        $version = $versionClass::VERSION;
        if (version_compare($version, '3.0', '>')) {
            $this->akeneoVersion3();
        } else {
            $this->akeneoVersion2();
        }
    }

    public function checkVersionAndCreateClassAliases()
    {
        if (class_exists('Akeneo\Platform\CommunityVersion')) {
            $versionClass = new \Akeneo\Platform\CommunityVersion();
        } elseif (class_exists('Pim\Bundle\CatalogBundle\Version')) {
            $versionClass = new \Pim\Bundle\CatalogBundle\Version();
        }

        $version = $versionClass::VERSION;
        if (version_compare($version, '3.0', '>=')) {
            $this->akeneoVersion3();
        } else {
            $this->akeneoVersion2();
        }
    }

    public function akeneoVersion3()
    {
        $AliaseNames = [
            'ObjectNotFoundException'                   =>  'Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException',
            'AttributeConverterInterface'               =>  'Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface',
            'ProductModelInterface'                     =>  'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
            'AttributeFilterInterface'                  =>  'Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface',
            'ProductModelRepositoryInterface'           =>  'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface',
            'ProductRepositoryInterface'                =>  'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
            'ArrayConverterInterface'                   =>  'Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface',
            'ConverterInterface'                        =>  'Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface',
            'AssociationColumnsResolver'                =>  'Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver',
            'EntityWithValuesFilter'                    =>  'Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\EntityWithValuesFilter',
            'FilterInterface'                           =>  'Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface',
            'Version'                                   =>  'Akeneo\Tool\Component\Versioning\Model\Version',
            'UserContext'                               =>  'Akeneo\UserManagement\Bundle\Context\UserContext',
            'CollectionFilterInterface'                 =>  'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
            'ObjectFilterInterface'                     =>  'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',
            'FQCNResolver'                              =>  'Akeneo\Pim\Enrichment\Bundle\Resolver\FQCNResolver',
            'VersionRepositoryInterface'                =>  'Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface',
            'AttributeOptionRepository'                 =>  'Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeOptionRepository',
            'AttributeOptionType'                       =>  'Akeneo\Pim\Structure\Bundle\Form\Type\AttributeOptionType',
            'SimpleFactoryInterface'                    =>  'Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface',
            'SaverInterface'                            =>  'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
            'Operators'                                 =>  'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators',
            'AbstractProcessor'                         =>  'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor',
            'AttributeOptionInterface'                  =>  'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
            'AttributeInterface'                        =>  'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
            'AttributeOption'                           =>  'Akeneo\Pim\Structure\Component\Mode\AttributeOption',
            'AttributeTypes'                            =>  'Akeneo\Pim\Structure\Component\AttributeTypes',
            'AttributeRepositoryInterface'              =>  'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
            'CompletenessManager'                       =>  'Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager',
            'ConstraintCollectionProviderInterface'     =>  'Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface',
            'ChannelRepositoryInterface'                =>  'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
            'DefaultValuesProviderInterface'            =>  'Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface',
            'DatabaseProductReader'                     =>  'Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\ProductReader',
            'DataInvalidItem'                           =>  'Akeneo\Tool\Component\Batch\Item\DataInvalidItem',
            'FileStorage'                               =>  'Akeneo\Pim\Enrichment\Component\FileStorage',
            'FileStorerInterface'                       =>  'Akeneo\Tool\Component\FileStorage\File\FileStorerInterface',
            'FilterStructureLocale'                     =>  'Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\FilterStructureLocale',
            'InitializableInterface'                    =>  'Akeneo\Tool\Component\Batch\Item\InitializableInterface',
            'ItemReaderInterface'                       =>  'Akeneo\Tool\Component\Batch\Item\ItemReaderInterface',
            'ItemWriterInterface'                       =>  'Akeneo\Tool\Component\Batch\Item\ItemWriterInterface',
            'JobInterface'                              =>  'Akeneo\Tool\Component\Batch\Job\JobInterface',
            'MetricConverter'                           =>  'Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter',
            'ObjectUpdaterInterface'                    =>  'Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface',
            'ProductInterface'                          =>  'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'ProductQueryBuilderFactoryInterface'       =>  'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
            'SearchableRepositoryInterface'             =>  'Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface',
            'StepExecution'                             =>  'Akeneo\Tool\Component\Batch\Model\StepExecution',
            'StepExecutionAwareInterface'               =>  'Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface',
            'DefaultColumnSorter'                       =>  'Akeneo\Tool\Component\Connector\Writer\File\DefaultColumnSorter',
            'ColumnSorterInterface'                     =>  'Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface',
            'AssociationTypeRepositoryInterface'        =>  'Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface',
            'FieldSplitter'                             =>  'Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter',
            'LocalizerInterface'                        =>  'Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface',
            'AkeneoVersion'                             =>  'Akeneo\Platform\CommunityVersion',
            'AttributeConverterInterface'               =>  'Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface',
            'PimJob'                                    =>  'Akeneo\Tool\Component\Batch\Job\Job',
            'RuntimeErrorException'                     =>  'Akeneo\Tool\Component\Batch\Job\RuntimeErrorException',
            'JobInterruptedException'                   =>  'Akeneo\Tool\Component\Batch\Job\JobInterruptedException',
            'ExitStatus'                                =>  'Akeneo\Tool\Component\Batch\Job\ExitStatus',
            'BatchStatus'                               =>  'Akeneo\Tool\Component\Batch\Job\BatchStatus',
            'EventInterface'                            =>  'Akeneo\Tool\Component\Batch\Event\EventInterface',
            'JobExecutionEvent'                         =>  'Akeneo\Tool\Component\Batch\Event\JobExecutionEvent',
            'StepInterface'                             =>  'Akeneo\Tool\Component\Batch\Step\StepInterface',
            'ConstraintCollectionProviderInterface'     =>  'Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface',
            'JobInterface'                              =>  'Akeneo\Tool\Component\Batch\Job\JobInterface',
            'DefaultValuesProviderInterface'            =>  'Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface',
            'FlushableInterface'                        =>  'Akeneo\Tool\Component\Batch\Item\FlushableInterface',
            'InitializableInterface'                    =>  'Akeneo\Tool\Component\Batch\Item\InitializableInterface',
            'InvalidItemException'                      =>  'Akeneo\Tool\Component\Batch\Item\InvalidItemException',
            'ItemProcessorInterface'                    =>  'Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface',
            'ItemReaderInterface'                       =>  'Akeneo\Tool\Component\Batch\Item\ItemReaderInterface',
            'ItemWriterInterface'                       =>  'Akeneo\Tool\Component\Batch\Item\ItemWriterInterface',
            'JobRepositoryInterface'                    =>  'Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface',
            'StepExecution'                             =>  'Akeneo\Tool\Component\Batch\Model\StepExecution',
            'AbstractStep'                              =>  'Akeneo\Tool\Component\Batch\Step\AbstractStep',
            'StepExecutionAwareInterface'               =>  'Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface',
            'BaseReader'                                =>  'Akeneo\Pim\Enrichment\Component\Category\Connector\Reader\Database\CategoryReader',
            'CategoryRepositoryInterface'               =>  'Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface',
            'ChannelRepository'                         =>  'Akeneo\Channel\Bundle\Doctrine\Repository\ChannelRepository',
            'AbstractReader'                            =>  'Akeneo\Tool\Component\Connector\Reader\Database\AbstractReader',
            'FileInvalidItem'                           =>  'Akeneo\Tool\Component\Batch\Item\FileInvalidItem',
            'ArrayConverterInterface'                   =>  'Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface',
            'DataInvalidItem'                           =>  'Akeneo\Tool\Component\Batch\Item\DataInvalidItem',
            'CollectionFilterInterface'                 =>  'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
            'ObjectDetacherInterface'                   =>  'Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface',
            'PimProductProcessor'                       =>  'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\ProductProcessor',
            'AbstractProcessor'                         =>  'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor',
            'AttributeRepositoryInterface'              =>  'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
            'ChannelRepositoryInterface'                =>  'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
            'EntityWithFamilyValuesFillerInterface'     =>  'Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\EntityWithFamilyValuesFillerInterface',
            'BulkMediaFetcher'                          =>  'Akeneo\Tool\Component\Connector\Processor\BulkMediaFetcher',
            'MetricConverter'                           =>  'Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter',
            'Operators'                                 =>  'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators',
            'ProductFilterData'                         =>  'Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ProductFilterData',
            'Currency'                                  =>  'Akeneo\Channel\Component\Model\Currency',
            'JobInstance'                               =>  'Akeneo\Tool\Component\Batch\Model\JobInstance',
            'ProductQueryBuilderFactoryInterface'       =>  'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
            'CompletenessManager'                       =>  'Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager',
            'CategoryRepository'                        =>  'Akeneo\Tool\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository',
            'Datasource'                                =>  'Oro\Bundle\PimDataGridBundle\Datasource\Datasource',
            'DatagridRepositoryInterface'               =>  'Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface',
            'MassActionRepositoryInterface'             =>  'Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface',
            'HydratorInterface'                         =>  'Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface',
            'ObjectFilterInterface'                     =>  'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',
            'ChannelInterface'                          =>  'Akeneo\Channel\Component\Model\ChannelInterface',
            'JobParameters'                             =>  'Akeneo\Tool\Component\Batch\Job\JobParameters',
            'ProductInterface'                          =>  'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'ProductModelInterface'                     =>  'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
            'FamilyInterface'                           =>  'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
            'JobExecution'                              =>  'Akeneo\Tool\Component\Batch\Model\JobExecution',
            'FamilyController'                          =>  'Akeneo\Pim\Structure\Bundle\Controller\InternalApi\FamilyController',
            'FamilyUpdater'                             =>  'Akeneo\Pim\Structure\Component\Updater\FamilyUpdater',
            'SaverInterface'                            =>  'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
            'FamilyFactory'                             =>  'Akeneo\Pim\Structure\Component\Factory\FamilyFactory',
            'FamilyRepositoryInterface'                 =>  'Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface',
            'FileStorerInterface'                       =>  'Akeneo\Tool\Component\FileStorage\File\FileStorerInterface',
            'FileInfoRepositoryInterface'               =>  'Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface',
            'FileStorage'                               =>  'Akeneo\Pim\Enrichment\Component\FileStorage',
            'SimpleFactoryInterface'                    =>  'Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface',
            'ObjectUpdaterInterface'                    =>  'Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface',
            'IdentifiableObjectRepositoryInterface'     =>  'Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface',
            'AttributeFilterInterface'                  =>  'Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface',
            'FilterInterface'                           =>  'Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface',
            'JobLauncherInterface'                      =>  'Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface',
            'CommandLauncher'                           =>  'Akeneo\Tool\Component\Console\CommandLauncher',
            'IdentifiableObjectRepositoryInterface'     =>  'Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface',
            'OroToPimGridFilterAdapter'                 =>  'Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter',
            'FilterStructureLocale'                     =>  'Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\FilterStructureLocale',
            'VariantProductInterface'                   =>  'Akeneo\Pim\Enrichment\Component\Product\Model\VariantProductInterface',
            'AbstractItemMediaWriter'                   =>  'Akeneo\Tool\Component\Connector\Writer\File\AbstractItemMediaWriter',
            'ArchivableWriterInterface'                 =>  'Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface',
            'BufferFactory'                             =>  'Akeneo\Tool\Component\Buffer\BufferFactory',
            'FlatItemBufferFlusher'                     =>  'Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher',
            'FileExporterPathGeneratorInterface'        =>  'Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface',
            'EntityManagerClearerInterface'             =>  'Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface',
            'EntityWithFamilyInterface'                 =>  'Pim\Component\Catalog\Model\EntityWithFamilyInterface',
            'PimProcessor'                              =>  'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductProcessor',
            'TrackableItemReaderInterface'              =>  'Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface'
        ];

        foreach ($AliaseNames as $alias => $aliasPath) {
            if ((interface_exists($aliasPath) || class_exists($aliasPath)) && !class_exists($alias) && !interface_exists($alias)) {
                \class_alias($aliasPath, $alias);
            }
        }
    }

    public function akeneoVersion2()
    {
        $AliaseNames = [
            'ObjectNotFoundException'                   =>  'Pim\Component\Catalog\Exception\ObjectNotFoundException',
            'AttributeConverterInterface'               =>  'Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface',
            'ProductModelInterface'                     =>  'Pim\Component\Catalog\Model\ProductModelInterface',
            'AttributeFilterInterface'                  =>  'Pim\Component\Catalog\ProductModel\Filter\AttributeFilterInterface',
            'ProductModelRepositoryInterface'           =>  'Pim\Component\Catalog\Repository\ProductModelRepositoryInterface',
            'ProductRepositoryInterface'                =>  'Pim\Component\Catalog\Repository\ProductRepositoryInterface',
            'ArrayConverterInterface'                   =>  'Pim\Component\Connector\ArrayConverter\ArrayConverterInterface',
            'ConverterInterface'                        =>  'Pim\Component\Enrich\Converter\ConverterInterface',
            'AssociationColumnsResolver'                =>  'Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AssociationColumnsResolver',
            'EntityWithValuesFilter'                    =>  'Pim\Component\Catalog\Comparator\Filter\EntityWithValuesFilter',
            'FilterInterface'                           =>  'Pim\Component\Catalog\Comparator\Filter\FilterInterface',
            'Version'                                   =>  'Akeneo\Component\Versioning\Model\Version',
            'UserContext'                               =>  'Pim\Bundle\UserBundle\Context\UserContext',
            'CollectionFilterInterface'                 =>  'Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface',
            'ObjectFilterInterface'                     =>  'Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface',
            'FQCNResolver'                              =>  'Pim\Bundle\CatalogBundle\Resolver\FQCNResolver',
            'VersionRepositoryInterface'                =>  'Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface',
            'AttributeOptionRepository'                 =>  'Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeOptionRepository',
            'AttributeOptionType'                       =>  'Pim\Bundle\EnrichBundle\Form\Type\AttributeOptionType',
            'SimpleFactoryInterface'                    =>  'Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface',
            'SaverInterface'                            =>  'Akeneo\Component\StorageUtils\Saver\SaverInterface',
            'Operators'                                 =>  'Pim\Component\Catalog\Query\Filter\Operators',
            'AbstractProcessor'                         =>  'Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor',
            'AttributeOptionInterface'                  =>  'Pim\Component\Catalog\Model\AttributeOptionInterface',
            'AttributeInterface'                        =>  'Pim\Component\Catalog\Model\AttributeInterface',
            'AttributeOption'                           =>  'Pim\Bundle\CatalogBundle\Entity\AttributeOption',
            'AttributeTypes'                            =>  'Pim\Component\Catalog\AttributeTypes',
            'AttributeRepositoryInterface'              =>  'Pim\Component\Catalog\Repository\AttributeRepositoryInterface',
            'CompletenessManager'                       =>  'Pim\Component\Catalog\Manager\CompletenessManager',
            'ChannelRepositoryInterface'                =>  'Pim\Component\Catalog\Repository\ChannelRepositoryInterface',
            'ConstraintCollectionProviderInterface'     =>  'Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface',
            'DatabaseProductReader'                     =>  'Pim\Component\Connector\Reader\Database\ProductReader',
            'DataInvalidItem'                           =>  'Akeneo\Component\Batch\Item\DataInvalidItem',
            'DefaultValuesProviderInterface'            =>  'Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface',
            'FileStorage'                               =>  'Pim\Component\Catalog\FileStorage',
            'FileStorerInterface'                       =>  'Akeneo\Component\FileStorage\File\FileStorerInterface',
            'FilterStructureLocale'                     =>  'Pim\Component\Connector\Validator\Constraints\FilterStructureLocale',
            'InitializableInterface'                    =>  'Akeneo\Component\Batch\Item\InitializableInterface',
            'ItemReaderInterface'                       =>  'Akeneo\Component\Batch\Item\ItemReaderInterface',
            'ItemWriterInterface'                       =>  'Akeneo\Component\Batch\Item\ItemWriterInterface',
            'JobInterface'                              =>  'Akeneo\Component\Batch\Job\JobInterface',
            'MetricConverter'                           =>  'Pim\Component\Catalog\Converter\MetricConverter',
            'ProductInterface'                          =>  'Pim\Component\Catalog\Model\ProductInterface',
            'ObjectUpdaterInterface'                    =>  'Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface',
            'ProductQueryBuilderFactoryInterface'       =>  'Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface',
            'SearchableRepositoryInterface'             =>  'Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface',
            'StepExecution'                             =>  'Akeneo\Component\Batch\Model\StepExecution',
            'StepExecutionAwareInterface'               =>  'Akeneo\Component\Batch\Step\StepExecutionAwareInterface',
            'DefaultColumnSorter'                       =>  'Pim\Component\Connector\Writer\File\DefaultColumnSorter',
            'ColumnSorterInterface'                     =>  'Pim\Component\Connector\Writer\File\ColumnSorterInterface',
            'AssociationTypeRepositoryInterface'        =>  'Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface',
            'FieldSplitter'                             =>  'Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter',
            'LocalizerInterface'                        =>  'Akeneo\Component\Localization\Localizer\LocalizerInterface',
            'AkeneoVersion'                             =>  'Pim\Bundle\CatalogBundle\Version',
            'AttributeConverterInterface'               =>  'Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface',
            'PimJob'                                    =>  'Akeneo\Component\Batch\Job\Job',
            'RuntimeErrorException'                     =>  'Akeneo\Component\Batch\Job\RuntimeErrorException',
            'JobInterruptedException'                   =>  'Akeneo\Component\Batch\Job\JobInterruptedException',
            'BatchStatus'                               =>  'Akeneo\Component\Batch\Job\BatchStatus',
            'ExitStatus'                                =>  'Akeneo\Component\Batch\Job\ExitStatus',
            'EventInterface'                            =>  'Akeneo\Component\Batch\Event\EventInterface',
            'JobExecutionEvent'                         =>  'Akeneo\Component\Batch\Event\JobExecutionEvent',
            'StepInterface'                             =>  'Akeneo\Component\Batch\Step\StepInterface',
            'ConstraintCollectionProviderInterface'     =>  'Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface',
            'JobInterface'                              =>  'Akeneo\Component\Batch\Job\JobInterface',
            'DefaultValuesProviderInterface'            =>  'Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface',
            'FlushableInterface'                        =>  'Akeneo\Component\Batch\Item\FlushableInterface',
            'InitializableInterface'                    =>  'Akeneo\Component\Batch\Item\InitializableInterface',
            'InvalidItemException'                      =>  'Akeneo\Component\Batch\Item\InvalidItemException',
            'ItemProcessorInterface'                    =>  'Akeneo\Component\Batch\Item\ItemProcessorInterface',
            'ItemReaderInterface'                       =>  'Akeneo\Component\Batch\Item\ItemReaderInterface',
            'ItemWriterInterface'                       =>  'Akeneo\Component\Batch\Item\ItemWriterInterface',
            'JobRepositoryInterface'                    =>  'Akeneo\Component\Batch\Job\JobRepositoryInterface',
            'StepExecution'                             =>  'Akeneo\Component\Batch\Model\StepExecution',
            'AbstractStep'                              =>  'Akeneo\Component\Batch\Step\AbstractStep',
            'StepExecutionAwareInterface'               =>  'Akeneo\Component\Batch\Step\StepExecutionAwareInterface',
            'BaseReader'                                =>  'Pim\Component\Connector\Reader\Database\CategoryReader',
            'CategoryRepositoryInterface'               =>  'Akeneo\Component\Classification\Repository\CategoryRepositoryInterface',
            'ChannelRepository'                         =>  'Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ChannelRepository',
            'AbstractReader'                            =>  'Pim\Component\Connector\Reader\Database\AbstractReader',
            'FileInvalidItem'                           =>  'Akeneo\Component\Batch\Item\FileInvalidItem',
            'ArrayConverterInterface'                   =>  'Pim\Component\Connector\ArrayConverter\ArrayConverterInterface',
            'DataInvalidItem'                           =>  'Akeneo\Component\Batch\Item\DataInvalidItem',
            'CollectionFilterInterface'                 =>  'Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface',
            'ObjectDetacherInterface'                   =>  'Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface',
            'PimProductProcessor'                       =>  'Pim\Component\Connector\Processor\Normalization\ProductProcessor',
            'AbstractProcessor'                         =>  'Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor',
            'AttributeRepositoryInterface'              =>  'Pim\Component\Catalog\Repository\AttributeRepositoryInterface',
            'ChannelRepositoryInterface'                =>  'Pim\Component\Catalog\Repository\ChannelRepositoryInterface',
            'EntityWithFamilyValuesFillerInterface'     =>  'Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface',
            'BulkMediaFetcher'                          =>  'Pim\Component\Connector\Processor\BulkMediaFetcher',
            'MetricConverter'                           =>  'Pim\Component\Catalog\Converter\MetricConverter',
            'Operators'                                 =>  'Pim\Component\Catalog\Query\Filter\Operators',
            'ProductFilterData'                         =>  'Pim\Component\Connector\Validator\Constraints\ProductFilterData',
            'Currency'                                  =>  'Pim\Component\Catalog\Model\CurrencyInterface',
            'JobInstance'                               =>  'Akeneo\Component\Batch\Model\JobInstance',
            'ProductQueryBuilderFactoryInterface'       =>  'Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface',
            'CompletenessManager'                       =>  'Pim\Component\Catalog\Manager\CompletenessManager',
            'CategoryRepository'                        =>  'Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository',
            'Datasource'                                =>  'Pim\Bundle\DataGridBundle\Datasource\Datasource',
            'DatagridRepositoryInterface'               =>  'Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface',
            'MassActionRepositoryInterface'             =>  'Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface',
            'HydratorInterface'                         =>  'Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface',
            'ObjectFilterInterface'                     =>  'Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface',
            'ChannelInterface'                          =>  'Pim\Component\Catalog\Model\ChannelInterface',
            'JobParameters'                             =>  'Akeneo\Component\Batch\Job\JobParameters',
            'ProductInterface'                          =>  'Pim\Component\Catalog\Model\ProductInterface',
            'ProductModelInterface'                     =>  'Pim\Component\Catalog\Model\ProductModelInterface',
            'FamilyInterface'                           =>  'Pim\Component\Catalog\Model\FamilyInterface',
            'JobExecution'                              =>  'Akeneo\Component\Batch\Model\JobExecution',
            'FamilyController'                          =>  'Pim\Bundle\EnrichBundle\Controller\Rest\FamilyController',
            'FamilyUpdater'                             =>  'Pim\Component\Catalog\Updater\FamilyUpdater',
            'SaverInterface'                            =>  'Akeneo\Component\StorageUtils\Saver\SaverInterface',
            'FamilyFactory'                             =>  'Pim\Component\Catalog\Factory\FamilyFactory',
            'FamilyRepositoryInterface'                 =>  'Pim\Component\Catalog\Repository\FamilyRepositoryInterface',
            'FileStorerInterface'                       =>  'Akeneo\Component\FileStorage\File\FileStorerInterface',
            'FileInfoRepositoryInterface'               =>  'Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface',
            'FileStorage'                               =>  'Pim\Component\Catalog\FileStorage',
            'SimpleFactoryInterface'                    =>  'Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface',
            'ObjectUpdaterInterface'                    =>  'Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface',
            'IdentifiableObjectRepositoryInterface'     =>  'Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface',
            'AttributeFilterInterface'                  =>  'Pim\Component\Catalog\ProductModel\Filter\AttributeFilterInterface',
            'FilterInterface'                           =>  'Pim\Component\Catalog\Comparator\Filter\FilterInterface',
            'JobLauncherInterface'                      =>  'Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface',
            'CommandLauncher'                           =>  'Akeneo\Component\Console\CommandLauncher',
            'IdentifiableObjectRepositoryInterface'     =>  'Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface',
            'OroToPimGridFilterAdapter'                 =>  'Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter',
            'FilterStructureLocale'                     =>  'Pim\Component\Connector\Validator\Constraints\FilterStructureLocale',
            'VariantProductInterface'                   =>  'Pim\Component\Catalog\Model\VariantProductInterface',
            'AbstractItemMediaWriter'                   =>  'Pim\Component\Connector\Writer\File\AbstractItemMediaWriter',
            'ArchivableWriterInterface'                 =>  'Pim\Component\Connector\Writer\File\ArchivableWriterInterface',
            'BufferFactory'                             =>  'Akeneo\Component\Buffer\BufferFactory',
            'FlatItemBufferFlusher'                     =>  'Pim\Component\Connector\Writer\File\FlatItemBufferFlusher',
            'FileExporterPathGeneratorInterface'        =>  'Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface',
            'EntityWithFamilyInterface'                 =>  'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface',
            'PimProcessor'                              =>  'Pim\Component\Connector\Processor\Denormalization\ProductProcessor',
            'TrackableItemReaderInterface'              =>  'Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface'
        ];

        foreach ($AliaseNames as $alias => $aliasPath) {
            if ((interface_exists($aliasPath) || class_exists($aliasPath)) && !class_exists($alias) && !interface_exists($alias)) {
                \class_alias($aliasPath, $alias);
            }
        }
    }
}
