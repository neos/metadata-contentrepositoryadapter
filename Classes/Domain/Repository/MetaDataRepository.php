<?php
namespace Neos\MetaData\ContentRepositoryAdapter\Domain\Repository;

/*
 * This file is part of the Neos.MetaData.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use TYPO3\Media\Domain\Model\Asset;
use TYPO3\TYPO3CR\Domain\Model\NodeData;
use TYPO3\TYPO3CR\Domain\Model\Workspace;
use TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository;

/**
 * @Flow\Scope("singleton")
 */
class MetaDataRepository extends NodeDataRepository
{
    const ENTITY_CLASSNAME = NodeData::class;

    const METADATA_ROOT_NODE_NAME = 'assets';

    /**
     * @param Asset $asset
     * @param Workspace $workspace
     *
     * @return NodeData
     */
    public function findOneByAsset(Asset $asset, Workspace $workspace)
    {
        return $this->findOneByPath('/' . self::METADATA_ROOT_NODE_NAME . '/' . $asset->getIdentifier(), $workspace);
    }

    /**
     * @param Asset $asset
     * @param Workspace $workspace
     */
    public function removeByAsset(Asset $asset, Workspace $workspace)
    {
        $nodeData = $this->findOneByAsset($asset, $workspace);
        if ($nodeData !== null) {
            $this->remove($nodeData);
        }
    }
}
