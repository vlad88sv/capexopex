<?php
/**
 * Created by PhpStorm.
 * User: carlos.hidalgo
 * Date: 23/08/2017
 * Time: 02:31 PM
 */

namespace AppBundle\Services;

use AppBundle\Entity\Projects;
use AppBundle\Entity\Resources;
use AppBundle\Entity\Timesheets;
use Circle\RestClientBundle\Services\RestClient;
use Doctrine\ORM\EntityManager;

class projectmanager_com
{

    /**
     * projectmanager_com constructor.
     */

    private $circleRestClientBundle;
    private $em;
    private $doctrine;

    public function __construct(RestClient $circleRestClientBundle, EntityManager $entityManager)
    {
        $this->circleRestClientBundle = $circleRestClientBundle;
        $this->em = $entityManager;
    }

    public function timesheetByResource($iResourceID) {


        for($i = 0; $i < 5; $i++) {
            $oRet = $this->circleRestClientBundle->get("https://api.projectmanager.com/api/v1/resources/{$iResourceID}/timesheets.json");
            $aContent = json_decode($oRet->getContent());

            if (json_last_error() == 0 && isset($aContent->timesheets)) {

                return $aContent->timesheets;
            } else {
                //var_dump($aContent);
            }
        }
        return 'error';

    }


    /**
     * @param $iResourceID integer
     * @param $dateFrom integer
     * @param $dateTo integer
     */
    public function timesheetByResourceAndDateRange($iResourceID, $dateFrom, $dateTo) {
        $iPrevTimeLimit = ini_get('max_execution_time');
        set_time_limit(900);


        $aTimesheets = $this->timesheetByResource($iResourceID);

        if ($aTimesheets == 'error'){
            return 'error';
        }

        $aRet = array();

        foreach($aTimesheets as $aTimeSheet) {
            $dateTask = strtotime($aTimeSheet->date);

            if ($dateTask >= $dateFrom && $dateTask <= $dateTo) {
                $aRet[date('Ymd', $dateTask)][] =  (object) array_merge_recursive((array) $aTimeSheet, ['task' => $this->getSingleTask($aTimeSheet)] );
            }
        }

        ksort($aRet);

        set_time_limit($iPrevTimeLimit);

        return $aRet;
    }

    public function getMissingCapexOpexByResourceAndDateRange($iResourceID, $dateFrom, $dateTo) {
        $iPrevTimeLimit = ini_get('max_execution_time');
        set_time_limit(3600);

        $iAPICalls = 0;
        $iAPICallsFailed = 0;

        $aTimesheets = $this->timesheetByResource($iResourceID);
        $iAPICalls++;

        if ($aTimesheets == 'error'){
            return 'error';
        }

        $aRet = array();

        foreach($aTimesheets as $aTimeSheet) {
            $dateTask = strtotime($aTimeSheet->date);

            if ($dateTask >= $dateFrom && $dateTask <= $dateTo) {
                $oPm = $this->getSingleProject($aTimeSheet->projectId);
                $iAPICalls++;


                $failed = false;
                for($i = 0; $i < 5; $i++) {

                    $oTask = $this->getSingleTask($aTimeSheet);
                    $iAPICalls++;
                    if ($oTask == "error") { //API call failed, retry
                        $iAPICallsFailed++;
                        $failed = true;
                    } elseif ($oTask == "na") { // Non applicable (admin task)
                        //if ($failed) var_dump("Recovered");
                        break;
                    } elseif ($oTask == "deleted") { // Non applicable (project task has been deleted)
                        //if ($failed) var_dump("Recovered");
                        $aRet[] = (object)array_merge_recursive((array)$aTimeSheet, ['task' => ['name' => '*deleted*']], ['pm' => $oPm]);
                        break;
                    } else {
                        //if ($failed) var_dump("Recovered");
                        break;
                    }
                    //var_dump('fail: ' . $aTimeSheet->taskId);
                }

                if (isset($oTask->customColumns) && count($oTask->customColumns) > 0) {

                    $missing = true;
                    foreach ($oTask->customColumns as $column) {

                        $value = strtoupper($column->data);
                        if ($column->name = "CAPEX/OPEX" && in_array($value, ["OPEX", "CAPEX"])) {
                            $missing = false;
                        }
                    }

                    if ($missing) {
                        $this->flagTaskForNoCache($aTimeSheet);
                        $aRet[] = (object)array_merge_recursive((array)$aTimeSheet, ['task' => $oTask], ['pm' => $oPm]);
                    }
                }
            }
        }

        set_time_limit($iPrevTimeLimit);

        return ['info' => ['calls' => $iAPICalls, 'callsFailed' => $iAPICallsFailed], 'data' => $aRet];
    }

