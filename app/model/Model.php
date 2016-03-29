<?php

class Model {

    final public function __construct() {
        if (func_num_args() == 1 && is_array(func_get_arg(0))) {
            // Initialize the model with the array's contents.
            $array = func_get_arg(0);
            $this->mapTypes($array);
        }
    }

    protected function mapTypes($array) {
        // Hard initialise simple types, lazy load more complex ones.
        foreach ($array as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
                unset($array[$key]);
            }
        }
    }
    
    private function createObjectFromName($name, $item)
    {
      $type = $this->$name;
      return new $type($item);
    }
}
