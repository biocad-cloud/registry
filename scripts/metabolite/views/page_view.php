<?php

abstract class page_view {

    /**
     * @var int
    */
    protected $page;
    /**
     * @var int
    */
    protected $page_size;
    /**
     * @var int
    */
    protected $offset;

    public function __construct($page, $page_size = 20) {
        $this->page = $page;
        $this->page_size = $page_size;
        $this->offset = ($page -1) * $page_size;
    }

    /**
     * @param string $term the query term
     * 
     * @return integer[]
    */
    public abstract function q($term);
}