<?php

namespace App\Services\QuestionTrivia;

use App\Entity\TriviaQuestion;
use App\Repository\TriviaQuestionRepository;

class TriviaQuestionService implements TriviaQuestionInterface
{
    /**
     * @param TriviaQuestion $triviaQuestion
     * @param TriviaQuestionRepository $triviaQuestionRepository
     */
    public function __construct(protected TriviaQuestion $triviaQuestion, protected TriviaQuestionRepository $triviaQuestionRepository)
    {
    }

    /**
     * @param array $attributes
     * @return void
     */
    public function store(array $attributes): void
    {
        $this->triviaQuestionRepository->store($attributes);
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $questions = $this->triviaQuestionRepository->get();
        $data = [];
        foreach ($questions as $question) {
            $data[] = [
                'id' => $question->getId(),
                'text' => $question->getQuestionText(),
                'type' => $question->getType(),
                'options' => json_decode($question->getOptions(), true),
            ];
        }
        return $data;
    }
}