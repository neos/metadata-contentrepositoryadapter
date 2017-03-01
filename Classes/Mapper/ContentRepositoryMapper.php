<?php
namespace Neos\MetaData\ContentRepositoryAdapter\Mapper;

/*
 * This file is part of the Neos.MetaData.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeTemplate;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\Context;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Exception\NodeTypeNotFoundException;
use Neos\Eel\CompilingEvaluator;
use Neos\Eel\Exception as EelException;
use Neos\Eel\Utility as EelUtility;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\Asset;
use Neos\MetaData\ContentRepositoryAdapter\Domain\Repository\MetaDataRepository;
use Neos\MetaData\ContentRepositoryAdapter\Service\NodeService;
use Neos\MetaData\Domain\Collection\MetaDataCollection;
use Neos\MetaData\Mapper\MetaDataMapperInterface;

/**
 * @Flow\Scope("singleton")
 */
class ContentRepositoryMapper implements MetaDataMapperInterface
{
    /**
     * @Flow\Inject(lazy=FALSE)
     * @var CompilingEvaluator
     */
    protected $eelEvaluator;

    /**
     * The default context variables available inside Eel
     * @var array
     */
    protected $defaultContextVariables;

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
     * @var NodeService
     */
    protected $nodeService;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\InjectConfiguration(package="Neos.MetaData.ContentRepositoryAdapter", path="mapping")
     * @var array
     */
    protected $mappingConfiguration;

    /**
     * @Flow\InjectConfiguration(package="Neos.MetaData.ContentRepositoryAdapter", path="overwriteExisting")
     * @var array
     */
    protected $overwriteExisting;

    public function initializeObject()
    {
        $this->context = $this->contextFactory->create(['workspaceName' => 'live']);
    }

    /**
     * @param Asset $asset
     * @param MetaDataCollection $metaDataCollection
     *
     * @return void
     * @throws NodeTypeNotFoundException
     * @throws EelException
     */
    public function mapMetaData(Asset $asset, MetaDataCollection $metaDataCollection)
    {
        if ($this->overwriteExisting) {
            $this->metaDataRepository->removeByAsset($asset, $this->context->getWorkspace());
            $this->metaDataRepository->persistEntities();
            if ($asset->getResource()->isDeleted()) {
                return;
            }
        } elseif ($this->metaDataRepository->findOneByAsset($asset, $this->context->getWorkspace()) !== null) {
            if ($asset->getResource()->isDeleted()) {
                $this->metaDataRepository->removeByAsset($asset, $this->context->getWorkspace());
                $this->metaDataRepository->persistEntities();
            }
            return;
        }

        if (isset($this->mappingConfiguration['nodeTypeMappings'][$asset->getMediaType()])) {
            $nodeTypeName = $this->mappingConfiguration['nodeTypeMappings'][$asset->getMediaType()];
        } else {
            $nodeTypeName = $this->mappingConfiguration['defaultNodeType'];
        }
        $nodeType = $this->nodeTypeManager->getNodeType($nodeTypeName);

        $assetNodeTemplate = new NodeTemplate();
        $assetNodeTemplate->setNodeType($nodeType);
        $assetNodeTemplate->setName($asset->getIdentifier());
        $this->mapMetaDataToNodeData($assetNodeTemplate, $nodeType, $metaDataCollection);
        $this->nodeService->findOrCreateMetaDataRootNode($this->context)->createNodeFromTemplate($assetNodeTemplate);
    }

    /**
     * @param NodeTemplate $nodeData
     * @param NodeType $nodeType
     * @param MetaDataCollection $metaDataCollection
     *
     * @throws EelException
     */
    protected function mapMetaDataToNodeData(NodeTemplate $nodeData, NodeType $nodeType, MetaDataCollection $metaDataCollection)
    {
        if ($this->defaultContextVariables === null) {
            $this->defaultContextVariables = EelUtility::getDefaultContextVariables($this->mappingConfiguration['defaultEelContext']);
        }

        $contextVariables = array_merge($this->defaultContextVariables, $metaDataCollection->toArray());

        foreach ($nodeType->getProperties() as $propertyName => $propertyConfiguration) {
            if (isset($propertyConfiguration['mapping'])) {
                $value = EelUtility::evaluateEelExpression($propertyConfiguration['mapping'], $this->eelEvaluator, $contextVariables);
                if (!empty($value)) {
                    $nodeData->setProperty($propertyName, $value);
                }
            }
        }
    }
}
