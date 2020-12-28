<?php

class github {

    /**
     * @var string
    */
    private $user;
    /**
     * @var string
    */
    private $repo;
    /**
     * @var string
    */
    private $ref;

    function __construct($user, $repo, $ref = "master") {
        $this->user = $user;
        $this->repo = $repo;
        $this->ref  = $ref;
    }

    public function getContent($path) {
        $url = "https://api.github.com/repos/{$this->user}/{$this->repo}/contents/{$path}?ref={$this->ref}";
        $json = json_decode(file_get_contents($url));

        controller::success($json);
    }
}