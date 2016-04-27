<?php
namespace Neos\MetaData\ContentRepositoryAdapter\Mapper;

use Neos\MetaData\ContentRepositoryAdapter\Domain\Repository\MetaDataRepository;
use Neos\MetaData\Domain\Collection\MetaDataCollection;
use Neos\MetaData\Domain\Dto;
use Neos\MetaData\Mapper\MetaDataMapperInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Domain\Service\NodeSearchService;
use TYPO3\TYPO3CR\Domain\Factory\NodeFactory;
use TYPO3\TYPO3CR\Domain\Model\AbstractNodeData;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use TYPO3\TYPO3CR\Domain\Model\NodeType;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;
use TYPO3\TYPO3CR\Exception\NodeTypeNotFoundException;
use TYPO3\Eel\Utility as EelUtility;


class ContentRepositoryMapper implements MetaDataMapperInterface
{

    /**
     * @Flow\Inject(lazy=FALSE)
     * @var \TYPO3\Eel\CompilingEvaluator
     */
    protected $eelEvaluator;

    /**
     * @Flow\Inject
     * @var MetaDataRepository
     */
    protected $metaDataRepository;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var NodeSearchService
     */
    protected $nodeSearchService;

    /**
     * @var \TYPO3\TYPO3CR\Domain\Service\Context
     */
    protected $context;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var NodeInterface
     */
    protected $rootNode;


    public function initializeObject()
    {
        $this->context = $this->contextFactory->create(['workspaceName' => 'live']);
        $this->rootNode = $this->context->getRootNode();
    }

    /**
     * @param \TYPO3\Media\Domain\Model\Asset $asset
     * @param MetaDataCollection $metaDataCollection
     * @throws NodeTypeNotFoundException
     * @throws \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException
     * @return void
     */
    public function mapMetaData(\TYPO3\Media\Domain\Model\Asset $asset, MetaDataCollection $metaDataCollection)
    {
        $nodeType = $this->nodeTypeManager->getNodeType('Neos.MetaData:Image');
        $asset = $metaDataCollection->get('asset');

        $assetNodeData = $this->metaDataRepository->findOneByAssetIdentifier($asset->getIdentifier(), $this->context);
        if ($assetNodeData === null) {
            $assetNodeDataTemplate = $this->createAssetNodeTemplate($asset, $nodeType);
            $this->mapMetaDataToNdoeData($assetNodeDataTemplate, $nodeType, $metaDataCollection);
            $this->findOrCreateMetaDataRootNode()->createNodeFromTemplate($assetNodeDataTemplate);
        } else {
            $this->mapMetaDataToNdoeData($assetNodeData, $nodeType, $metaDataCollection);
            $this->metaDataRepository->update($assetNodeData);
        }
    }

    /**
     * @param AbstractNodeData $nodeData
     * @param NodeType $nodeType
     * @param MetaDataCollection $metaDataCollection
     * @throws \TYPO3\Eel\Exception
     */
    protected function mapMetaDataToNdoeData(AbstractNodeData $nodeData, NodeType $nodeType, MetaDataCollection $metaDataCollection)
    {
        foreach ($nodeType->getProperties() as $propertyName => $propertyConfiguration) {
            if (isset($propertyConfiguration['mapping'])) {
                $nodeData->setProperty($propertyName, EelUtility::evaluateEelExpression($propertyConfiguration['mapping'], $this->eelEvaluator, $metaDataCollection->toArray()));
            }
        }
    }

    /**
     * @return NodeInterface
     * @throws NodeTypeNotFoundException
     */
    protected function findOrCreateMetaDataRootNode()
    {
        $metaDataRootNodeData = $this->metaDataRepository->findOneByPath('/' . MetaDataRepository::METADATA_ROOT_NODE_NAME, $this->context->getWorkspace());

        if ($metaDataRootNodeData !== null) {
            $metaDataRootNode = $this->nodeFactory->createFromNodeData($metaDataRootNodeData, $this->context);
            return $metaDataRootNode;
        }

        $nodeTemplate = new NodeTemplate();
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('unstructured'));
        $nodeTemplate->setName(MetaDataRepository::METADATA_ROOT_NODE_NAME);
        return $this->rootNode->createNodeFromTemplate($nodeTemplate);
    }

    /**
     * @param Dto\Asset $asset
     * @param NodeType $nodeType
     * @return NodeTemplate
     */
    protected function createAssetNodeTemplate(Dto\Asset $asset, NodeType $nodeType)
    {
        $assetNodeTemplate = new NodeTemplate();
        $assetNodeTemplate->setNodeType($nodeType);
        $assetNodeTemplate->setName($asset->getIdentifier());

        return $assetNodeTemplate;
    }
}