<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MarvelCharacter
 *
 * @ORM\Table(name="marvel_character")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MarvelCharacterRepository")
 */
class MarvelCharacter
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="foreignId", type="integer")
     */
    private $foreignId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="thumbnailUrl", type="string", length=255)
     */
    private $thumbnailUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="friendlyName", type="string", length=255, unique=true)
     */
    private $friendlyName;

    /**
     * @var int
     *
     * @ORM\Column(name="numComics", type="integer")
     */
    private $numComics;

    /**
     * @var int
     *
     * @ORM\Column(name="numSeries", type="integer")
     */
    private $numSeries;

    /**
     * @var int
     *
     * @ORM\Column(name="numEvents", type="integer")
     */
    private $numEvents;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="favourites")
     */
    private $favouritedBy;

    /**
     * @var boolean
     */
    private $favourite = false;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set foreignId
     *
     * @param integer $foreignId
     * @return MarvelCharacter
     */
    public function setForeignId($foreignId)
    {
        $this->foreignId = $foreignId;

        return $this;
    }

    /**
     * Get foreignId
     *
     * @return integer
     */
    public function getForeignId()
    {
        return $this->foreignId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return MarvelCharacter
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return MarvelCharacter
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set thumbnailUrl
     *
     * @param string $thumbnailUrl
     * @return MarvelCharacter
     */
    public function setThumbnailUrl($thumbnailUrl)
    {
        $this->thumbnailUrl = $thumbnailUrl;

        return $this;
    }

    /**
     * Get thumbnailUrl
     *
     * @return string
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnailUrl;
    }

    /**
     * Set friendlyName
     *
     * @param string $friendlyName
     * @return MarvelCharacter
     */
    public function setFriendlyName($friendlyName)
    {
        $this->friendlyName = $friendlyName;

        return $this;
    }

    /**
     * Get friendlyName
     *
     * @return string 
     */
    public function getFriendlyName()
    {
        return $this->friendlyName;
    }

    /**
     * Set numComics
     *
     * @param integer $numComics
     * @return MarvelCharacter
     */
    public function setNumComics($numComics)
    {
        $this->numComics = $numComics;

        return $this;
    }

    /**
     * Get numComics
     *
     * @return integer 
     */
    public function getNumComics()
    {
        return $this->numComics;
    }

    /**
     * Set numSeries
     *
     * @param integer $numSeries
     * @return MarvelCharacter
     */
    public function setNumSeries($numSeries)
    {
        $this->numSeries = $numSeries;

        return $this;
    }

    /**
     * Get numSeries
     *
     * @return integer 
     */
    public function getNumSeries()
    {
        return $this->numSeries;
    }

    /**
     * Set numEvents
     *
     * @param integer $numEvents
     * @return MarvelCharacter
     */
    public function setNumEvents($numEvents)
    {
        $this->numEvents = $numEvents;

        return $this;
    }

    /**
     * Get numEvents
     *
     * @return integer 
     */
    public function getNumEvents()
    {
        return $this->numEvents;
    }

    /**
     * Set favourite
     *
     * @param boolean $favourite
     * @return MarvelCharacter
     */
    public function setFavourite($favourite)
    {
        $this->favourite = $favourite;

        return $this;
    }

    /**
     * Get favourite
     *
     * @return boolean
     */
    public function getFavourite()
    {
        return $this->favourite;
    }
}
