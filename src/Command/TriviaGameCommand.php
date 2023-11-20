<?php

namespace App\Command;

use App\Enums\QuestionType;
use App\Services\QuestionTrivia\TriviaQuestionService;
use App\Strategy\QuestionStrategy;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'trivia:start',
    description: 'Add a short description for your command',
)]
class TriviaGameCommand extends Command
{
    /**
     * @param TriviaQuestionService $triviaQuestionService
     * @param QuestionStrategy $strategy
     */
    public function __construct(protected TriviaQuestionService $triviaQuestionService, protected QuestionStrategy $strategy)
    {
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $player1 = $this->askForPlayerName($input, $output, 'Player 1');
        $player2 = $this->askForPlayerName($input, $output, 'Player 2');
        $continue = $this->ask($input, $output, 'Do you want to add more new questions Or Start Existing Question in Our database (yes/no): ');

        if ($continue === "no") {
            $questions = $this->triviaQuestionService->get();
            if (count($questions) === 0) {
                $output->writeln("Your Database Questions is Empty, Please select Yes and add new Questions!! ");
                exit();
            }
        } else {
            $questions = $this->addQuestions($input, $output, $player1);
        }
        $this->playQuiz($input, $output, $player2, $questions);
        return Command::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $playerNumber
     * @return mixed
     */
    private function askForPlayerName(InputInterface $input, OutputInterface $output, string $playerNumber): mixed
    {
        $helper = $this->getHelper('question');
        $question = new Question("Enter $playerNumber's name: ");
        return $helper->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $playerName
     * @return array
     * @throws Exception
     */
    private function addQuestions(InputInterface $input, OutputInterface $output, string $playerName): array
    {
        $questions = [];
        do {
            $questionType = $this->askQuestionType($input, $output);
            $questions[] = $this->strategy->generateQuestion($questionType, $input, $output, $playerName, $this->triviaQuestionService);
            $continue = $this->ask($input, $output, 'Do you want to add more questions? (yes/no): ');
        } while (strtolower($continue) === 'yes');
        return $questions;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    private function askQuestionType(InputInterface $input, OutputInterface $output): mixed
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Select the question type:', [QuestionType::CHOICE->value, QuestionType::TRUEFALSE->value]);
        $question->setErrorMessage('Invalid question type.');
        return $helper->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $question
     * @return mixed
     */
    private function ask(InputInterface $input, OutputInterface $output, $question): mixed
    {
        return $this->getHelper('question')->ask($input, $output, new Question($question));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $player2
     * @param array $questions
     * @return void
     */
    private function playQuiz(InputInterface $input, OutputInterface $output, $player2, array $questions): void
    {
        $totalQuestions = count($questions);
        $player2Score = 0;
        foreach ($questions as $question) {
            $player2Answer = $this->askQuestion($input, $output, $player2, $question);
            if ($question['type'] === QuestionType::CHOICE->value && $player2Answer === $question['options']['correct_answer']) {
                $player2Score++;
            } elseif ($question['type'] === QuestionType::TRUEFALSE->value && (bool)$player2Answer === $question['options']['correct_answer']) {
                $player2Score++;
            }
        }
        $output->writeln("\nResults for $player2:");
        $output->writeln("Total Questions: $totalQuestions");
        $output->writeln("Correct Answers: $player2Score");
        $output->writeln("Wrong Answers: " . ($totalQuestions - $player2Score));
        $output->writeln("Score: $player2Score/$totalQuestions");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $player
     * @param $question
     * @return null
     */
    private function askQuestion(InputInterface $input, OutputInterface $output, $player, $question)
    {
        $helper = $this->getHelper('question');
        if ($question['type'] === QuestionType::CHOICE->value) {
            $questionText = $question['text'] . "\nOptions: " . implode(', ', $question['options']['options']) . "\nwrite Your Answer: ";
            $question = new ChoiceQuestion($player . ', ' . $questionText, $question['options']['options']);
            $question->setErrorMessage('Invalid option.');
            return $helper->ask($input, $output, $question);
        } elseif ($question['type'] === QuestionType::TRUEFALSE->value) {
            $questionText = $question['text'] . "\nIs the answer True or False? ";
            $question = new ChoiceQuestion($player . ', ' . $questionText, ['true', 'false']);
            $question->setErrorMessage('Invalid answer.');
            return $helper->ask($input, $output, $question);
        }
        return null;
    }
}
