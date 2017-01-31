<?php
namespace Neos\MarketPlace\ReviewRegistry\Domain\Model;

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
 * Subject
 */
class Subject
{
    /**
     * Listen to all subject (mainly for debugging)
     */
    const ALL = 'neos.marketplace.review.>';

    /**
     * Review requested
     */
    const REVIEW_REQUESTED = 'neos.marketplace.review.registry.reviewrequested';

    /**
     * Stage Registration Requested
     */
    const STAGE_REGISTRATION_REQUESTED = 'neos.marketplace.review.registry.stageregistrationrequested';

    /**
     * Stage Pattern
     */
    const STAGE_PATTERN = 'neos.marketplace.review.stage.{name}';

    /**
     * Stage Pattern
     */
    const STAGE_PING_PATTERN = 'neos.marketplace.review.stage.{name}.ping';
}
