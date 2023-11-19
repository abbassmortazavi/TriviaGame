<?php

namespace App\Command;

use App\Entity\TriviaQuestion;
use App\Services\Question\QuestionService;
use App\Services\QuestionTrivia\TriviaQuestionService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'trivia:start',
    description: 'Add a short description for your command',
)]
class TriviaGameCommand extends Command
{

    public function __construct(protected EntityManagerInterface $entityManager, protected ManagerRegistry $doctrine, protected TriviaQuestion $question, protected TriviaQuestionService $triviaQuestionService)
    {
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
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


        $this->playQuiz($input, $output, $player1, $player2, $questions);

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
     */
    private function addQuestions(InputInterface $input, OutputInterface $output, string $playerName): array
    {
        $questions = [];

        do {
            $questionType = $this->askQuestionType($input, $output);
            switch ($questionType) {
                case 'multiple_choice':
                    $questions[] = $this->addMultipleChoiceQuestion($input, $output, $playerName);
                    break;
                case 'true_false':
                    $questions[] = $this->addTrueFalseQuestion($input, $output, $playerName);
                    break;
            }
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
        $question = new ChoiceQuestion('Select the question type:', ['multiple_choice', 'true_false']);
        $question->setErrorMessage('Invalid question type.');

        return $helper->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $playerName
     * @return array
     */
    private function addMultipleChoiceQuestion(InputInterface $input, OutputInterface $output, $playerName): array
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
        $this->storeQuestion($data);
        return $data;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $playerName
     * @return array
     */
    private function addTrueFalseQuestion(InputInterface $input, OutputInterface $output, $playerName): array
    {
        $questionText = $this->ask($input, $output, 'Enter the True/False question: ');
        $correctAnswer = $this->ask($input, $output, 'Is the answer True or False? ');

        $data['player'] = $playerName;
        $data['text'] = $questionText;
        $data['type'] = 'true_false';
        $data['options'] = [
            'correct_answer' => $correctAnswer === "true",
        ];
        $this->storeQuestion($data);
        return $data;
    }

    private function ask(InputInterface $input, OutputInterface $output, $question)
    {
        return $this->getHelper('question')->ask($input, $output, new Question($question));
    }

    /**
     * @param array $attributes
     * @return void
     */
    private function storeQuestion(array $attributes): void
    {
        $this->triviaQuestionService->store($attributes);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $player1
     * @param $player2
     * @param array $questions
     * @return void
     */
    private function playQuiz(InputInterface $input, OutputInterface $output, $player1, $player2, array $questions): void
    {
        $totalQuestions = count($questions);
        $player2Score = 0;
        foreach ($questions as $question) {
            $player2Answer = $this->askQuestion($input, $output, $player2, $question);
            if ($question['type'] === 'multiple_choice' && $player2Answer === $question['options']['correct_answer']) {
                $player2Score++;
            } elseif ($question['type'] === 'true_false' && (bool)$player2Answer === $question['options']['correct_answer']) {
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

        if ($question['type'] === 'multiple_choice') {
            $questionText = $question['text'] . "\nOptions: " . implode(', ', $question['options']['options']) . "\nwrite Your Answer: ";
            $question = new ChoiceQuestion($player . ', ' . $questionText, $question['options']['options']);
            $question->setErrorMessage('Invalid option.');

            return $helper->ask($input, $output, $question);
        } elseif ($question['type'] === 'true_false') {
            $questionText = $question['text'] . "\nIs the answer True or False? ";
            $question = new ChoiceQuestion($player . ', ' . $questionText, ['true', 'false']);
            $question->setErrorMessage('Invalid answer.');

            return $helper->ask($input, $output, $question);
        }

        return null;
    }

}
