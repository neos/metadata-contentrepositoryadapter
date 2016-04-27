<?php
namespace Neos\MetaData\ContentRepositoryAdapter\Domain\Repository;

use TYPO3\TYPO3CR\Domain\Model\NodeData;
use TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository;
use TYPO3\TYPO3CR\Domain\Service\Context;
use TYPO3\Flow\Annotations as Flow;

/**
* @Flow\Scope("singleton")
*/
class MetaDataRepository extends NodeDataRepository
{

    const ENTITY_CLASSNAME = NodeData::class;

    const METADATA_ROOT_NODE_NAME = 'meta';


    /**
     * @param $assetIdentifier
     * @param Context $context
     * @return NodeData
     */
    public function findOneByAssetIdentifier($assetIdentifier, Context $context)
    {
        $assetNodeData = $this->findOneByPath(sprintf('/%s/%s', self::METADATA_ROOT_NODE_NAME, $assetIdentifier), $context->getWorkspace());
        return $assetNodeData;
    }

}