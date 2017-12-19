<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Services\projectmanager_com;

class DefaultController extends Controller
{
    private $aResources = [
        // C&S
        1874503 => "Carlos Vladimir Hidalgo Duran",
        1855104 => "Silvia Johanna De Paz Muñoz",
        1695062 => "Jonathan Rigoberto Mendez Reyes",
        1858246 => "Josue Francisco Orellana Vega",
        1813379 => "Jose Ruben Lopez Menjivar",
        1874502 => "Rafael Fernando Gutierrez Tejada",
        2144940 => "Carlos Jose Recinos Herrera",
        2280031 => "Jose Alejandro Mejia Reyes",
        2280075 => "Mauricio Antonio Batlle Castro",

        // Corporate
        2217562 => "Maria Guillermina Velasco Perez",
        1855105 => "Giovanni Ismael Alvarez Lopez",
        2144943 => "Rolando Alexis Cisneros Cente",
        2249926 => "Javier Ernesto Romero Cruz",
        2249927 => "Rodolfo Francisco Toche Hernandez",
        2280041 => "Jose Rodrigo Mejia Gamez",
        1701607 => "Alfonso Lopez Santamaria",

        // BAs
        2149750 => "Ernesto Enrique Mena Abdala",
        2280044 => "Claudia Carolina Figueroa Romero",
        2280016 => "Rina Elizabeth Castillo Ortiz",
        2280076 => "Diana Marcela Ortega Santos",

        // Soporte
        2279956 => "Luis Soriano Rodriguez",
        2234607 => "Fernando Vladimir Montes Arevalo",
        1813367 => "Jorge Armando Ramirez Urrutia",
        2280032 => "Gerardo Jose Zarate Martinez",
        1813383 => "Christian Omar Sum Lopez",
        1813382 => "Pablo Miguel Suazo Salazar",

        // PMs
        1809427 => "Jose Roberto Bojorquez Trujillo",
        1793765 => "Ruth Viviana Hernandez Vandenberg",

        // Qlik / DI
        2280043 => "Pablo Oswaldo Portillo Alvarado",
        1813376 => "Miguel Cubias Caceres",
        2280069 => "David Jotam Funes Urrutia",
        2280057 => "Luis Rodolfo Velasco Peña",
        2191869 => "Miguel Angel Lopez Alfaro",

        1761307 => "Karla Magaly Rivas Vasquez",
        2179426 => "Marco Alexander Orellana Sandoval",
        2212252 => "Walter Alexander Gonzalez Martinez",
        1813376 => "Miguel Cubias Caceres",

        // Managers
        1695060 => "Juan Othmaro Menjivar Rosales",
        1695054 => "Daniel Ernesto Vigil Sandoval",
        1695055 => "Arturo Marenco Rodriguez",

    ];

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $this->getDoctrine()->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

        $cache = new FilesystemCache();

        if ($cache->has('cache.front')) {

            $response = $cache->get('cache.front');
            $response->headers->set('x-cache', 'SimpleCache');
            return $response;
        }

        /* @var $spm \AppBundle\Services\projectmanager_com */
        $spm = $this->container->get(projectmanager_com::class);
        $ret = [];

        //$ret = $spm->timesheetByResourceAndDateRange(1874503, strtotime("2017-07-01"), strtotime("2017-08-31"));
        //$ret = $spm->timesheetByResource(1874503);

        $iDateStart = strtotime("first day of january this year");
        //$iDateStart = strtotime("first day of this month");
        $iDateEnd = strtotime(date('Y-m-t'));

        foreach ($this->aResources as $resourceID => $resourceName) {
            $tmpRet = $spm->getMissingCapexOpexByResourceAndDateRange($resourceID, $iDateStart , $iDateEnd);

            if ($tmpRet <> 'error') {
                $ret[$resourceName] = $tmpRet;
            } else {
                var_dump($tmpRet . ' ' . $resourceName);
            }
        }

        $twig = $this->render('default/index.html.twig', [
            'ret' => $ret,
            'iDateStart' => $iDateStart,
            'iDateEnd' => $iDateEnd
        ]);

        $cache->set('cache.front', $twig, 1800);

        return $twig;
    }

    /**
     * @Route("/update", name="updateResources")
     */
    public function updateAction(Request $request)
    {
        $spm = $this->container->get(projectmanager_com::class);
        $spm->updateResources();
    }

}
