<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", mappedBy="posts")
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="parentPost", orphanRemoval=true)
     */
    private $comments;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->categories = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->addPost($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            $category->removePost($this);
        }

        return $this;
    }

    /**
     * @return Object[]
     * returns JSON friendly object array of $this-post categories names
     */
    public function getCategoriesData() 
    {
        $categories = [];

        foreach($this->categories as $category) {
            array_push($categories, $category->getName());
        }

        return $categories;
    }

    /**
     * @return Object
     * returns JSON friendly object of post data
     */
    public function getData()
    {
        return [
            'title' => $this->title,
            'id' => $this->id,
            'categories' => $this->getCategoriesData(),
            'body' => $this->body,
            'date' => $this->date,
            'comments' => $this->getCommentsData(),
            'owner' => $this->owner->getUsername()
        ];
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setParentPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getParentPost() === $this) {
                $comment->setParentPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Array
     * returns array of comments
     */
    public function getCommentsData() 
    {
        $comments = [];
        foreach($this->comments as $comment) {
            array_push($comments, $comment->getData());
        }

        return $comments;
    }
}
