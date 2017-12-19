<?php

namespace AppBundle\Command;

use AppBundle\Services\projectmanager_com;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class buildCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pm:build')
            ->setDescription('Build resources');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pm = $this->getContainer()->get(projectmanager_com::class);

        $pm->updateResources();
    }
}
