<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Util;

interface TokenGeneratorInterface
{
    /**
     * @return string
     */
    public function generateToken();
}