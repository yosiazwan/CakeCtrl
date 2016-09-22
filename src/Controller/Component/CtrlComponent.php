<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Filesystem\Folder;
use ReflectionClass;
use ReflectionMethod;

/**
 * CakePHP CtrlComponent
 * @author Yosi Azwan
 * @email yosi.azwan@gmail.com
 */
class CtrlComponent extends Component {
    
    public $baseDir = '../src/Controller/';
    public $ignoreList = [
        '.',
        '..',
        'Component',
        'TestController.php',
        'AppController.php',
        'beforeFilter',
        'afterFilter',
        'initialize',        
    ];
    
    public function getControllers($path=''){
        $newPath = $this->baseDir.$path.'/';
        $dir = new Folder($newPath);
        $content = $dir->read();
        $folder = $content[0];
        $files = $content[1];
        $ctrls = [];
        foreach($files as $fl){
            if(!in_array($fl, $this->ignoreList)){
                $ctrl = explode('.', $fl)[0];
                $ctrls[] = substr($ctrl,0,strlen($ctrl)-10);
            }
        }
        
        foreach($folder as $fd){
            if(!in_array($fd, $this->ignoreList)){
                $ctrls[$fd] = $this->getControllers($fd);
            }
        }
        return $ctrls;     
    }
    
    public function getActions($controllerName,$prefik='')
    {   
        if($prefik!==''){  $prefik = $prefik.'\\'; } 
        $className = 'App\\Controller\\'.$prefik.$controllerName.'Controller';
        $class = new ReflectionClass($className);
        $actions = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        $results = [];
        foreach($actions as $action){
            if($action->class == $className && !in_array($action->name, $this->ignoreList)){
                array_push($results, $action->name);
            }  
        }
        return $results;
    }
    
    
    public function getResources(){        
        $resources = [];
        $ctrls = $this->getControllers();
        foreach($ctrls as $key=>$val){
            if(!is_array($val)){
                $resources[$val] = [
                    'prefix' => '',
                    'actions' => $this->getActions($val)                    
                ];
            }else{
                foreach($val as $v){
                    $resources[$v] = [
                        'prefix' => $key,
                        'actions' => $this->getActions($v, $key)
                    ];
                }
            }
        }
        return $resources;        
    }
    
}
