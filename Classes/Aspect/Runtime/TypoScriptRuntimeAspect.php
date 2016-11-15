<?php
namespace Neos\MetaData\ContentRepositoryAdapter\Aspect\Runtime;

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
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\AOP\JoinPointInterface;

/**
 * @Flow\Aspect
 */
class TypoScriptRuntimeAspect
{
    /**
     * @Flow\Inject
     * @var \Neos\MetaData\ContentRepositoryAdapter\Service\NodeService
     */
    protected $nodeService;

    /**
     * @param JoinPointInterface $joinPoint
     * @Flow\After("method(TYPO3\TypoScript\Core\Runtime->pushContextArray())")
     * @return void
     */
    public function extendContextWithMetaDataRootNode(JoinPointInterface $joinPoint)
    {
        /** @var \TYPO3\TypoScript\Core\Runtime $runtime */
        $runtime = $joinPoint->getProxy();

        $currentContext = $runtime->getCurrentContext();

        if(isset($currentContext['node'])) {
            /** @var \TYPO3\TYPO3CR\Domain\Model\NodeInterface $node */
            $node = $currentContext['node'];

            $metaDataRootNode = $this->nodeService->findOrCreateMetaDataRootNode($node->getContext());

            $currentContext = $runtime->popContext();
            $currentContext[MetaDataRepository::METADATA_ROOT_NODE_NAME] = $metaDataRootNode;
            $runtime->pushContextArray($currentContext);
        }
    }
}
