<?php

namespace App\Enums;

enum QuestionType: string
{
    case TRUEFALSE = 'true_false';
    case CHOICE = 'multiple_choice';
}
