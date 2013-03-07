<?php


abstract class __TemplateEngineView extends __View {

    const PRE_FILTER     = 1;
    const POST_FILTER    = 2;
    const OUTPUT_FILTER  = 3;

    /**
     * This is the array that will contain all filters for current __View instance.
     *
     * @var array
     */
    protected $filterStack = array( self::PRE_FILTER    => array(),
                                    self::POST_FILTER   => array(),
                                    self::OUTPUT_FILTER => array() );

    protected $_template_locator_class = '__TemplateLocator';

    /**
     * This variable will contain the path and template file to use in the render operation
     *
     * @var string
     */
    protected $_template_file = null;

    /**
     * Specify the template file to use without the path
     *
     * @param string $template_file The template filename
     * 
     */
    public function setTemplate($template_file) {
        $this->_template_file = $template_file;
    }
    
    public function getTemplate() {
        return $this->_template_file;
    }
    
    public function setTemplateLocatorClass($template_locator_class) {
        $this->_template_locator_class = $template_locator_class;
    }
    
    public function getTemplateLocatorClass() {
        return $this->_template_locator_class;
    }

    final public function execute() {
        if($this->_template_file == null) {
            throw __ExceptionFactory::getInstance()->createException('ERR_TEMPLATE_NOT_DEFINED');
        }
        //Locate the template File
        $template_file = $this->locateTemplateFile($this->_template_file);
        $this->setCompileDir(__PathResolver::resolvePath(__ApplicationContext::getInstance()->getPropertyContent('TEMPLATES_COMPILE_DIR'), __ApplicationContext::getInstance()->getBaseDir()));
        //setup filters:
        //- The component filter is the filter in charge of parse the template for components.
        //  Is just available on __TemplateEngineView classes:
        $component_filter = new __ComponentFilter();
        $this->addFilter( $component_filter );
        //render the ui:
        return $this->templatize($template_file);
    }

    /**
     * Given a template file, we will attempt to resolve the path of the template filename
     * @param $templateFile the simple template file name that we want to locate using our Template Locator
     * @return string of the full template file name
     */
    final protected function locateTemplateFile($template_filename) {
    	$template_file = null;
    	$template_locator_class = $this->getTemplateLocatorClass();
    	$template_locator = new $template_locator_class();
    	if( $template_locator instanceof __FileLocator ) {
    		$template_file = $template_locator->locateFile($template_filename);
    		if($template_file == null) {
    			throw __ExceptionFactory::getInstance()->createException('ERR_TEMPLATE_NOT_FOUND', array($template_filename));
    		}
    	}
    	else {
    		throw __ExceptionFactory::getInstance()->createException('ERR_WRONG_TEMPLATE_LOCATOR_CLASS', array($template_locator_class));
    	}
    	return $template_file;
    }

    /**
     * Add an filter to the filter stack
     *
     * @param mixed The filter to add to the filter stack.
     * @param integer The type of filter. It can be self::POST_FILTER, self::PRE_FILTER, self::OUTPUT_FILTER
     *               
     */
    final public function addFilter(__TemplateFilter &$filter, $type = null)
    {
        if($type == null) {
            $type = $filter->getType();
        }
        $this->filterStack[$type][] = $filter;
    }

    final public function executePreFilters($compiled) {
        foreach ($this->filterStack[__TemplateFilter::PRE_FILTER] as $filter) {
            $compiled = $filter->executeFilter($compiled, $this);
        }
        return $compiled;
    }

    final public function executePostFilters($compiled) {
        foreach ($this->filterStack[__TemplateFilter::POST_FILTER] as $filter) {
            $compiled = $filter->executeFilter($compiled, $this);
        }
        return $compiled;
    }

    final public function executeOutputFilters($compiled) {
        foreach ($this->filterStack[__TemplateFilter::OUTPUT_FILTER] as $filter) {
            $compiled = $filter->executeFilter($compiled, $this);
        }
        return $compiled;
    }

    abstract protected function templatize($template_file);

    abstract public function getComponentParserClass();
    
    abstract public function setCompileDir($compile_dir);
    
}
