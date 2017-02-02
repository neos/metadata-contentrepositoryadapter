<?php
namespace Neos\MetaData\ContentRepositoryAdapter;

use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;
use Neos\MetaData\ContentRepositoryAdapter\Mapper\ContentRepositoryMapper;
use Neos\MetaData\MetaDataManager;

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
