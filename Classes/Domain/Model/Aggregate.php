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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;

/**
 * Aggregate
 *
 * @Flow\Entity
 * @Flow\Scope("prototype")
 */
class Aggregate
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
     * @var string
     */
    protected $type;

    /**
     * @var integer
     */
    protected $version = 1;

    /**
     * @var ArrayCollection<Event>
     * @ORM\OneToMany(mappedBy="aggregate")
     * @ORM\OrderBy({"version" = "DESC"})
     */
    protected $events;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * Aggregate constructor
     */
    public function __construct()
    {
        $this->timestamp = new \DateTime();
        $this->events = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return integer
     */
    public function getIdentifier()
    {
        return $this->uid;
    }

    /**
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function addEvent($data)
    {
        $this->version += 1;
        $event = new Event($this, $data, $this->version);
        $this->events->add($event);
        return $this;
    }
}
