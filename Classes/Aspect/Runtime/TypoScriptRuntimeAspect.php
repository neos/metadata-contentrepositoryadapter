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

use TYPO3\Flow\Annotations as Flow;

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
     * @param \TYPO3\Flow\AOP\JoinPointInterface $joinPoint
     * @Flow\After("method(TYPO3\TypoScript\Core\Runtime->pushContextArray())")
     * @return void
     */
    public function extendContextWithMetaDataRootNode(\TYPO3\Flow\AOP\JoinPointInterface $joinPoint)
    {
        /** @var \TYPO3\TypoScript\Core\Runtime $runtime */
        $runtime = $joinPoint->getProxy();

        $currentContext = $runtime->getCurrentContext();

        if(isset($currentContext['node'])) {
            /** @var \TYPO3\TYPO3CR\Domain\Model\NodeInterface $node */
            $node = $currentContext['node'];

            $metaDataRootNode = $this->nodeService->findOrCreateMetaDataRootNode($node->getContext());

            $runtime->pushContext('meta', $metaDataRootNode);
        }
    }
}