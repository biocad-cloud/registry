<?php

include_once __DIR__ . "/page_view.php";

class page_mass extends page_view {

    public function __construct($page, $page_size = 20) {
        parent::__construct($page, $page_size);
    }

    public function q($term) {
        $mass = Conversion::CDbl($term);
        $da = 0.01;
        $min = $mass - $da;
        $max = $mass + $da;

        return (new Table(["cad_registry"=>"metabolites"]))
            ->where(["exact_mass"=> between($min, $max)])
            ->limit($this->offset, $this->page_size)
            ->project("id")
            ;
    }
}

return (function($page, $page_size) {
    return new page_mass($page, $page_size);
});