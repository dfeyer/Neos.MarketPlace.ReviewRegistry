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
use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @Flow\Entity
 * @Flow\Scope("prototype")
 */
class Event
{
    /**
     * When was this event?
     *
     * @var \DateTime
     */
    protected $timestamp;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(nullable=true, options={"unsigned"=true})
     */
    protected $uid;

    /**
     * @var Aggregate
     * @ORM\ManyToOne(inversedBy="events")
     */
    protected $aggregate;

    /**
     * @var array<mixed>
     * @ORM\Column(type="flow_json_array")
     */
    protected $data;

    /**
     * @var integer
     */
    protected $version;

    /**
     * Event constructor.
     * @param Aggregate $aggregate
     * @param array $data
     * @param int $version
     */
    public function __construct(Aggregate $aggregate, array $data, $version)
    {
        $this->timestamp = new \DateTime();
        $this->aggregate = $aggregate;
        $this->data = $data;
        $this->version = $version;
    }
}
