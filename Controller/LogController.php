<?php

namespace DavidTeruel\LogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;

/**
 * Class LogController
 * @package DavidTeruel\LogBundle\Controller
 */
class LogController extends Controller
{
    public function listAction()
    {
        $logsDir = $this->container->get('kernel')->getLogDir();
        $finder = new Finder();
        $finder->files()->in($logsDir);

        $files = array();
        foreach($finder->files() as $file){
            array_push($files, $file);
        }

        return $this->render('LogBundle:Log:list.html.twig', array(
            'files' => $files
        ));
    }

    public function showFileAction($name, $numberLines = 1)
    {
        $logsDir = $this->container->get('kernel')->getLogDir();
        $filePath = $logsDir.'/'.$name;

        $output = '';

        $lines = file($filePath);
        if(count($lines) <= $numberLines){
            $numberLines = count($lines);
        }
        for ($i = count($lines) - $numberLines; $i < count($lines); $i++) {
           $output .= $lines[$i] . PHP_EOL;
        }


        return $this->render('LogBundle:Log:showLog.html.twig', array(
            'file' => $output
        ));
    }

    public function showParametersFileAction()
    {
        $parametersFile = '';
        $rootDir = $this->container->get('kernel')->getRootDir();

        //1st, locate the import with parameters location into the config file
        $configFile = $rootDir.'/config/config.yml';
        $routeParametersFile = '';

        $handle = fopen($configFile, 'r');
        if($handle){
            while(($line = fgets($handle)) !== false){
                if(strpos($line, 'parameters.yml')){
                    $routeParametersFile = $line;

                    break;
                }
            }

            fclose($handle);
        }

        if($routeParametersFile != ''){
            //2nd split the line to clean route of resource
            $importLine = explode('resource:', $routeParametersFile);

            if(count($importLine) > 1){
                $resourceFileRoute = $importLine[1];
                $cleanedRoute = str_replace('}', '', $resourceFileRoute);
                $cleanedRoute = str_replace(PHP_EOL, '', $cleanedRoute);
                $cleanedRoute = str_replace(' ', '', $cleanedRoute);

                if(!empty($cleanedRoute)){
                    $parametersFile = file_get_contents($rootDir.'/config/'.$cleanedRoute);
                }
            }
        }

        return $this->render('LogBundle:Log:showParameters.html.twig', array(
            'file' => $parametersFile
        ));
    }
}
