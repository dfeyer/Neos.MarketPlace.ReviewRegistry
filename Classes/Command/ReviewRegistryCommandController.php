<?php
namespace Neos\MarketPlace\ReviewRegistry\Command;

/*
 * This file is part of the Neos.MarketPlace package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Nats\Connection;
use Neos\MarketPlace\ReviewRegistry\Domain\Model\Subject;
use Neos\MarketPlace\ReviewRegistry\Service\StageService;
use Ramsey\Uuid\Uuid;
use Ttree\Flow\NatsIo\ConnectionFactory;
use Ttree\Flow\NatsIo\Domain\Model\Message;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;

/**
 * Review Registry Event Command Controller
 */
class ReviewRegistryCommandController extends CommandController
{
    /**
     * @var ConnectionFactory
     * @Flow\Inject
     */
    protected $connectionFactory;

    /**
     * @var StageService
     * @Flow\Inject
     */
    protected $reviewStageService;

    /**
     * Start the review registry
     */
    public function startCommand()
    {
        $this->outputLine();
        $this->outputLine('Review Registery started ...');
        $connection = $this->connectionFactory->create();

        $this->handleReviewRequest($connection);
        $this->discoverReviewStage($connection);

        $connection->wait();
    }

    /**
     * Debug all registry messages
     */
    public function debugCommand()
    {
        $this->outputLine();
        $this->outputLine('Review Registery debug console ...');
        $connection = $this->connectionFactory->create();
        $connection->subscribe(Subject::ALL, function (\Nats\Message $response) {
            $body = preg_replace("/\r|\n/", '', $response->getBody());
            $this->outputLine('<info>DEBUG</info> subject=%s body="%s" sid=%s', [
                $response->getSubject(),
                $body,
                $response->getSid()
            ]);
        });

        $connection->wait();
    }

    /**
     * @param Connection $connection
     */
    protected function handleReviewRequest(Connection $connection)
    {
        
        // Handle review request
        $connection->queueSubscribe(Subject::REVIEW_REQUESTED, 'start', function (\Nats\Message $response) use ($connection) {
            $message = Message::create($response, true);
            $payload = $message->getPayload();
            $package = $payload['package'];
            $this->outputLine(sprintf('<info>++</info> action=review_start package=%s', $package));

            $inbox = $this->createAndSubscribeToReviewInbox($package, $connection);

            foreach ($this->reviewStageService->all() as $stage) {
                $connection->request($stage . '.ping', null, function (\Nats\Message $response) use ($stage, $connection, $package, $inbox) {
                    if ($response->getBody() !== 'pong') {
                        $this->outputLine(sprintf('<error>++</error> action=error message="Stage %s not available"', $stage));
                        $this->reviewStageService->unregister($stage);
                    } else {
                        $message = Message::create([
                            'package' => $package,
                            'inbox' => $inbox
                        ]);
                        $connection->publish($stage, $message->serialize());
                    }
                });
            }
        });
    }

    /**
     * Create and subscribe to an INBOX for the current review
     *
     * This create a uniq INBOX for each review. This INBOX can be used to distribute event relative to
     * the current review. When the review is finished (action=review_stage_finised), the connection unsubscribe
     * automatically.
     *
     * @param string $package
     * @param Connection $connection
     * @return string
     */
    protected function createAndSubscribeToReviewInbox($package, Connection $connection)
    {
        $inbox = '_INBOX.PACKAGE.' . Uuid::uuid4()->toString();
        $this->outputLine(sprintf('<comment>++</comment> action=create_inbox package=%s inbox=%s', $package, $inbox));
        $connection->subscribe($inbox, function (\Nats\Message $response) use ($connection) {
            $message = $response->getBody();
            $this->outputLine(sprintf('<comment>!!</comment> %s', $message));
            if (strpos($message, 'action=review_stage_finised') === 0) {
                $connection->unsubscribe($response->getSid());
            }
        });
        return $inbox;
    }

    /**
     * @param Connection $connection
     */
    protected function discoverReviewStage(Connection $connection)
    {
        // Handle review stage registration
        $connection->subscribe(Subject::STAGE_REGISTRATION_REQUESTED, function (\Nats\Message $response) {
            $message = Message::create($response, true);
            $payload = $message->getPayload();
            if (!is_array($payload)) {
                // todo error handling
                return;
            }
            $stage = $payload['subject'];
            switch ($payload['command']) {
                case 'register':
                    if (!$this->reviewStageService->hasStage($stage)) {
                        $this->outputLine(sprintf('<info>++</info> action=register_stage stage=%s', $stage));
                        $this->reviewStageService->register($stage);
                    } else {
                        $this->outputLine(sprintf('<comment>~~</comment> action=skip_register_stage stage=%s', $stage));
                    }
                    break;
                case 'unregister':
                    if ($this->reviewStageService->hasStage($stage)) {
                        $this->outputLine(sprintf('<info>--</info> action=unregister_stage stage=%s', $stage));
                        $this->reviewStageService->register($stage);
                    }
                    break;
            }
        });
    }
}
