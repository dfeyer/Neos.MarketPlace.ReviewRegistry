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
use Nats\Message;
use Neos\MarketPlace\ReviewRegistry\Exception;

/**
 * ReviewInterface
 *
 * @api
 */
interface ReviewInterface
{
    const ACTION_STARTED = 'review_stage_started';
    const ACTION_FINISHED = 'review_stage_finised';

    /**
     * @param Connection $connection
     * @return string
     * @throws Exception
     */
    public function initialize(Connection $connection);

    /**
     * @param Message $response
     */
    public function handleReview(Message $response);

    /**
     * @return string
     */
    public function subject();

    /**
     * @return string
     */
    public function ping();
}
