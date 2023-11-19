<?php

namespace App\Command;

use App\Entity\TriviaQuestion;
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
   // name: 'TriviaGameCommand',
    description: 'Add a short description for your command',
)]
class TriviaGameCommand extends Command
{

    public function __construct(protected EntityManagerInterface $entityManager, protected ManagerRegistry $doctrine, protected TriviaQuestion $question)
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $player1 = $this->askForPlayerName($input, $output, 'Player 1');
        $player2 = $this->askForPlayerName($input, $output, 'Player 2');

        $questions = $this->addQuestions($input, $output, $player1);

        $this->playQuiz($input, $output, $player1, $player2, $questions);

        return Command::SUCCESS;
    }

    private function askForPlayerName(InputInterface $input, OutputInterface $output, string $playerNumber)
    {
        $helper = $this->getHelper('question');
        $question = new Question("Enter $playerNumber's name: ");
        return $helper->ask($input, $output, $question);
    }

    private function addQuestions(InputInterface $input, OutputInterface $output, string $playerName)
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

    private function askQuestionType(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Select the question type:', ['multiple_choice', 'true_false']);
        $question->setErrorMessage('Invalid question type.');

        return $helper->ask($input, $output, $question);
    }

    private function addMultipleChoiceQuestion(InputInterface $input, OutputInterface $output, $playerName)
    {
        $questionText = $this->ask($input, $output, 'Enter the multiple-choice question: ');
        $options = [];

        for ($i = 1; $i <= 4; $i++) {
            $options[] = $this->ask($input, $output, "Enter option $i: ");
        }

        $correctAnswer = $this->ask($input, $output, 'Which option is the correct answer (1-4)? ');

        $this->storeQuestion($playerName, $questionText, 'multiple_choice', [
            'options' => $options,
            'correct_answer' => (int) $correctAnswer - 1,
        ]);

        return [
            'type' => 'multiple_choice',
            'question' => $questionText,
            'options' => $options,
            'correct_answer' => (int) $correctAnswer - 1,
        ];
    }

    private function addTrueFalseQuestion(InputInterface $input, OutputInterface $output, $playerName)
    {
        $questionText = $this->ask($input, $output, 'Enter the True/False question: ');
        $correctAnswer = $this->ask($input, $output, 'Is the answer True or False? ');

        $this->storeQuestion($playerName, $questionText, 'true_false', [
            'correct_answer' => strtolower($correctAnswer) === 'true',
        ]);

        return [
            'type' => 'true_false',
            'question' => $questionText,
            'correct_answer' => strtolower($correctAnswer) === 'true',
        ];
    }

    private function ask(InputInterface $input, OutputInterface $output, $question)
    {
        return $this->getHelper('question')->ask($input, $output, new Question($question));
    }

    private function storeQuestion($playerName, $questionText, $type, $options): void
    {
        $entityManager = $this->doctrine->getManager();

        $this->question->setPlayerName($playerName);
        $this->question->setQuestionText($questionText);
        $this->question->setType($type);
        $this->question->setOptions(json_encode($options));

        $entityManager->persist($this->question);
        $entityManager->flush();
    }

    private function playQuiz(InputInterface $input, OutputInterface $output, $player1, $player2, array $questions)
    {
        $totalQuestions = count($questions);

        $player2Score = 0;

        foreach ($questions as $question) {
            $player2Answer = $this->askQuestion($input, $output, $player2, $question);

            if ($question['type'] === 'multiple_choice' && $player2Answer === $question['correct_answer']) {
                $player2Score++;
            } elseif ($question['type'] === 'true_false' && $player2Answer === $question['correct_answer']) {
                $player2Score++;
            }
        }

        $output->writeln("\nResults for $player2:");
        $output->writeln("Total Questions: $totalQuestions");
        $output->writeln("Correct Answers: $player2Score");
        $output->writeln("Wrong Answers: " . ($totalQuestions - $player2Score));
        $output->writeln("Score: $player2Score/$totalQuestions");
    }
    private function askQuestion(InputInterface $input, OutputInterface $output, $player, $question)
    {
        $helper = $this->getHelper('question');

        if ($question['type'] === 'multiple_choice') {
            $questionText = $question['question'] . "\nOptions: " . implode(', ', $question['options']) . "\nEnter your choice (1-4): ";
            $question = new ChoiceQuestion($player . ', ' . $questionText, $question['options']);
            $question->setErrorMessage('Invalid option.');

            return $helper->ask($input, $output, $question);
        } elseif ($question['type'] === 'true_false') {
            $questionText = $question['question'] . "\nIs the answer True or False? ";
            $question = new ChoiceQuestion($player . ', ' . $questionText, ['true', 'false']);
            $question->setErrorMessage('Invalid answer.');

            return $helper->ask($input, $output, $question);
        }

        return null;
    }

}
