<?php
namespace Neos\MetaData\ContentRepositoryAdapter\Eel\FlowQueryOperations;


use TYPO3\Eel\FlowQuery\Operations\AbstractOperation;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Eel\FlowQuery\FlowQuery;

/**
 * EEL operation to get the metaData of an image
 */
class MetaDataOperation extends AbstractOperation {

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
    public function canEvaluate($context) {
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
     * @return \DateTime
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments) {
        $imagePropertyName = $arguments[0];
        if($this->contextNode->hasProperty($imagePropertyName)) {

           $imageArray = $this->contextNode->getProperties($arguments[0]);
           $image = $imageArray['image'];

           $identifier = $image->getidentifier();

            $nodeData = $this->metaDataRepository->findOneByAssetIdentifier($identifier, $this->contextNode->getContext());
            return $this->nodeFactory->createFromNodeData($nodeData, $this->contextNode->getContext());
        }
    }
}
