<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Model;

abstract class BaseTranslatable {
    /**
     * Get all PUBLIC and PROTECTED params with theirs associates values in this object
     * @return array
     */
    public function getTranslatableEntityObjectValues() {
        return get_object_vars($this);
    }
}