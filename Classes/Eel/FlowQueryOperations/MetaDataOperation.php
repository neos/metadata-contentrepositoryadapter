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

use TYPO3\Eel\FlowQuery\Operations\AbstractOperation;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Media\Domain\Model\Image;
use TYPO3\Media\Domain\Model\ImageVariant;
use TYPO3\TYPO3CR\Domain\Model\NodeData;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Eel\FlowQuery\FlowQuery;

/**
 * EEL operation to get the metaData of an image
 */
class MetaDataOperation extends AbstractOperation
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    static protected $shortName = 'metaData';

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
     * @var NodeInterface
     */
    protected $contextNode;

    /**
     * @Flow\Inject
     * @var \Neos\MetaData\ContentRepositoryAdapter\Domain\Repository\MetaDataRepository
     */
    protected $metaDataRepository;

    /**
     * @Flow\Inject
     * @var \TYPO3\TYPO3CR\Domain\Factory\NodeFactory
     */
    protected $nodeFactory;

    /**
     * {@inheritdoc}
     *
     * We can only handle TYPO3CR Nodes.
     *
     * @param mixed $context
     * @return boolean
     */
    public function canEvaluate($context)
    {
        if (isset($context[0]) && ($context[0] instanceof NodeInterface)) {
            $this->contextNode = $context[0];
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery $flowQuery the FlowQuery object
     * @param array $arguments the arguments for this operation
     * @return mixed|null if the operation is final, the return value
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments)
    {
        $imagePropertyName = $arguments[0];
        if ($this->contextNode->hasProperty($imagePropertyName)) {
            $image = $this->contextNode->getProperty($imagePropertyName);
            if ($image instanceof ImageVariant) {
                $image = $image->getOriginalAsset();
            }
            if ($image instanceof Image) {
                $identifier = $image->getIdentifier();
                $nodeData = $this->metaDataRepository->findOneByAssetIdentifier($identifier, $this->contextNode->getContext()->getWorkspace());
                if ($nodeData instanceof NodeData) {
                    return $this->nodeFactory->createFromNodeData($nodeData, $this->contextNode->getContext());
                }
            }
        }

        return null;
    }
}
