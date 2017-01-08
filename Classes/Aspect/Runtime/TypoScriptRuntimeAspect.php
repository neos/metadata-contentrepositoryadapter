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
use Neos\MetaData\ContentRepositoryAdapter\Service\NodeService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Reflection\ObjectAccess;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TypoScript\Core\Runtime;

/**
 * @Flow\Aspect
 */
class TypoScriptRuntimeAspect
{
    /**
     * @Flow\Inject
     * @var NodeService
     */
    protected $nodeService;

    /**
     * @param JoinPointInterface $joinPoint
     * @Flow\Before("method(TYPO3\TypoScript\Core\Runtime->pushContextArray())")
     *
     * @return void
     */
    public function extendContextArrayWithMetaDataRootNode(JoinPointInterface $joinPoint)
    {
        $contextArray = $joinPoint->getMethodArgument('contextArray');
        if (isset($contextArray['node'])) {
            /** @var NodeInterface $node */
            $node = $contextArray['node'];
            $contextArray[MetaDataRepository::METADATA_ROOT_NODE_NAME] = $this->nodeService->findOrCreateMetaDataRootNode($node->getContext());
            $joinPoint->setMethodArgument('contextArray', $contextArray);
        }
    }

    /**
     * @param JoinPointInterface $joinPoint
     * @Flow\AfterReturning("method(TYPO3\TypoScript\Core\Runtime->pushContext(key == 'node'))")
     *
     * @return void
     */
    public function extendContextWithMetaDataRootNode(JoinPointInterface $joinPoint)
    {
        /** @var Runtime $runtime */
        $runtime = $joinPoint->getProxy();

        $renderingStack = ObjectAccess::getProperty($runtime, 'renderingStack', true);
        $contextArray = array_pop($renderingStack);

        /** @var NodeInterface $node */
        $node = $contextArray['node'];
        $contextArray[MetaDataRepository::METADATA_ROOT_NODE_NAME] = $this->nodeService->findOrCreateMetaDataRootNode($node->getContext());

        $renderingStack[] = $contextArray;
        ObjectAccess::setProperty($runtime, 'renderingStack', $renderingStack);
    }
}
