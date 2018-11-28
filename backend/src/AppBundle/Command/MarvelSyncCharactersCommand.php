<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\MarvelCharacter;

class MarvelSyncCharactersCommand extends ContainerAwareCommand
{
    private static $description = "Lists available characters from the Marvel API";

    protected function configure()
    {
        $this
            ->setName('marvel:sync-characters')
            ->setDescription(self::$description)
            ->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'DB offset', 0)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Row limit', 20)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $marvelApi = $container->get('app.marvel_api_service');
        $offset = $input->getOption('offset');
        $limit = $input->getOption('limit');
        $characters = $marvelApi->getCharacters($offset, $limit);

        $em = $container->get('doctrine')->getManager();
        $characterRepo = $em->getRepository('AppBundle:MarvelCharacter');

        foreach ($characters as $apiCharacter) {
            // Search for character
            $localCharacters = $characterRepo->findByForeignId($apiCharacter->id);
            // Create a new entity if we don't already have a copy
            if (count($localCharacters)) {
                $localCharacter = $localCharacters[0];
            } else {
                $localCharacter = new MarvelCharacter();
                $localCharacter->setForeignId($apiCharacter->id);
            }

            // Set details
            $localCharacter->setName($apiCharacter->name);
            $friendlyName = strtolower(
                str_replace(' ', '-',
                    preg_replace('/[^\da-z ]/i', '',
                        $apiCharacter->name
                    )
                )
            );
            $localCharacter->setFriendlyName($friendlyName);
            $localCharacter->setDescription($apiCharacter->description);
            $thumbnailUrl = "{$apiCharacter->thumbnail->path}.{$apiCharacter->thumbnail->extension}";
            /*if (strpos($thumbnailUrl, 'image_not_available') !== false) {
                $thumbnailUrl = '';
            }*/
            $localCharacter->setThumbnailUrl($thumbnailUrl);
            $localCharacter->setNumComics($apiCharacter->comics->available);
            $localCharacter->setNumSeries($apiCharacter->series->available);
            $localCharacter->setNumEvents($apiCharacter->events->available);

            // Save to db
            $em->persist($localCharacter);

            $output->writeln("Syncing {$localCharacter->getName()} ({$localCharacter->getForeignId()})");
        }
        $em->flush();
    }

}
