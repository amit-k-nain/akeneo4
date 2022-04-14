<?php

namespace BolgattyDemoBundle\Job;

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
        
        return $updatedSteps;
    }
}
