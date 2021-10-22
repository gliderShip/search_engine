<?php

namespace App\Command;

use App\Exception\ArgumentException;
use App\Exception\CommandException;
use App\Exception\ConsoleException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class PandoraCommand extends ConsoleCommand
{
    const COMMANDS = ['index' => 'index', 'query' => 'query', 'exit' => 'exit'];
    const NERVOUS_BREAKDOWN_THRESHOLD = 3;
    protected static $defaultName = 'pandora';
    protected static $defaultDescription = 'A box that would have been better left closed';

    private string $command;
    private string $userRequest;
    private int $consecutiveErrors = 0;



    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $helper = $this->getHelper('question');

        $question = new Question('');
        $question->setAutocompleterValues(self::COMMANDS);
        $question->setNormalizer(function ($answer) {
            $answer = strtolower($answer);
            return $answer ? trim($answer) : '';
        });

        $question->setValidator(function ($answer) {
            $this->command = $this->validateCommand($answer);
            $this->consecutiveErrors = 0;
            return $answer;
        });

        $this->userRequest = $helper->ask($input, $output, $question);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $returnCode = self::CONSOLE_SUCCESS;

        try{
            $returnCode = $this->executeCommand($this->command, $this->userRequest, $output);
        } catch (ConsoleException $e){
            $io->write($e->getMessage());
        }

        return $returnCode;
    }

    private function validateCommand(string $userRequest): string
    {
        $command = strtok($userRequest, " ");
        if (!in_array($command, self::COMMANDS)) {
            ++$this->consecutiveErrors;
            if ($this->consecutiveErrors >= self::NERVOUS_BREAKDOWN_THRESHOLD) {
                $this->crash();
            } else {
                throw new CommandException($command, "$userRequest command not in our repertoire");
            }
        }

        return $command;
    }

    private function crash()
    {
        exit("error : ℙ𝕒𝕟𝕕𝕠𝕣𝕒 ⲓ𝛓 ⲉⲭⲣⲉꞅⲓⲉⲛⲥⲓⲛ𝓰 a 🅼🅴🅽🆃🅰🅻 вⷡrͬaͣᴋⷦdͩoͦwn" . "Ē̶̡͎͉̖̟͒̚ṛ̸̽͑r̷̨͇̗̬͍͖̓ö̸͍̗́̚r̵̡̤̐͘͝:̸̥̪͔̻̍͆͊̚ ̷̛̥͔͑̑̀ͅ4̶͈͔̔͗̍̊̊͆1̴̙̟͇́̎̈̿̂͊8̸̡̙͈̺̖̫̃ ̶͓̳̞͆̃͒ͅI̸̼̲̱̜̿͆'̸̰̒́̈͝m̸̨̛̯͉̘̭͐̒̑ͅ ̴̧͎̙͇͈̝̅̓ã̵̪͇̥̜͈̦̍ ̷̢͈̥̫͐t̸̡̪̜͛e̷̛͍̥̩̳̘͒̑͒͝a̸̭͚̭͑̋͋͊̎͌ṕ̸̗̊o̸̪͇̠̰̟̣̾̃͒̋̈́t̴̖̄̍͊͘!̸̜̺͈̄͛̊̕͠");

    }

    private function executeCommand(string $command, string $userRequest, OutputInterface $output): string
    {
        switch ($command) {
            case self::COMMANDS['exit']:
                exit(self::CONSOLE_SUCCESS);
            case self::COMMANDS['index']:
                $returnCode = $this->indexDocument($command, $userRequest, $output);
                break;
            case self::COMMANDS['query']:
                $returnCode = $this->queryDocuments($command, $userRequest, $output);
                break;
            default:
                throw new \BadMethodCallException("Unknown command $command");
        }

        return $returnCode;
    }

    private function indexDocument(string $command, string $userRequest, OutputInterface $output): string
    {
        $indexCommand = $this->getApplication()->find(IndexCommand::getDefaultName());

        $arguments = $this->getUserRequestArguments(IndexCommand::getDefaultName(), $userRequest);

        if(count($arguments) < 2){
            throw new ArgumentException($command, "Please provide the $command command arguments!");
        }

        $commandArguments = [
            'docId'  => array_shift($arguments),
            'tokens'  => $arguments,
        ];

        $indexCommandInput = new ArrayInput($commandArguments);
        try {
            $returnCode = $indexCommand->run($indexCommandInput, $output);
        } catch (\Exception $e) {
            throw new CommandException(IndexCommand::getDefaultName(), $e->getMessage());
        }

        return $returnCode;
    }

    private function queryDocuments(string $command, string $userRequest, OutputInterface $output): string
    {
        $queryCommand = $this->getApplication()->find(QueryCommand::getDefaultName());

        $arguments = $this->getUserRequestArguments(QueryCommand::getDefaultName(), $userRequest);

        if(empty($arguments) || empty($arguments[0])){
            throw new ArgumentException($command, "Please provide the $command command arguments!");
        }

        $commandArguments = [
            'expression'  => $arguments,
        ];
        dump($commandArguments);

        $queryCommandInput = new ArrayInput($commandArguments);
        try {
            $returnCode = $queryCommand->run($queryCommandInput, $output);
        } catch (\Exception $e) {
            throw new CommandException(QueryCommand::getDefaultName(), $e->getMessage());
        }

        return $returnCode;
    }

    /**
     * @throws ArgumentException
     */
    private function getUserRequestArguments(string $command, $userRequest): array{
        // arguments are everything after the first space
        $argumentsString =  substr(strstr($userRequest," "), 1);
        $arguments = explode(' ', $argumentsString);

        if(count($arguments) < 1){
            throw new ArgumentException($command, "Please provide the $command command arguments!");
        }

        return $arguments;
    }

}