    public function flagTaskForNoCache($iTask)
    {
        $dTask = $this->em->getRepository("AppBundle:Timesheets")->findOneBy(['taskid' => $iTask->taskId]);

        if ($dTask) {
            $dTask->setStatus(TRUE);
            $this->em->persist($dTask);
            $this->em-> flush();
        }
    }

    public function getSingleTask($iTask, $force = false)
    {
        if (empty($iTask->taskId)) {
            return 'na';
        }

        // Check cache, only valid if cache status equals 1 (closed / no pendings)
        //$aTimeSheet->projectId

        $dTask = $this->em->getRepository("AppBundle:Timesheets")->findOneBy(['taskid' => $iTask->taskId]);
        if ($force) {
            $this->em->remove($dTask);
            $this->em-flush();
        } else {

            if (!empty($dTask) && $dTask->getStatus() == FALSE) {
                return json_decode(json_encode($dTask->getTask()), FALSE);
            }
        }

        $oRet = $this->circleRestClientBundle->get("https://api.projectmanager.com/api/v1/tasks/{$iTask->taskId}.json");

        if ($oRet->getStatusCode() == 400) {
            return 'deleted';
        };

        $aContent = json_decode($oRet->getContent());
        if (json_last_error() == 0 && isset($aContent->task)) {
            $dTask = new Timesheets();
            $dTask->setTask($aContent->task);
            $dTask->setResourceid($this->em->getRepository('AppBundle:Resources')->findOneBy(['resourceid' => $iTask->resourceId]));
            $dTask->setAdmintypeid($iTask->adminTypeId);
            $dTask->setTaskid($iTask->taskId);
            $dTask->setHours($iTask->hours);
            $dTask->setProjectid( $this->em->getRepository('AppBundle:Projects')->findOneBy(['pmid' => $iTask->projectId]) );
            $dTask->setTaskname($aContent->task->name);
            $dTask->setDate(new \DateTime($iTask->date));

            $this->em->persist($dTask);
            $this->em->flush();

            return $aContent->task;
        } else {
            //var_dump($oRet->getStatusCode());
        }

        return 'error';
    }

    public function getSingleProject($iProject) {
        if (empty($iProject)) {
            return null;
        }

        $dProject = $this->em->getRepository("AppBundle:Projects")->findOneBy([ 'pmid' => $iProject ]);

        if (!empty($dProject)) {
            return (object) $dProject->getProject();
        }

        $oRet = $this->circleRestClientBundle->get("https://api.projectmanager.com/api/v1/projects/{$iProject}.json");

        $aContent = json_decode($oRet->getContent());
        if (json_last_error() == 0 && isset($aContent->project)) {
            $dProject = new Projects();

            $dProject->setShortname($aContent->project->shortName);
            $dProject->setName($aContent->project->name);
            $dProject->setPmid($aContent->project->id);
            $dProject->setProject($aContent->project);
            $this->em->persist($dProject);
            $this->em->flush();

            return $aContent->project;
        }

        return 'error';
    }

    public function updateResources() {
        $oRet = $this->circleRestClientBundle->get("https://api.projectmanager.com/api/v1/resources.json");
        $aContent = json_decode($oRet->getContent());

        if (json_last_error() <> 0 || !isset($aContent->resources)) {

            return 'error';
        }

        foreach($aContent->resources as  $resource) {

            $dResource = $this->em->getRepository("AppBundle:Resources")->findOneBy(['resourceid' => $resource->id]);

            if (empty($dResource)) {
                $dResource = new Resources();
            }

            try {

                $dResource->setName($resource->name);
                $dResource->setShortname($resource->shortName);
                $dResource->setResourceid($resource->id);

                $this->em->persist($dResource);
                $this->em->flush();

                echo "Inserted: {$resource->name}".PHP_EOL;
            } catch (\Exception $e) {
                echo "Failed: {$resource->name}".PHP_EOL;
                echo " -- " . $e->getMessage();
            }

        }
    }
}