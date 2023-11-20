<?php

namespace App\Strategy;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface GenerateQuestionStrategy
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $playerName
     * @param $triviaQuestionService
     */
    public function generate(InputInterface $input, OutputInterface $output, $playerName, $triviaQuestionService);
}