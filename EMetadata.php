<?
/**
* EMetadata class file
* @author Vitaliy Stepanenko <mail@vitaliy.in>
* @version 0.2
* @package metadata
* @license BSD   
*/

/**
*  Metadata Helps to get metainformation about models,controllers and actions in your application* 
* 
* For using you need:
* 1. Place this file to components directory of your application (<your_app_dir>/protected/components) 
* 2. Add to 'components' section in your application config (<your_app_dir>/protected/config/main.php) next lines:
* 'components'=>array(
*   'metadata'=>array('class'=>'EMetadata'),
*    ...
*  ),        
* 3. Use:
*   $user_actions = Yii::app()->metadata->getActions('UserController');
*   var_dump($user_actions);
*/
class EMetadata extends CApplicationComponent
{

    /**
    * Get all information about application
    * if modules of your application have controllers with same name, it will raise fatall error
    * 
    */
    public function getAll()
    {
        
        $meta = array(
            'models'        => $this->getModels(),
            'controllers'   => $this->getControllers(),
            'modules'       => $this->getModules(),
        );
        foreach ($meta['controllers'] as &$controller) {
            $controller = array(
                'name'      => $controller,
                'actions'   => $this->getActions($controller)
            );   
        }
         
        foreach ($meta['modules'] as &$module) {
            
            $controllers = $this->getControllers($module);
                        
            foreach ($controllers as &$controller) {
                $controller = array(
                    'name'      => $controller,
                    'actions'   => $this->getActions($controller,$module)
                );   
            }
                        
            $module = array(
                'name'          => $module,
                'controllers'   => $controllers,
                'models'        => $this->getModels($module),
            );
            
        }
         
        return $meta;

    }

    /**
    * Get actions of controller
    * 
    * @param string|CController $controller
    * @param string|null $module
    * @return array
    */
    public function getActions($controller, $module=null)
    {
        if ($controller instanceof CController) {
            $controller = get_class($controller);
        }
        if ($module != null){
            $path = join(DIRECTORY_SEPARATOR,array(Yii::app()->modulePath, $module, 'controllers'));
            $this->setModuleIncludePaths($module);
        }else{
            $path = 'protected' . DIRECTORY_SEPARATOR . 'controllers';
        }        
        
        
            include_once($path . DIRECTORY_SEPARATOR . $controller . '.php');        
            $reflection = new ReflectionClass($controller); 
            $methods    = $reflection->getMethods(); 
        
        //$cInstance=new $controller(null);
        // var_dump($cInstance->actions());
        $actions = array();
        foreach($methods as $method)
        {           
            if (strpos($method->name,'action')===0 and ctype_upper($method->name[6])) {
                $actions[] = str_replace('action','',$method->name);
            }
        }
        return $actions;

    }

    /**
    * Set php include paths for module
    * 
    * @param string $module
    * @todo refactor it
    */
    protected function setModuleIncludePaths($module)
    {       
        set_include_path(join(PATH_SEPARATOR,array(
            get_include_path(),            
            join(DIRECTORY_SEPARATOR,array(Yii::app()->modulePath,$module,'components')),
            join(DIRECTORY_SEPARATOR,array(Yii::app()->modulePath,$module,'models')),
            join(DIRECTORY_SEPARATOR,array(Yii::app()->modulePath,$module,'vendors')),
        )));
    }
    
    /**
    * Get list of controllers with actions
    * 
    * @param string|null $module
    * @return array 
    */
    function getControllersActions($module=null)
    {
        $c=$this->getControllers($module);
        foreach ($c as &$controller) {
            $controller = array(
                'name'      => $controller,
                'actions'   => $this->getActions($controller, $module)
            );   
        }
        return $c;
    }
    
    /**
    * Scan controller directory & return array of MVC controllers
    * 
    * @param string|null $module    
    * @return array
    */
    public function getControllers($module=null)
    {

        if ($module!=null){
            $path = join(DIRECTORY_SEPARATOR, array(Yii::app()->modulePath, $module, 'controllers'));            
        }else{
            $path = 'protected' . DIRECTORY_SEPARATOR . 'controllers';
        }                                
        $controllers = array_filter(scandir($path),array($this,'isController'));
        foreach ($controllers as &$c) {            
            $c=str_ireplace('.php', '', $c);
        }       
        return $controllers;         
    }

    /**
    * Scans models directory & return array of MVC models
    * 
    * @param string|null $module
    * @param bool $include_classes
    * @return array
    */
    public function getModels($module=null, $include_classes=false)
    {

        if ($module != null){
            $path = join(DIRECTORY_SEPARATOR, array(Yii::app()->modulePath, $module, 'models'));
        } else {
            $path = 'protected'.DIRECTORY_SEPARATOR.'models';
        }                

        $files  = scandir($path);
        $models = array();
        foreach ($files as $f) {
            if (stripos($f,'.php') !== false) {
                $models[] = str_ireplace('.php', '', $f);
                if ($include_classes) {
                    include_once($path . DIRECTORY_SEPARATOR . $f);
                } 

            }
        }       
        return $models; 

    }

    /**
    * Not used
    * 
    * @param string $fileName
    */
    private function isControllerFile($fileName)
    {
        return stripos($fileName,'Controller.php') !== false;
    }



    /**
    * Returns array of module names
    * 
    */
    public function getModules()
    {        
        $modules = scandir(Yii::app()->modulePath);        
        $modules=array_filter($modules, array($this, 'isModuleFile'));
        return $modules;
    }    

    /**
    * Used in getModules() to filter array of files & directories
    * 
    * @param string $name
    */
    protected function isModuleFile($name)
    {
        return $name!='.' and $name!='..' and is_dir(Yii::app()->modulePath . DIRECTORY_SEPARATOR . $name);
    }

}
