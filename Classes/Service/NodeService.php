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

use TYPO3\Flow\Annotations as Flow;
use Neos\MetaData\ContentRepositoryAdapter\Domain\Repository\MetaDataRepository;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\TYPO3CR\Domain\Service\Context;
use TYPO3\TYPO3CR\Domain\Factory\NodeFactory;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;
use TYPO3\TYPO3CR\Exception\NodeConfigurationException;
use TYPO3\TYPO3CR\Exception\NodeTypeNotFoundException;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

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
     * @var \TYPO3\TYPO3CR\Domain\Service\NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @var \TYPO3\TYPO3CR\Domain\Model\NodeInterface
     */
    protected $metaDataRootNode = null;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @param Context $context
     * @return NodeInterface
     * @throws NodeTypeNotFoundException
     * @throws NodeConfigurationException
     */
    public function findOrCreateMetaDataRootNode(Context $context)
    {
        if($this->metaDataRootNode instanceof NodeInterface) {
            return $this->metaDataRootNode;
        }

        $metaDataRootNodeData = $this->metaDataRepository->findOneByPath('/' . MetaDataRepository::METADATA_ROOT_NODE_NAME, $context->getWorkspace());

        if ($metaDataRootNodeData !== null) {
            $metaDataRootNode = $this->nodeFactory->createFromNodeData($metaDataRootNodeData, $context);
            return $metaDataRootNode;
        }

        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('unstructured'));
        $nodeTemplate->setName(MetaDataRepository::METADATA_ROOT_NODE_NAME);

        $context = $this->contextFactory->create(['workspaceName' => 'live']);
        $rootNode = $context->getRootNode();

        $this->metaDataRootNode = $rootNode->createNodeFromTemplate($nodeTemplate);
        $this->persistenceManager->persistAll();
        return $this->metaDataRootNode;
    }

}
