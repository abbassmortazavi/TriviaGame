<?php

namespace App\Services\QuestionTrivia;

use App\Entity\TriviaQuestion;
use Doctrine\Persistence\ManagerRegistry;

class TriviaQuestionService implements TriviaQuestionInterface
{
    public function __construct(protected TriviaQuestion $triviaQuestion, protected ManagerRegistry $doctrine)
    {
    }

    /**
     * @param array $attributes
     * @return void
     */
    public function store(array $attributes): void
    {
        $entityManager = $this->doctrine->getManager();

        $entity = new TriviaQuestion();
        $entity->setPlayerName($attributes['player']);
        $entity->setQuestionText($attributes['text']);
        $entity->setType($attributes['type']);
        $entity->setOptions(json_encode($attributes['options']));

        $entityManager->persist($entity);
        $entityManager->flush();
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $questions = $this->doctrine->getRepository(TriviaQuestion::class)->findAll();
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