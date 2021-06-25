<?php
namespace Datavis\Entity;

use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Site;
use Omeka\Entity\User;

/**
 * @Entity
 * @HasLifecycleCallbacks
 */
class DatavisVis extends AbstractEntity
{
    /**
     * @Id
     * @Column(
     *     type="integer",
     *     options={
     *         "unsigned"=true
     *     }
     * )
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ManyToOne(
     *     targetEntity="Omeka\Entity\User"
     * )
     * @JoinColumn(
     *     nullable=true,
     *     onDelete="SET NULL"
     * )
     */
    protected $owner;

    /**
     * @ManyToOne(
     *     targetEntity="Omeka\Entity\Site"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $site;

    /**
     * @Column(
     *     type="datetime",
     *     nullable=false
     * )
     */
    protected $created;

    /**
     * @Column(
     *     type="datetime",
     *     nullable=true
     * )
     */
    protected $modified;

    /**
     * @Column(
     *     type="datetime",
     *     nullable=true
     * )
     */
    protected $datasetModified;

    /**
     * @Column(
     *     type="string",
     *     length=255,
     *     nullable=false
     * )
     */
    protected $datasetType;

    /**
     * @Column(
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     */
    protected $diagramType;

    /**
     * @Column(
     *     type="json",
     *     nullable=false
     * )
     */
    protected $datasetData;

    /**
     * @Column(
     *     type="json",
     *     nullable=false
     * )
     */
    protected $diagramData;

    /**
     * @Column(
     *     type="json",
     *     nullable=true
     * )
     */
    protected $dataset;

    /**
     * @Column(
     *     type="string",
     *     length=255,
     *     nullable=false
     * )
     */
    protected $title;

    /**
     * @Column(
     *     type="text",
     *     nullable=true
     * )
     */
    protected $description;

    /**
     * @Column(
     *     type="text",
     *     nullable=false
     * )
     */
    protected $query;

    public function getId()
    {
        return $this->id;
    }

    public function setOwner(?User $owner = null) : void
    {
        $this->owner = $owner;
    }

    public function getOwner() : ?User
    {
        return $this->owner;
    }

    public function setSite(Site $site) : void
    {
        $this->site = $site;
    }

    public function getSite() : Site
    {
        return $this->site;
    }

    public function setCreated(DateTime $created) : void
    {
        $this->created = $created;
    }

    public function getCreated() : DateTime
    {
        return $this->created;
    }

    public function setModified(DateTime $modified) : void
    {
        $this->modified = $modified;
    }

    public function getModified() : ?DateTime
    {
        return $this->modified;
    }

    public function setDatasetModified(DateTime $datasetModified) : void
    {
        $this->datasetModified = $datasetModified;
    }

    public function getDatasetModified() : ?DateTime
    {
        return $this->datasetModified;
    }

    public function setDatasetType(string $datasetType) : void
    {
        $this->datasetType = $datasetType;
    }

    public function getDatasetType() : string
    {
        return $this->datasetType;
    }

    public function setDiagramType(?string $diagramType) : void
    {
        if (is_string($diagramType) && '' === trim($diagramType)) {
            $diagramType = null;
        }
        $this->diagramType = $diagramType;
    }

    public function getDiagramType() : ?string
    {
        return $this->diagramType;
    }

    public function setDatasetData(array $datasetData) : void
    {
        $this->datasetData = $datasetData;
    }

    public function getDatasetData() : array
    {
        return $this->datasetData;
    }

    public function setDiagramData(array $diagramData) : void
    {
        $this->diagramData = $diagramData;
    }

    public function getDiagramData() : array
    {
        return $this->diagramData;
    }

    public function setDataset(?array $dataset) : void
    {
        $this->dataset = $dataset;
    }

    public function getDataset() : ?array
    {
        return $this->dataset;
    }

    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setDescription(?string $description) : void
    {
        if (is_string($description) && '' === trim($description)) {
            $description = null;
        }
        $this->description = $description;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function setQuery(string $query) : void
    {
        $this->query = $query;
    }

    public function getQuery() : string
    {
        return $this->query;
    }

    /**
     * @PrePersist
     */
    public function prePersist(LifecycleEventArgs $eventArgs) : void
    {
        $this->setCreated(new DateTime('now'));
    }
}
