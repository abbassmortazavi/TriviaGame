<?php

namespace App\Strategy;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class MultipleChoiceQuestion implements GenerateQuestionStrategy
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
        $questionText = $this->ask($input, $output, 'Enter the multiple-choice question: ');
        $options = [];
        for ($i = 1; $i <= 4; $i++) {
            $options[] = $this->ask($input, $output, "Enter option $i: ");
        }
        $correctAnswer = $this->ask($input, $output, 'Which option is the correct answer please write it? ');

        $data['player'] = $playerName;
        $data['text'] = $questionText;
        $data['type'] = 'multiple_choice';
        $data['options'] = [
            'options' => $options,
            'correct_answer' => $correctAnswer,
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