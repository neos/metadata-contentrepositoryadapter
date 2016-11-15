<?php
namespace Neos\MetaData\ContentRepositoryAdapter;

use Neos\MetaData\ContentRepositoryAdapter\Mapper\ContentRepositoryMapper;
use Neos\MetaData\Extractor\Domain\ExtractionManager;
use Neos\MetaData\MetaDataManager;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;
use TYPO3\Media\Domain\Repository\AssetRepository;


/**
 * The MetaData ContentRepositoryAdaptor package
 */
class Package extends BasePackage {

    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     *
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(MetaDataManager::class, 'metaDataCollectionUpdated', ContentRepositoryMapper::class, 'mapMetaData');
        $dispatcher->connect(AssetRepository::class, 'assetDeleted', ExtractionManager::class, 'extractMetaData');
    }
}
