<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MarvelListCharactersCommand extends ContainerAwareCommand
{
    private static $description = "Lists available characters from the Marvel API";

    protected function configure()
    {
        $this
            ->setName('marvel:list-characters')
            ->setDescription(self::$description)
            ->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'DB offset', 0)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Row limit', 20)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $marvelApi = $this->getContainer()->get('app.marvel_api_service');
        $offset = $input->getOption('offset');
        $limit = $input->getOption('limit');
        $characters = $marvelApi->getCharacters($offset, $limit);

        foreach ($characters as $character) {
            $output->writeln($character->name);
        }
    }

}
