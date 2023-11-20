<?php

namespace App\Strategy;

use App\Enums\QuestionType;
use Exception;

class QuestionStrategy
{
    /**
     * @throws Exception
     */
    public function generateQuestion(string $type, $input, $output, $playerName, $triviaQuestionService)
    {
        $question = $this->defineQuestion($type);
        return $question->generate($input, $output, $playerName, $triviaQuestionService);
    }

    /**
     * @param string $type
     * @return MultipleChoiceQuestion|TrueFalseQuestion
     * @throws Exception
     */
    private function defineQuestion(string $type): TrueFalseQuestion|MultipleChoiceQuestion
    {
        return match ($type) {
            QuestionType::CHOICE->value => new MultipleChoiceQuestion(),
            QuestionType::TRUEFALSE->value => new TrueFalseQuestion(),
            default => throw new Exception('Not Found Question Type!'),
        };
    }
}