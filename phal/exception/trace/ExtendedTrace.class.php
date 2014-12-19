<?php

class __ExtendedTrace {

    private $_stack = array();
    private $_e = null;
    
    public function __construct(Exception $e) {
        $this->_e = $e;
    }
    
    protected function _processStack() {
        $trace = $this->_e->getTrace();
        $return_value = array();
        $previous_trace_item = null;
        foreach($trace as $trace_item_data) {
            $trace_item = new __TraceItem();
            if(key_exists('file', $trace_item_data)) {
                $trace_item->setFile($trace_item_data['file']);
            }    
            if(key_exists('line', $trace_item_data)) {
                $trace_item->setLine($trace_item_data['line']);
            }    
            $trace_item->setFunction($trace_item_data['function']);  
            if(key_exists('class', $trace_item_data)) {
                $trace_item->setClass($trace_item_data['class']);
            }
            if(key_exists('type', $trace_item_data)) {
                $trace_item->setType($trace_item_data['type']);
            }
            $trace_item->setArguments($trace_item_data['args']);    
            if($previous_trace_item != null) {
            	$previous_trace_item->setNextTraceItem($trace_item);
            }
            $return_value[] = $trace_item;
            $previous_trace_item = $trace_item;
            unset($trace_item);
        }
        return $return_value;
    }
    
    public function getStack() {
        if($this->_stack == null) {
            $this->_stack = $this->_processStack();
        }
        return $this->_stack;
    }
    
    public function __toString() {
        return $this->_e->getTraceAsString();
    }
        
    public function toHtml() {
        $return_value = '<table cellpadding="0" cellspacing="0" border="0"><tr><td nowrap="nowrap">
        <ol style="padding: 0px; margin: 0px;">';
        $stack = $this->getStack();
        $trace_item_number = 1;
        
        foreach($stack as $trace_item) {
            $file = str_replace(APP_DIR, '<i>APP_DIR</i>', $trace_item->getFile());
            $file = str_replace(PHAL_DIR, '<i>PHAL_DIR</i>', $file);
            $code = $trace_item->getCodeAroundAsHtml();

            if(!empty($file)) {
                $trace_detail = '<small style="font-size: 0.9em;">' . $file . ' line ' . $trace_item->getLine() . ' <a href="javascript:void(0);" onclick="showOrHideCode(\'trace_code_' . $trace_item_number . '\');">(code)</a></small><br />
            <div style="display: none;" id="trace_code_' . $trace_item_number . '">
            ' . $code . '
            </div>
                ';                
            }
            else {
                $trace_detail = null;
            }
            
            $return_value .= '<li style="padding-bottom: 10px;list-style-position: inside; padding-left: 0px; margin: 0px;"><b>' . $trace_item->getCall() . '()</b>
            <br />
            ' . $trace_detail . '
            </li>
            ';
            $trace_item_number++;
        }
        $return_value .= '</ol></td></tr></table>
<script language="javascript">

if(typeof(showOrHideCode) == \'undefined\') {

    showOrHideCode = function(elementId) {
        var element = document.getElementById(elementId);
        if(element.style.display == "none") {
            element.style.display = "";
        }
        else {
            element.style.display = "none";
        }
    };

}

</script>        
';
        return $return_value;
    }
    
}
