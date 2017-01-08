<?php
namespace Neos\MetaData\ContentRepositoryAdapter\Service;

/*
 * This file is part of the Neos.MetaData.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\MetaData\ContentRepositoryAdapter\Domain\Repository\MetaDataRepository;
use Neos\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Factory\NodeFactory;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use TYPO3\TYPO3CR\Domain\Service\Context;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;

/**
 * @Flow\Scope("singleton")
 */
class NodeService
{
    /**
     * @Flow\Inject
     * @var MetaDataRepository
     */
    protected $metaDataRepository;

    /**
     * @Flow\Inject
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @var NodeInterface
     */
    protected $metaDataRootNode;

    /**
     * @param Context $context
     *
     * @return NodeInterface
     */
    public function findOrCreateMetaDataRootNode(Context $context)
    {
        if ($this->metaDataRootNode instanceof NodeInterface) {
            return $this->metaDataRootNode;
        }

        $metaDataRootNodeData = $this->metaDataRepository->findOneByPath('/' . MetaDataRepository::METADATA_ROOT_NODE_NAME, $context->getWorkspace());

        if ($metaDataRootNodeData !== null) {
            $this->metaDataRootNode = $this->nodeFactory->createFromNodeData($metaDataRootNodeData, $context);

            return $this->metaDataRootNode;
        }

        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('unstructured'));
        $nodeTemplate->setName(MetaDataRepository::METADATA_ROOT_NODE_NAME);

        $rootNode = $context->getRootNode();

        $this->metaDataRootNode = $rootNode->createNodeFromTemplate($nodeTemplate);
        $this->metaDataRepository->persistEntities();

        return $this->metaDataRootNode;
    }
}
