<?php

namespace LoewenstarkSpam\Models;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="s_loespam_penalty")
 * @ORM\Entity
 */
class Penalty
{
    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $ip
     *
     * @ORM\Column(type="string", length=500, nullable=false)
     */
    private $ip;


    /**
     * @var string $penalty
     *
     * @ORM\Column(type="integer", length=500, nullable=false)
     */
    private $penalty;

    /**
     * @var string $created_at
     *
     * @ORM\Column(type="time", length=500, nullable=false)
     */
    private $created_at;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getPenalty()
    {
        return $this->penalty;
    }

    /**
     * @param string $pid
     */
    public function setPenalty($penalty)
    {
        $this->penalty = $penalty;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param string $url
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }
}
