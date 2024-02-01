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
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use SoftCommerce\Profile\Api\Data\ProfileInterface;
use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @inheritDoc
 */
class ExportConfigData extends Command
{
    private const COMMAND_NAME = 'profile:config:export';
    private const TYPE_ID_FILTER = 'id';

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    private DateTime $dateTime;

    /**
     * @var WriteInterface
     */
    private WriteInterface $directory;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param DateTime $dateTime
     * @param Filesystem $filesystem
     * @param ResourceConnection $resourceConnection
     * @param SerializerInterface $serializer
     * @param string|null $name
     * @throws FileSystemException
     */
    public function __construct(
        DateTime $dateTime,
        Filesystem $filesystem,
        ResourceConnection $resourceConnection,
        SerializerInterface $serializer,
        ?string $name = null
    ) {
        $this->dateTime = $dateTime;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->connection = $resourceConnection->getConnection();
        $this->serializer = $serializer;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Export profile configuration data.')
            ->setDefinition([
                new InputOption(
                    self::TYPE_ID_FILTER,
                    '-i',
                    InputOption::VALUE_OPTIONAL,
                    'Profile type ID filter.'
                )
            ]);
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $typeIds = [];
            if ($filter = $input->getOption(self::TYPE_ID_FILTER)) {
                $typeIds = explode(',', $filter);
                $typeIds = array_map('trim', $typeIds);
            }

            if ($data = $this->getProfileData($typeIds)) {
                $filename = $this->writeProfileData($data);
                $output->writeln('<info>Config data has been exported.</info>');
                $output->writeln(
                    sprintf(
                        '<info>Effected profiles:</info> <comment>%s</comment>',
                        implode(', ', $typeIds ?: array_keys($data))
                    )
                );
                $output->writeln(
                    sprintf(
                        '<info>Config data has been saved to: </info> <comment>%s</comment>',
                        $filename
                    )
                );
            } else {
                $output->writeln('<comment>Could not find applicable profiles.</comment>');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param array $typeIds
     * @return array
     */
    private function getProfileData(array $typeIds = []): array
    {
        $select = $this->connection->select()
            ->from(
                ProfileInterface::DB_TABLE_NAME,
                [
                    ProfileInterface::ENTITY_ID,
                    ProfileInterface::TYPE_ID,
                    ProfileInterface::NAME
                ]
            );

        if ($typeIds) {
            $select->where(ProfileInterface::TYPE_ID . ' IN (?)', $typeIds);
        }

        $result = [];
        foreach ($this->connection->fetchAll($select) as $profile) {
            $profileId = $profile[ProfileInterface::ENTITY_ID];
            $profileType = $profile[ProfileInterface::TYPE_ID];
            $result[$profileType] = $profile;

            $select = $this->connection
                ->select()
                ->from(ConfigInterface::DB_TABLE_NAME)
                ->where(ConfigInterface::PARENT_ID . ' = ?', $profileId);

            $configData = $this->connection->fetchAll($select);
            foreach ($configData as $index => $configItem) {
                $configValue = $configItem[ConfigInterface::VALUE];
                try {
                    $configData[$index][ConfigInterface::VALUE] = $this->serializer->unserialize($configValue);
                } catch (\InvalidArgumentException $e) {
                    $configData[$index][ConfigInterface::VALUE] = $configValue;
                }
            }

            $result[$profileType]['config'] = $configData;
        }

        return $result;
    }

    /**
     * @param array $data
     * @return string
     * @throws FileSystemException
     */
    private function writeProfileData(array $data): string
    {
        $data = json_encode($data, JSON_PRETTY_PRINT);
        $destination = "plenty/profile-config-{$this->dateTime->gmtDate('Y-m-d-His')}.json";

        $stream = $this->directory->openFile($destination, 'a');
        $stream->lock();
        $stream->write($data);
        $stream->unlock();
        $stream->close();

        return $this->directory->getAbsolutePath($destination);
    }
}
