<?php

namespace BolgattyDemoBundle\Job\Version;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

$classLoader = new \BolgattyDemoBundle\Listener\ClassDefinationForCompatibility();
$classLoader->customLoaderSystem();

/**
 * Implementation of the {@link Job} interface.
 */
class Job extends \PimJob implements \JobInterface
{
    public const CSV_PRODUCT_MODEL_STEP_NAME = 'csv_product_model_history_export';

    public const XLSX_PRODUCT_MODEL_STEP_NAME = 'xlsx_product_model_history_export';

    /**
     * {@inheritdoc}
     */
    protected function doExecute(\JobExecution $jobExecution)
    {
        $this->steps = $this->modifyStepsOrder($this->steps, $jobExecution);

        parent::doExecute($jobExecution);
    }

    protected function modifyStepsOrder(array $steps, \JobExecution $jobExecution)
    {
        $jobParameters = $jobExecution->getJobParameters();
        $updatedSteps = $steps;

        if ($jobParameters->has('exportProductModelFirst')
            && $jobParameters->get('exportProductModelFirst')) {
            foreach ($steps as $index => $step) {
                if ($step->getName() === self::CSV_PRODUCT_MODEL_STEP_NAME || $step->getName() === self::XLSX_PRODUCT_MODEL_STEP_NAME) {
                    unset($updatedSteps[$index]);
                    array_unshift($updatedSteps, $step);
                    break;
                }
            }
        }

        return $updatedSteps;
    }
}
