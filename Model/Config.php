<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface as StoreScope;
use Magento\Store\Model\StoreManagerInterface;
use SoftCommerce\Core\App\Config\Value as ConfigValue;
use SoftCommerce\Core\App\Config\ValueFactory;
use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;
use SoftCommerce\ProfileConfig\Model\ResourceModel;

/**
 * @inheritDoc
 *
 * @method string getSection()
 * @method Config setSection(string $value)
 * @method int getWebsite()
 * @method Config setWebsite(int $value)
 * @method int getStore()
 * @method Config setStore(int $value)
 * @method array getGroups()
 * @method Config setGroups(array $value)
 * @method string getScopeCode()
 * @method Config setScopeCode(string $value)
 */
class Config extends AbstractModel implements ConfigInterface, IdentityInterface
{
    const CACHE_TAG = 'plenty_profile_config_model';

    /**
     * @var string
     */
    protected $cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var array
     */
    private $configData;

    /**
     * @var Config\Structure
     */
    private $configStructure;

    /**
     * @var ReinitableConfigInterface
     */
    private $appConfig;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var ResourceProfile\Config\Collection
     */
    private $configCollectionFactory;

    /**
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SettingChecker|mixed
     */
    private $settingChecker;

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Config::class);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return [$this->_eventPrefix . '_' . $this->getEntityId()];
    }

    /**
     * @inheritDoc
     */
    public function getEntityId(): int
    {
        return (int) $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function getParentId(): ?int
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setParentId(int $parentId)
    {
        $this->setData(self::PARENT_ID, $parentId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getScope(): ?string
    {
        return $this->getData(self::SCOPE);
    }

    /**
     * @inheritDoc
     */
    public function setScope(string $scope)
    {
        $this->setData(self::SCOPE, $scope);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getScopeId(): ?int
    {
        return $this->getData(self::SCOPE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setScopeId(int $scopeId)
    {
        $this->setData(self::SCOPE_ID, $scopeId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): ?string
    {
        return $this->getData(self::PATH);
    }

    /**
     * @inheritDoc
     */
    public function setPath(string $path)
    {
        $this->setData(self::PATH, $path);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): ?string
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        $this->setData(self::VALUE, $value);
        return $this;
    }

    /**
     * @param $groupId
     * @param array $groupData
     * @param array $groups
     * @param $sectionPath
     * @param array $extraOldGroups
     * @param array $oldConfig
     * @param Transaction $saveTransaction
     * @param Transaction $deleteTransaction
     * @throws LocalizedException
     */
    protected function processGroup(
        $groupId,
        array $groupData,
        array $groups,
        $sectionPath,
        array &$extraOldGroups,
        array &$oldConfig,
        Transaction $saveTransaction,
        Transaction $deleteTransaction
    ) {
        $groupPath = $sectionPath . '/' . $groupId;
        if (isset($groupData['fields'])) {
            /** @var Structure\Element\Group $group */
            $group = $this->configStructure->getElement($groupPath);

            $fieldsetData = [];
            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $fieldsetData[$fieldId] = $fieldData['value'] ?? null;
            }

            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $isReadOnly = $this->settingChecker->isReadOnly(
                    $groupPath . '/' . $fieldId,
                    $this->getScope(),
                    $this->getScopeCode()
                );

                if ($isReadOnly) {
                    continue;
                }

                $field = $this->getField($sectionPath, $groupId, $fieldId);

                /** @var ConfigValue $backendModel */
                $backendModel = $field->hasBackendModel()
                    ? $this->configValueFactory->create($field->getAttribute('backend_model'))
                    : $this->configValueFactory->create();

                if (!isset($fieldData['value'])) {
                    $fieldData['value'] = null;
                }

                $data = [
                    'field' => $fieldId,
                    'groups' => $groups,
                    'group_id' => $group->getId(),
                    'scope' => $this->getScope(),
                    'scope_id' => $this->getScopeId(),
                    'scope_code' => $this->getScopeCode(),
                    'field_config' => $field->getData(),
                    'fieldset_data' => $fieldsetData,
                ];
                $backendModel->addData($data);
                $this->checkSingleStoreMode($field, $backendModel);

                $path = $this->getFieldPath($field, $extraOldGroups, $oldConfig);
                $backendModel->setProfileId($this->getProfileId())
                    ->setPath($path)
                    ->setValue($fieldData['value']);

                $inherit = !empty($fieldData['inherit']);
                if (isset($oldConfig[$path])) {
                    $backendModel->setConfigId($oldConfig[$path]['entity_id']);
                    if (!$inherit) {
                        $saveTransaction->addObject($backendModel);
                    } else {
                        $deleteTransaction->addObject($backendModel);
                    }
                } elseif (!$inherit) {
                    $backendModel->unsConfigId();
                    $saveTransaction->addObject($backendModel);
                }
            }
        }

        if (isset($groupData['groups'])) {
            foreach ($groupData['groups'] as $subGroupId => $subGroupData) {
                $this->processGroup(
                    $subGroupId,
                    $subGroupData,
                    $groups,
                    $groupPath,
                    $extraOldGroups,
                    $oldConfig,
                    $saveTransaction,
                    $deleteTransaction
                );
            }
        }
    }

    /**
     * @param $path
     * @param $scope
     * @param $scopeId
     * @param bool $full
     * @return array
     */
    protected function getConfigByPath($path, $scope, $scopeId, $full = true)
    {
        $configDataCollection = $this->configCollectionFactory
            ->addScopeFilter($scope, $scopeId, $path, $this->getProfileId());

        $config = [];
        $configDataCollection->load();
        foreach ($configDataCollection->getItems() as $data) {
            if ($full) {
                $config[$data->getPath()] = [
                    'path' => $data->getPath(),
                    'value' => $data->getValue(),
                    'entity_id' => $data->getId(),
                ];
            } else {
                $config[$data->getPath()] = $data->getValue();
            }
        }
        return $config;
    }

    /**
     * @param bool $full
     * @return array
     */
    protected function getConfig($full = true)
    {
        return $this->getConfigByPath(
            $this->getSection(),
            $this->getScope(),
            $this->getScopeId(),
            $full
        );
    }

    /**
     * @param Field $fieldConfig
     * @param $dataObject
     */
    protected function checkSingleStoreMode(
        Field $fieldConfig,
        $dataObject
    ) {
        $isSingleStoreMode = $this->storeManager->isSingleStoreMode();
        if (!$isSingleStoreMode) {
            return;
        }
        if (!$fieldConfig->showInDefault()) {
            $websites = $this->storeManager->getWebsites();
            $singleStoreWebsite = array_shift($websites);
            $dataObject->setScope('websites');
            $dataObject->setWebsiteCode($singleStoreWebsite->getCode());
            $dataObject->setScopeCode($singleStoreWebsite->getCode());
            $dataObject->setScopeId($singleStoreWebsite->getId());
        }
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function loadData()
    {
        if (null === $this->configData) {
            $this->initScope();
            $this->configData = $this->getConfig(false);
        }
        return $this->configData;
    }

    /**
     * @param $path
     * @param bool $full
     * @param array $oldConfig
     * @return array
     */
    public function extendConfig($path, $full = true, $oldConfig = [])
    {
        $extended = $this->getConfigByPath($path, $this->getScope(), $this->getScopeId(), $full);
        if (is_array($oldConfig) && !empty($oldConfig)) {
            return $oldConfig + $extended;
        }
        return $extended;
    }

    /**
     * @param $path
     * @param $value
     */
    public function setDataByPath($path, $value)
    {
        $path = trim($path);
        if ($path === '') {
            throw new \UnexpectedValueException('Path must not be empty');
        }
        $pathParts = explode('/', $path);
        $keyDepth = count($pathParts);
        if ($keyDepth !== 3) {
            throw new \UnexpectedValueException(
                'Allowed depth of configuration is 3 (<section>/<group>/<field>). Your configuration depth is '
                . $keyDepth . " for path '$path'"
            );
        }
        $data = [
            'section' => $pathParts[0],
            'groups' => [
                $pathParts[1] => [
                    'fields' => [
                        $pathParts[2] => ['value' => $value],
                    ],
                ],
            ],
        ];

        $this->addData($data);
    }

    /**
     * @param $path
     * @param null $inherit
     * @param null $configData
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConfigDataValue($path, &$inherit = null, $configData = null)
    {
        $this->loadData();
        if ($configData === null) {
            $configData = $this->configData;
        }
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = $this->appConfig->getValue($path, $this->getScope(), $this->getScopeCode());
            $inherit = true;
        }

        return $data;
    }

    /**
     * @return $this|Config
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function save()
    {
        $this->initScope();

        $sectionId = $this->getSection();
        $groups = $this->getGroups();
        if (empty($groups)) {
            return $this;
        }

        $oldConfig = $this->getConfig(true);

        /** @var Transaction $deleteTransaction */
        $deleteTransaction = $this->transactionFactory->create();
        /** @var Transaction $saveTransaction */
        $saveTransaction = $this->transactionFactory->create();

        $changedPaths = [];
        $extraOldGroups = [];
        foreach ($groups as $groupId => $groupData) {
            $this->processGroup(
                $groupId,
                $groupData,
                $groups,
                $sectionId,
                $extraOldGroups,
                $oldConfig,
                $saveTransaction,
                $deleteTransaction
            );

            $groupChangedPaths = $this->getChangedPaths($sectionId, $groupId, $groupData, $oldConfig, $extraOldGroups);
            $changedPaths = array_merge($changedPaths, $groupChangedPaths);
        }

        try {
            $deleteTransaction->delete();
            $saveTransaction->save();

            $this->removeCacheData();
            $this->appConfig->reinit();

            $this->_eventManager->dispatch(
                "plenty_profile_config_changed_section_{$this->getSection()}",
                [
                    'website' => $this->getWebsite(),
                    'store' => $this->getStore(),
                    'profile_id' => $this->getProfileId(),
                    'changed_paths' => $changedPaths,
                ]
            );
        } catch (\Exception $e) {
            $this->removeCacheData();
            $this->appConfig->reinit();
            throw $e;
        }

        return $this;
    }

    /**
     * @return string
     */
    private function getConfigType()
    {
        return 'plenty_profile_' . $this->getProfileId();
    }

    /**
     * @return $this
     */
    private function removeCacheData()
    {
        $this->cache->remove($this->getConfigType());
        $this->cache->remove($this->getConfigType() . '_default');

        foreach ([StoreScope::SCOPE_WEBSITES, StoreScope::SCOPE_STORES] as $curScopeType) {
            foreach ($data[$curScopeType] ?? [] as $curScopeId => $curScopeData) {
                $this->cache->remove($this->getConfigType() . '_' . $curScopeType . '_' . $curScopeId);
            }
        }

        $this->cache->remove($this->getConfigType() . '_scopes');
        return $this;
    }

    /**
     * @param Group $group
     * @param string $fieldId
     * @return string
     * @throws LocalizedException
     */
    private function getOriginalFieldId(Group $group, string $fieldId): string
    {
        if ($group->shouldCloneFields()) {
            $cloneModel = $group->getCloneModel();

            /** @var Structure\Element\Field $field */
            foreach ($group->getChildren() as $field) {
                foreach ($cloneModel->getPrefixes() as $prefix) {
                    if ($prefix['field'] . $field->getId() === $fieldId) {
                        $fieldId = $field->getId();
                        break(2);
                    }
                }
            }
        }

        return $fieldId;
    }

    /**
     * @param string $sectionId
     * @param string $groupId
     * @param string $fieldId
     * @return Field
     * @throws LocalizedException
     */
    private function getField(string $sectionId, string $groupId, string $fieldId): Field
    {
        /** @var Structure\Element\Group $group */
        $group = $this->configStructure->getElement($sectionId . '/' . $groupId);
        $fieldPath = $group->getPath() . '/' . $this->getOriginalFieldId($group, $fieldId);
        return $this->configStructure->getElement($fieldPath);
    }

    /**
     * Get field path
     *
     * @param Field $field
     * @param array $oldConfig
     * @param array $extraOldGroups
     * @return string
     */
    private function getFieldPath(Field $field, array &$oldConfig, array &$extraOldGroups): string
    {
        $path = $field->getGroupPath() . '/' . $field->getId();

        $configPath = $field->getConfigPath();
        if ($configPath && strrpos($configPath, '/') > 0) {
            // Extend old data with specified section group
            $configGroupPath = substr($configPath, 0, strrpos($configPath, '/'));
            if (!isset($extraOldGroups[$configGroupPath])) {
                $oldConfig = $this->extendConfig($configGroupPath, true, $oldConfig);
                $extraOldGroups[$configGroupPath] = true;
            }
            $path = $configPath;
        }

        return $path;
    }

    /**
     * @param array $oldConfig
     * @param string $path
     * @param array $fieldData
     * @return bool
     */
    private function isValueChanged(array $oldConfig, string $path, array $fieldData): bool
    {
        if (isset($oldConfig[$path]['value'])) {
            $result = !isset($fieldData['value']) || $oldConfig[$path]['value'] !== $fieldData['value'];
        } else {
            $result = empty($fieldData['inherit']);
        }

        return $result;
    }

    /**
     * @param string $sectionId
     * @param string $groupId
     * @param array $groupData
     * @param array $oldConfig
     * @param array $extraOldGroups
     * @return array
     * @throws LocalizedException
     */
    private function getChangedPaths(
        string $sectionId,
        string $groupId,
        array $groupData,
        array &$oldConfig,
        array &$extraOldGroups
    ): array {
        $changedPaths = [];

        if (isset($groupData['fields'])) {
            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $field = $this->getField($sectionId, $groupId, $fieldId);
                $path = $this->getFieldPath($field, $oldConfig, $extraOldGroups);
                if ($this->isValueChanged($oldConfig, $path, $fieldData)) {
                    $changedPaths[] = $path;
                }
            }
        }

        if (isset($groupData['groups'])) {
            $subSectionId = $sectionId . '/' . $groupId;
            foreach ($groupData['groups'] as $subGroupId => $subGroupData) {
                $subGroupChangedPaths = $this->getChangedPaths(
                    $subSectionId,
                    $subGroupId,
                    $subGroupData,
                    $oldConfig,
                    $extraOldGroups
                );
                $changedPaths = \array_merge($changedPaths, $subGroupChangedPaths);
            }
        }

        return $changedPaths;
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function initScope()
    {
        if ($this->getSection() === null) {
            $this->setSection('');
        }
        if ($this->getWebsite() === null) {
            $this->setWebsite('');
        }
        if ($this->getStore() === null) {
            $this->setStore('');
        }

        if ($this->getStore()) {
            $scope = 'stores';
            $store = $this->storeManager->getStore($this->getStore());
            $scopeId = (int)$store->getId();
            $scopeCode = $store->getCode();
        } elseif ($this->getWebsite()) {
            $scope = 'websites';
            $website = $this->storeManager->getWebsite($this->getWebsite());
            $scopeId = (int)$website->getId();
            $scopeCode = $website->getCode();
        } else {
            $scope = 'default';
            $scopeId = 0;
            $scopeCode = '';
        }
        $this->setScope($scope);
        $this->setScopeId($scopeId);
        $this->setScopeCode($scopeCode);
    }
}
