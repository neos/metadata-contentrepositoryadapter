<?php
namespace Neos\MetaData\ContentRepositoryAdapter\Eel\FlowQueryOperations;

/*
 * This file is part of the Neos.MetaData.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Repository\AssetRepository;

/**
 * EEL operation to get the assets from meta data nodes
 */
class GetAssetsOperation extends AbstractOperation
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    static protected $shortName = 'getAssets';

    /**
     * {@inheritdoc}
     *
     * @var integer
     */
    static protected $priority = 100;

    /**
     * @var boolean
     */
    protected static $final = true;

    /**
     * @Flow\Inject
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * {@inheritdoc}
     *
     * We can only handle TYPO3CR Nodes.
     *
     * @param array $context
     *
     * @return boolean
     */
    public function canEvaluate($context)
    {
        foreach ($context as $contextNode) {
            if (!($contextNode instanceof NodeInterface)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery $flowQuery the FlowQuery object
     * @param array $arguments the arguments for this operation
     *
     * @return array
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments)
    {
        $assets = [];
        foreach ($flowQuery->getContext() as $metaDataNode) {
            /** @var NodeInterface $metaDataNode */
            $assetIdentifier = $metaDataNode->getNodeData()->getName();
            $assets[$assetIdentifier] = $this->assetRepository->findByIdentifier($assetIdentifier);
        }

        return $assets;
    }
}
