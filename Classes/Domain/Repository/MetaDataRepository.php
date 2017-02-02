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

use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\Asset;

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
