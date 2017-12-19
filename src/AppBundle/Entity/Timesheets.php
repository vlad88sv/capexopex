<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Timesheets
 *
 * @ORM\Table(name="timesheets", indexes={@ORM\Index(name="type", columns={"type"}), @ORM\Index(name="FK_timesheets_resources", columns={"resourceId"}), @ORM\Index(name="status", columns={"status"}), @ORM\Index(name="hours", columns={"hours"}), @ORM\Index(name="taskId", columns={"taskId"}), @ORM\Index(name="FK_timesheets_projects", columns={"projectId"})})
 * @ORM\Entity
 */
class Timesheets
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="hours", type="float", precision=10, scale=0, nullable=false)
     */
    private $hours = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="adminTypeId", type="integer", nullable=true)
     */
    private $admintypeid = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="taskId", type="integer", nullable=true)
     */
    private $taskid = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="type", type="boolean", nullable=false)
     */
    private $type = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="taskName", type="string", length=250, nullable=false)
     */
    private $taskname;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    private $status = '0';

    /**
     * @var array
     *
     * @ORM\Column(name="task", type="json_array", nullable=false)
     */
    private $task;

    /**
     * @var \Projects
     *
     * @ORM\ManyToOne(targetEntity="Projects")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="projectId", referencedColumnName="id")
     * })
     */
    private $projectid;

    /**
     * @var \Resources
     *
     * @ORM\ManyToOne(targetEntity="Resources")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="resourceId", referencedColumnName="id")
     * })
     */
    private $resourceid;



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
     * Set hours
     *
     * @param float $hours
     *
     * @return Timesheets
     */
    public function setHours($hours)
    {
        $this->hours = $hours;

        return $this;
    }

    /**
     * Get hours
     *
     * @return float
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * Set admintypeid
     *
     * @param integer $admintypeid
     *
     * @return Timesheets
     */
    public function setAdmintypeid($admintypeid)
    {
        $this->admintypeid = $admintypeid;

        return $this;
    }

    /**
     * Get admintypeid
     *
     * @return integer
     */
    public function getAdmintypeid()
    {
        return $this->admintypeid;
    }

    /**
     * Set taskid
     *
     * @param integer $taskid
     *
     * @return Timesheets
     */
    public function setTaskid($taskid)
    {
        $this->taskid = $taskid;

        return $this;
    }

    /**
     * Get taskid
     *
     * @return integer
     */
    public function getTaskid()
    {
        return $this->taskid;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Timesheets
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set type
     *
     * @param boolean $type
     *
     * @return Timesheets
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return boolean
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set taskname
     *
     * @param string $taskname
     *
     * @return Timesheets
     */
    public function setTaskname($taskname)
    {
        $this->taskname = $taskname;

        return $this;
    }

    /**
     * Get taskname
     *
     * @return string
     */
    public function getTaskname()
    {
        return $this->taskname;
    }

    /**
     * Set status
     *
     * @param boolean $status
     *
     * @return Timesheets
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set task
     *
     * @param array $task
     *
     * @return Timesheets
     */
    public function setTask($task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return array
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set projectid
     *
     * @param \AppBundle\Entity\Projects $projectid
     *
     * @return Timesheets
     */
    public function setProjectid(\AppBundle\Entity\Projects $projectid = null)
    {
        $this->projectid = $projectid;

        return $this;
    }

    /**
     * Get projectid
     *
     * @return \AppBundle\Entity\Projects
     */
    public function getProjectid()
    {
        return $this->projectid;
    }

    /**
     * Set resourceid
     *
     * @param \AppBundle\Entity\Resources $resourceid
     *
     * @return Timesheets
     */
    public function setResourceid(\AppBundle\Entity\Resources $resourceid = null)
    {
        $this->resourceid = $resourceid;

        return $this;
    }

    /**
     * Get resourceid
     *
     * @return \AppBundle\Entity\Resources
     */
    public function getResourceid()
    {
        return $this->resourceid;
    }
}
