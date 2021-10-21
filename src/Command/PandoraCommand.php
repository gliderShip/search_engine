<?php

namespace App\Command;

use App\Exception\NervousBreakdownException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class PandoraCommand extends ConsoleCommand
{
    protected static $defaultName = 'pandora';
    protected static $defaultDescription = 'A box that would have been better left closed';

    const COMMANDS = ['index', 'query'];
    const NERVOUS_BREAKDOWN_THRESHOLD = 3;

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');

        $question = new Question('');
        $question->setAutocompleterValues(self::COMMANDS);

        $question->setNormalizer(function ($answer) {
            $answer = strtolower($answer);
            return $answer ? trim($answer) : '';
        });

        $question->setValidator(function ($answer) {

            $command = strtok($answer, " ");
            if(!in_array($command, self::COMMANDS)){
                $this->crash($this);
                return self::CONSOLE_ARGUMENT_ERROR;
            }

            return $answer;
        });

        $question->setMaxAttempts(self::NERVOUS_BREAKDOWN_THRESHOLD);

        $userCommand = $helper->ask($input, $output, $question);


        dump($userCommand);


        return 0;
    }

    private function commandValidator(string $answer): string{


    }

    private function crash(){
        throw new NervousBreakdownException("Ē̶̡͎͉̖̟͒̚ṛ̸̽͑r̷̨͇̗̬͍͖̓ö̸͍̗́̚r̵̡̤̐͘͝:̸̥̪͔̻̍͆͊̚ ̷̛̥͔͑̑̀ͅ4̶͈͔̔͗̍̊̊͆1̴̙̟͇́̎̈̿̂͊8̸̡̙͈̺̖̫̃ ̶͓̳̞͆̃͒ͅI̸̼̲̱̜̿͆'̸̰̒́̈͝m̸̨̛̯͉̘̭͐̒̑ͅ ̴̧͎̙͇͈̝̅̓ã̵̪͇̥̜͈̦̍ ̷̢͈̥̫͐t̸̡̪̜͛e̷̛͍̥̩̳̘͒̑͒͝a̸̭͚̭͑̋͋͊̎͌ṕ̸̗̊o̸̪͇̠̰̟̣̾̃͒̋̈́t̴̖̄̍͊͘!̸̜̺͈̄͛̊̕͠");
    }
}
