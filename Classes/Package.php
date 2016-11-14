<?php
namespace Neos\MetaData\ContentRepositoryAdapter;

use Neos\MetaData\ContentRepositoryAdapter\Mapper\ContentRepositoryMapper;
use Neos\MetaData\MetaDataManager;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;

class Package extends BasePackage
{
    /**
     * @inheritdoc
     *
     * @param Bootstrap $bootstrap The current bootstrap
     *
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(MetaDataManager::class, 'metaDataCollectionUpdated', ContentRepositoryMapper::class, 'mapMetaData');
    }
}
