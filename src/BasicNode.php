<?php

namespace Typedin\Breadcrumbs;

use Assert\Assertion;
use Typedin\Breadcrumbs\Contracts\Node;

/**
 * Class Node.
 *
 * @author typedin
 */
final class BasicNode implements Node
{
    private string $url;
    private string $name;

    /**
     * @param string $url
     * @param string $name
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $url, string $name)
    {
        Assertion::notEmpty($url, 'Node url cannot be empty.');
        $this->url = $url;

        Assertion::NotEmpty($name, 'Node name cannot be empty.');
        $this->name = $name;
    }

    /**
     * Get the node's url.
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * Get the node's name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
}
