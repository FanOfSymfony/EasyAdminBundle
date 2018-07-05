<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Util;

interface CanonicalizerInterface
{
    /**
     * @param string $string
     *
     * @return string
     */
    public function canonicalize($string);
}