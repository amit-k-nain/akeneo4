<?php

namespace BolgattyDemoBundle\Step;

use EventInterface;
use FlushableInterface;
use InitializableInterface;
use InvalidItemException;
use ItemProcessorInterface;
use ItemReaderInterface;
use ItemWriterInterface;
use JobRepositoryInterface;
use StepExecution;
use Warning;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Basic step implementation that read items, process them and write them
 *
 */
interface ItemStepInterface
{
    /**
     * Get reader
     *
     * @return ItemReaderInterface
     */
    public function getReader();

    /**
     * Get processor
     *
     * @return ItemProcessorInterface
     */
    public function getProcessor();

    /**
     * Get writer
     *
     * @return ItemWriterInterface
     */
    public function getWriter();

    /**
     * {@inheritdoc}
     */
    public function doExecute(StepExecution $stepExecution);

    /**
     * Flushes step elements
     */
    public function flushStepElements();
}
