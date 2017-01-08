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

use Neos\MetaData\ContentRepositoryAdapter\Domain\Repository\MetaDataRepository;
use Neos\MetaData\ContentRepositoryAdapter\Service\NodeService;
use Neos\MetaData\Domain\Collection\MetaDataCollection;
use Neos\MetaData\Mapper\MetaDataMapperInterface;
use TYPO3\Eel\CompilingEvaluator;
use TYPO3\Eel\Exception as EelException;
use TYPO3\Eel\Utility as EelUtility;
use Neos\Flow\Annotations as Flow;
use TYPO3\Media\Domain\Model\Asset;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use TYPO3\TYPO3CR\Domain\Model\NodeType;
use TYPO3\TYPO3CR\Domain\Service\Context;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;
use TYPO3\TYPO3CR\Exception\NodeTypeNotFoundException;

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
    protected $settings;

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
        if (isset($this->settings['nodeTypeMappings'][$asset->getMediaType()])) {
            $nodeTypeName = $this->settings['nodeTypeMappings'][$asset->getMediaType()];
        } else {
            $nodeTypeName = $this->settings['defaultNodeType'];
        }
        $nodeType = $this->nodeTypeManager->getNodeType($nodeTypeName);

        $assetNodeTemplate = new NodeTemplate();
        $assetNodeTemplate->setNodeType($nodeType);
        $assetNodeTemplate->setName($asset->getIdentifier());
        $this->mapMetaDataToNodeData($assetNodeTemplate, $nodeType, $metaDataCollection);

        $this->metaDataRepository->removeByAsset($asset, $this->context->getWorkspace());
        $this->metaDataRepository->persistEntities();
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
            $this->defaultContextVariables = EelUtility::getDefaultContextVariables($this->settings['defaultEelContext']);
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
