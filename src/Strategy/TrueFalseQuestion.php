<?php

namespace App\Strategy;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class TrueFalseQuestion implements GenerateQuestionStrategy
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $playerName
     * @param $triviaQuestionService
     * @return mixed
     */
    public function generate(InputInterface $input, OutputInterface $output, $playerName, $triviaQuestionService): mixed
    {
        $questionText = $this->ask($input, $output, 'Enter the True/False question: ');
        $correctAnswer = $this->ask($input, $output, 'Is the answer True or False? ');

        $data['player'] = $playerName;
        $data['text'] = $questionText;
        $data['type'] = 'true_false';
        $data['options'] = [
            'correct_answer' => $correctAnswer === "true",
        ];
        $this->storeQuestion($data, $triviaQuestionService);
        return $data;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $question
     * @return mixed
     */
    private function ask(InputInterface $input, OutputInterface $output, $question): mixed
    {
        $questionHelper = new QuestionHelper();
        return $questionHelper->ask($input, $output, new Question($question));
    }

    /**
     * @param array $attributes
     * @param $triviaQuestionService
     * @return void
     */
    private function storeQuestion(array $attributes, $triviaQuestionService): void
    {
        $triviaQuestionService->store($attributes);
    }
}