<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Console\Cli;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Serialize\SerializerInterface;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @inheritDoc
 */
class ImportConfigData extends Command
{
    private const COMMAND_NAME = 'profile:config:import';
    private const FILENAME_PARAM = 'file';

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var ReadInterface
     */
    private ReadInterface $directory;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param Filesystem $filesystem
     * @param ResourceConnection $resourceConnection
     * @param SerializerInterface $serializer
     * @param string|null $name
     */
    public function __construct(
        Filesystem $filesystem,
        ResourceConnection $resourceConnection,
        SerializerInterface $serializer,
        ?string $name = null
    ) {
        $this->directory = $filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
        $this->connection = $resourceConnection->getConnection();
        $this->serializer = $serializer;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Import profile configuration data.')
            ->setDefinition([
                new InputOption(
                    self::FILENAME_PARAM,
                    '-f',
                    InputOption::VALUE_REQUIRED,
                    'Filename parameter.'
                )
            ]);
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$filename = $input->getOption(self::FILENAME_PARAM)) {
            $output->writeln('<error>Filename is required.</error>');
            return Cli::RETURN_SUCCESS;
        }

        $filename = trim($filename);

        try {
            if ($data = $this->getProfileData($filename)) {
                $result = $this->saveProfileData($data);
                $output->writeln('<info>Profile data has been saved.</info>');
                $output->writeln(
                    sprintf(
                        '<info>Effected profiles:</info> <comment>%s</comment>',
                        implode(', ', $result)
                    )
                );
            } else {
                $output->writeln('<comment>Could not retrieve profile data for import.</comment>');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param string $filename
     * @return array
     * @throws FileSystemException
     */
    private function getProfileData(string $filename): array
    {
        $file = $this->directory->getAbsolutePath("plenty/$filename");
        if (!$this->directory->isReadable($file)) {
            throw new \Exception(sprintf('File "%s" is not readable.', $file));
        }

        $contents = $this->directory->readFile($file);
        $contents = $this->serializer->unserialize($contents);

        $saveRequestProfile = [];
        $saveRequestConfig = [];
        foreach ($contents as $profileItem) {
            $configData = $profileItem['config'] ?? [];
            unset ($profileItem['config']);

            $saveRequestProfile[] = $profileItem;

            foreach ($configData as $configItem) {
                $value = $configItem[ConfigInterface::VALUE] ?? null;
                if (is_array($value)) {
                    $configItem[ConfigInterface::VALUE] = $this->serializer->serialize($value);
                }

                $saveRequestConfig[$configItem[ConfigInterface::ENTITY_ID]] = $configItem;
            }
        }

        return [
            $saveRequestProfile,
            $saveRequestConfig
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    private function saveProfileData(array $data): array
    {
        list($saveRequestProfile, $saveRequestConfig) = $data;

        if ($saveRequestProfile) {
            $this->connection->insertOnDuplicate(
                $this->connection->getTableName(ProfileInterface::DB_TABLE_NAME),
                $saveRequestProfile
            );
        }

        if ($saveRequestConfig) {
            $this->connection->insertOnDuplicate(
                $this->connection->getTableName(ConfigInterface::DB_TABLE_NAME),
                $saveRequestConfig
            );
        }

        return array_column($saveRequestProfile, ProfileInterface::TYPE_ID);
    }
}
