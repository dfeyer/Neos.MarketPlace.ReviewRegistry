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

use TYPO3\Flow\Annotations as Flow;

/**
 * CommitersReview
 *
 * @Flow\Scope("singleton")
 */
class CommitersReview extends AbstractReview
{
    /**
     * @param \Nats\Message $response
     */
    public function handleReview(\Nats\Message $response)
    {
        
    }
}