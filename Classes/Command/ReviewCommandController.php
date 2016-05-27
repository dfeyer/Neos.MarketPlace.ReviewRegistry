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

use Neos\MarketPlace\ReviewRegistry\Domain\Model\Subject;
use Ttree\Flow\NatsIo\ConnectionFactory;
use Ttree\Flow\NatsIo\Domain\Model\Message;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;

/**
 * Review Registry Event Command Controller
 */
class ReviewCommandController extends CommandController
{
    /**
     * @var ConnectionFactory
     * @Flow\Inject
     */
    protected $connectionFactory;

    /**
     * Generate test event
     *
     * @param string $package
     */
    public function requestCommand($package)
    {
        $message = Message::create([
            'package' => (string)$package
        ]);
        $connection = $this->connectionFactory->create();
        $connection->publish(Subject::REVIEW_REQUESTED, $message->serialize());
    }
}
