<?php

namespace Typedin\Breadcrumbs;

use Assert\Assertion;
use Typedin\Breadcrumbs\Contracts\Node;

/**
 * Class Breadcrumbs.
 *
 * @author typedin
 */
final class Breadcrumbs
{
    private array $nodes;

    /**
     * @param array $links
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $nodes)
    {
        Assertion::allIsInstanceOf($nodes, Node::class, 'An array of nodes should be passed.');

        $urls = array_map(function ($node) {
            return $node->url();
        }, $nodes);
        

        Assertion::uniqueValues($urls, 'You may only create breadcrumbs with unique urls.');

        $names = array_map(function ($node) {
            return $node->name();
        }, $nodes);

        Assertion::uniqueValues($names, 'You may only create breadcrumbs with unique names.');

        $this->nodes = $nodes;
    }

    /**
     * Get all nodes.
     *
     * @return array Node
     */
    public function nodes(): array
    {
        return $this->nodes;
    }

    /**
     * Check if a node is at the first position.
     *
     * @param Node $node
     *
     * @return bool
     */
    public function isFirst(BasicNode $node): bool
    {
        return $node->url() === $this->nodes[0]->url();
    }

    /**
     * Check if a node is at the last position.
     *
     * @param Node $node
     *
     * @return bool
     */
    public function isLast(BasicNode $node): bool
    {
        return $node->url() === end($this->nodes)->url();
    }

    /**
     * Create ld+json json.
     *
     * @param string $url
     *
     * @return string json encoded string
     */
    public function getLdJson(string $url): string
    {
        Assertion::url($url, "The provided url is not a valid url ({$url}).");

        return json_encode([
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $this->createItemListElement($url),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }

    /**
     * Create a single item list element.
     *
     * @param string $url
     *
     * @return arra:w
     */
    private function createItemListElement(string $url): array
    {
        $elements = [];
        $position = 1;

        foreach ($this->nodes as $node) {
            $elements[] = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $node->name(),
                'item'     => rtrim($url, '/').'/'.ltrim($node->url(), '/'),
            ];
        }

        return $elements;
    }
}
