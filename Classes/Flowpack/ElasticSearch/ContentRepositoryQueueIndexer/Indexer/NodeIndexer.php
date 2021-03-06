<?php
namespace Flowpack\ElasticSearch\ContentRepositoryQueueIndexer\Indexer;

use Flowpack\ElasticSearch\ContentRepositoryAdaptor;
use Flowpack\ElasticSearch\ContentRepositoryQueueIndexer\Command\NodeIndexQueueCommandController;
use Flowpack\ElasticSearch\ContentRepositoryQueueIndexer\IndexingJob;
use Flowpack\JobQueue\Common\Job\JobManager;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * ElasticSearch Indexing Job Interface
 */
class NodeIndexer extends ContentRepositoryAdaptor\Indexer\NodeIndexer
{
    /**
     * @var JobManager
     * @Flow\Inject
     */
    protected $jobManager;

    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

    /**
     * @var bool
     * @Flow\InjectConfiguration(path="enableLiveAsyncIndexing")
     */
    protected $enableLiveAsyncIndexing;

    /**
     * @param NodeInterface $node
     * @param string|null $targetWorkspaceName
     */
    public function indexNode(NodeInterface $node, $targetWorkspaceName = null)
    {
        if ($targetWorkspaceName === null) {
            return;
        }
        if ($this->enableLiveAsyncIndexing !== true) {
            parent::indexNode($node, $targetWorkspaceName);
            return;
        }
        $indexingJob = new IndexingJob($this->indexNamePostfix, $targetWorkspaceName, [
            [
                'nodeIdentifier' => $this->persistenceManager->getIdentifierByObject($node->getNodeData()),
                'dimensions' => $node->getDimensions()
            ]
        ]);
        $this->jobManager->queue(NodeIndexQueueCommandController::LIVE_QUEUE_NAME, $indexingJob);
    }
}
