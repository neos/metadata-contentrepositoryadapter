<?php
namespace Neos\MetaData\ContentRepositoryAdapter\Eel\FlowQueryOperations;


use TYPO3\Eel\FlowQuery\Operations\AbstractOperation;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Eel\FlowQuery\FlowQuery;

/**
 * EEL operation to get the assets from meta data nodes
 */
class GetAssetsOperation extends AbstractOperation {

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
     * @var array
     */
    protected $metaDataNodes;

    /**
     * @Flow\Inject
     * @var \TYPO3\Media\Domain\Repository\AssetRepository
     */
    protected $assetRepository;

    /**
     * {@inheritdoc}
     *
     * We can only handle TYPO3CR Nodes.
     *
     * @param mixed $context
     * @return boolean
     */
    public function canEvaluate($context) {

        if (isset($context[0]) && ($context[0] instanceof NodeInterface) || is_array($context[0])) {
            $this->metaDataNodes = is_array($context[0]) ? $context[0] : [$context[0]];
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery $flowQuery the FlowQuery object
     * @param array $arguments the arguments for this operation
     * @return \DateTime
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments) {

        $assets = [];
        foreach($this->metaDataNodes as $metaDataNode) {

            if($metaDataNode instanceof NodeInterface) {
                $assetIdentifier = $metaDataNode->getNodeData()->getName();
                $assets[] = $this->assetRepository->findByIdentifier($assetIdentifier);
            }
        }

        return $assets;
    }
}
