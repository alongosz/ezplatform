<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Command;

use EzSystems\EzPlatformRichText\eZ\FieldType\RichText\Value;
use EzSystems\EzPlatformRichText\eZ\RichText\Converter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

final class ConvertCommand extends Command
{
    /** @var string */
    private $inputFixturesDirectory;
    /** @var \EzSystems\EzPlatformRichText\eZ\RichText\Converter[] */
    private $converters;

    public function __construct(
        Converter $richTextOutputConverter,
        Converter $richTextEditConverter,
        string $inputFixturesDirectory
    ) {
        parent::__construct();
        $this->converters = [
            'edit' => $richTextEditConverter,
            'output' => $richTextOutputConverter,
        ];
        $this->inputFixturesDirectory = $inputFixturesDirectory;
    }

    protected function configure()
    {
        $this
            ->setName('app:convert')
            ->addArgument('format', InputArgument::REQUIRED, 'edit,output')
            ->addArgument('file', InputArgument::OPTIONAL);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $file = $input->getArgument('file');
        if (empty($file)) {
            $file = $this->askForFile($input, $output);
        }
        $output->writeln('Selected: ' . $file);

        $richTextValue = new Value(file_get_contents($this->inputFixturesDirectory . '/' . $file));
        $converter = $this->getConverter($input->getArgument('format'));
        $output->writeln(
            $converter->convert($richTextValue->xml)->saveHTML()
        );
    }

    private function askForFile(InputInterface $input, OutputInterface $output): string
    {
        $directoryIterator = new \DirectoryIterator($this->inputFixturesDirectory);

        $files = [];
        foreach ($directoryIterator as $file) {
            if ($file->isDot()) {
                continue;
            }
            $files[] = $file->getFileInfo();
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select file to convert:',
            array_map(
                function (\SplFileInfo $fileInfo) {
                    return $fileInfo->getBasename();
                },
                $files
            )
        );
        $question->setErrorMessage('File %s is invalid.');

        return $helper->ask($input, $output, $question);
    }

    private function getConverter(string $format): Converter
    {
        return $this->converters[$format];
    }
}
