<?php

declare(strict_types=1);

namespace App\Command;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test-upload-minio',
    description: 'Testet den Upload zu MinIO via Flysystem.',
)]
class TestUploadToMinioCommand extends Command
{
    public function __construct(
        private readonly FilesystemOperator $mediaStorage
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = 'test-' . uniqid() . '.txt';
        $content = 'Das ist ein Testupload für MinIO.';

        try
        {
            $this->mediaStorage->write($filename, $content);
            $output->writeln("✅ Upload erfolgreich: <info>$filename</info>");
        } catch (\Throwable $e)
        {
            $output->writeln("❌ Upload fehlgeschlagen: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
