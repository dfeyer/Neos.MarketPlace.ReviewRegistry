<?php
namespace Neos\MarketPlace\ReviewRegistry\Review;

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
use Neos\MarketPlace\ReviewRegistry\Domain\Model\Group;
use Neos\MarketPlace\ReviewRegistry\Domain\Model\Subject;
use Neos\MarketPlace\ReviewRegistry\Exception;
use Ramsey\Uuid\Uuid;
use Ttree\Flow\NatsIo\Domain\Model\Message;
use TYPO3\Flow\Annotations as Flow;

/**
 * AbstractReview
 */
abstract class AbstractReview implements ReviewInterface
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var boolean
     */
    protected $initialized = false;

    /**
     * @var string
     */
    protected $sid;

    /**
     * @var string
     */
    protected $pingSid;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     * @return string
     * @throws Exception
     */
    public function initialize(Connection $connection)
    {
        $this->connection = $connection;
        $subject = $this->subject();
        $pingSubject = $this->ping();

        if ($this->sid === null) {
            $this->pingSid = $this->connection->subscribe($pingSubject, function (\Nats\Message $response) {
                $this->handlePing($response);
            });
            $this->sid = $this->connection->queueSubscribe($subject, Group::REVIEW_STAGE, function (\Nats\Message $response) {
                $message = Message::create($response, true);
                $payload = $message->getPayload();
                if (!isset($payload['inbox'])) {
                    // Missing inbox handle error
                    return;
                }
                if (!isset($payload['package'])) {
                    // Missing package handle error
                    return;
                }
                $this->log($payload, ReviewInterface::ACTION_STARTED);

                $this->handleReview($response);

                $this->log($payload, ReviewInterface::ACTION_FINISHED);
            });
            $this->identifier = Uuid::uuid4()->toString();
        }

        // Register the review stage
        $message = Message::create([
            'command' => 'register',
            'subject' => $subject,
            'identifier' => $this->identifier
        ]);
        $connection->publish(Subject::STAGE_REGISTRATION_REQUESTED, $message->serialize());

        return $this->sid;
    }

    /**
     * @return string
     */
    public function subject()
    {
        $name = str_replace('\\', '.', strtolower(get_called_class()));
        return str_replace('{name}', $name, Subject::STAGE_PATTERN);
    }

    /**
     * @return string
     */
    public function ping()
    {
        $name = str_replace('\\', '.', strtolower(get_called_class()));
        return str_replace('{name}', $name, Subject::STAGE_PING_PATTERN);
    }

    /**
     * @param string $action
     * @param array $payload
     * @param string $message
     */
    protected function log(array $payload, $action, $message = null)
    {
        if ($message === null) {
            $message = sprintf('action=%s stage=%s identifier=%s package=%s', $action, $this->subject(), $this->identifier, $payload['package']);
        } else {
            $message = sprintf('action=%s stage=%s identifier=%s package=%s message="%s"', $action, $this->subject(), $this->identifier, $payload['package'], $message);
        }
        $this->connection->publish($payload['inbox'], $message);
    }

    /**
     * @param \Nats\Message $response
     */
    protected function handlePing(\Nats\Message $response)
    {
        $response->reply('pong');
    }

    /**
     * Unsubscribe to the active SIDs
     */
    public function __destruct()
    {
        $this->connection->unsubscribe($this->sid);
        $this->connection->unsubscribe($this->pingSid);
    }
}
