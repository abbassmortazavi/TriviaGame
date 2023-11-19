<?php

namespace App\Services\QuestionTrivia;

interface TriviaQuestionInterface
{
    /**
     * @param array $attributes
     * @return void
     */
    public function store(array $attributes): void;

    /**
     * @return array
     */
    public function get(): array;
}