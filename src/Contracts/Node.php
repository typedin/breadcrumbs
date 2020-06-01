<?php

namespace Typedin\Breadcrumbs\Contracts;

/**
 * Interface Node
 * @author typedin
 */
interface Node
{
    public function url() : string;
    public function name() : string;
}
