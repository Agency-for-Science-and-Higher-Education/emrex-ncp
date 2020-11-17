<?php

namespace ISVU;

/**
 * Class ISVUElementStruktureStudija
 * @package ISVU
 */
class ISVUElementStruktureStudija
{
    private $isvu;

    /**
     * Counstructor
     */
    public function __construct($isvu)
    {
        $this->isvu = $isvu;
    }

    public function getSPElementData($url)
    {
        $data = [];

        $connector = new Connector();
        $sp_element_data = $connector->get($url);

        $data['length'] = $sp_element_data['trajanjeUSemestrima'];

        return $data;
    }
}
