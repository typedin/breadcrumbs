<?php

namespace Typedin\Breadcrumbs;

use Typedin\Breadcrumbs\Contracts\Node;
use Webmozart\Assert\Assert;

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
        Assert::stringNotEmpty($url, 'Node url cannot be empty.');
        $this->url = $url;

        Assert::stringNotEmpty($name, 'Node name cannot be empty.');
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
