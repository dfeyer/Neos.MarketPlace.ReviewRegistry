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

use Neos\MarketPlace\ReviewRegistry\Service\ReviewService;
use Ttree\Flow\NatsIo\ConnectionFactory;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;

/**
 * Review Registry Event Command Controller
 */
class ReviewSatelliteCommandController extends CommandController
{
    /**
     * @var ConnectionFactory
     * @Flow\Inject
     */
    protected $connectionFactory;

    /**
     * @var ReviewService
     * @Flow\Inject
     */
    protected $reviewService;

    /**
     * Register available review
     */
    public function startCommand()
    {
        $this->outputLine();
        $this->outputLine('Review Satellite started ...');
        $connection = $this->connectionFactory->create();

        $this->reviewService->initialize($connection);

        $connection->wait();
    }
}
