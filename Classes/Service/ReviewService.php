<?php
namespace Neos\MarketPlace\ReviewRegistry\Service;

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
use Neos\MarketPlace\ReviewRegistry\Review\ReviewInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManager;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * Review Service
 *
 * @Flow\Scope("singleton")
 * @api
 */
class ReviewService
{
    /**
     * @var ReflectionService
     * @Flow\Inject
     */
    protected $reflexionService;

    /**
     * @var ObjectManager
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @param Connection $connection
     */
    public function initialize(Connection $connection)
    {
        $reviews = $this->reflexionService->getAllImplementationClassNamesForInterface(ReviewInterface::class);

        $register = function () use ($reviews, $connection) {
            foreach ($reviews as $review) {
                /** @var ReviewInterface $review */
                $review = $this->objectManager->get($review);
                $review->initialize($connection);
            }
        };

        // Announce review steps
        $register();

        // Handle review step discovery
        $connection->subscribe(Subject::SERVICE_DISCOVERY_REQUESTED, $register);
    }
}
