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

use TYPO3\Flow\Annotations as Flow;

/**
 * Stage Service
 *
 * @Flow\Scope("singleton")
 * @api
 */
class StageService
{
    /**
     * @var array
     */
    protected $stageRegister = [];

    /**
     * @param string $stage
     */
    public function register($stage)
    {
        $this->stageRegister[$stage] = true;
    }

    /**
     * @param string $stage
     */
    public function unregister($stage)
    {
        $this->stageRegister[$stage] = false;
    }

    /**
     * @param string $stage
     * @return boolean
     */
    public function hasStage($stage)
    {
        return isset($this->stageRegister[$stage]) && $this->stageRegister[$stage] === true;
    }

    /**
     * @return array
     */
    public function all()
    {
        return array_keys(array_filter($this->stageRegister));
    }
}
