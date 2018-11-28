<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User implements UserInterface
{
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials()
    {
    }

    public function getSalt()
    {
        return null;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity="MarvelCharacter", inversedBy="favouritedBy")
     * @ORM\JoinTable(name="user_favourites",
     *      joinColumns={@ORM\JoinColumn(name="user_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="marvel_character_id")}
     *      )
     */
    private $favourites;

    public function __construct()
    {
        $this->favourites = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Add favourite
     *
     * @param object $character
     * @return User
     */
    public function addFavourite($character)
    {
        if (!$this->favourites->contains($character)) {
            $this->favourites[] = $character;
        }

        return $this;
    }

    /**
     * Remove favourite
     *
     * @param object $character
     * @return User
     */
    public function removeFavourite($character)
    {
        $this->favourites->removeElement($character);

        return $this;
    }

    /**
     * Set favourites
     *
     * @param array $favourites
     * @return User
     */
    public function setFavourites($favourites)
    {
        $this->favourites = $favourites;

        return $this;
    }

    /**
     * Get favourites
     *
     * @return array
     */
    public function getFavourites()
    {
        $favourites = $this->favourites;

        // Set the flag
        foreach ($favourites as $favourite) {
            $favourite->setFavourite(true);
        }

        return $favourites;
    }
}
