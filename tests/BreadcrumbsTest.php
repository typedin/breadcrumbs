<?php

namespace Tests\Unit;

use Helmich\JsonAssert\JsonAssertions;
use PHPUnit\Framework\TestCase;
use Typedin\Breadcrumbs\BasicNode;
use Typedin\Breadcrumbs\Breadcrumbs;

/**
 * @author yourname
 */
class BreadcrumbsTest extends TestCase
{
    use JsonAssertions;

    /**
     * @test
     */
    public function it_must_be_instanciated_with_an_array_of_nodes()
    {
        $this->expectExceptionMessage('An array of nodes should be passed.');

        $sut = new Breadcrumbs(['not', 'an', 'array', 'of', 'nodes']);
    }

    /**
     * @test
     */
    public function it_cannot_be_instanciate_with_twice_the_same_url()
    {
        $this->expectExceptionMessage('You may only create breadcrumbs with unique urls.');

        new Breadcrumbs([
            new BasicNode('/', 'home'),
            new BasicNode('/', 'not home'),
        ]);
    }

    /**
     * @test
     */
    public function it_cannot_be_instanciate_with_twice_the_same_name()
    {
        $this->expectExceptionMessage('You may only create breadcrumbs with unique names.');

        new Breadcrumbs([
            new BasicNode('/', 'same name'),
            new BasicNode('/not/home', 'same name'),
        ]);
    }

    /**
     * @test
     */
    public function it_constructs_an_array_of_nodes()
    {
        $sut = new Breadcrumbs([
            new BasicNode('/', 'home'),
            new BasicNode('/not/home', 'not home'),
        ]);

        $this->assertEquals(2, count($sut->nodes()));
    }

    /**
     * @test
     */
    public function it_can_tell_if_a_node_is_first()
    {
        $sut = new Breadcrumbs([
            new BasicNode('/', 'home'),
            new BasicNode('/not/home', 'not home'),
        ]);

        $this->assertTrue($sut->isFirst($sut->nodes()[0]));
    }

    /**
     * @test
     */
    public function it_can_tell_if_the_node_is_last()
    {
        $sut = new Breadcrumbs([
            new BasicNode('/', 'home'),
            new BasicNode('/not/home', 'not home'),
            new BasicNode('/is/last', 'is last'),
        ]);

        $this->assertTrue($sut->isLast($sut->nodes()[2]));
    }

    /**
     * @test
     */
    public function a_node_inside_a_list_is_neither_first_nor_last()
    {
        $sut = new Breadcrumbs([
            new BasicNode('/', 'home'),
            new BasicNode('/not/home', 'not home'),
            new BasicNode('/is/last', 'is last'),
        ]);

        $this->assertFalse($sut->isFirst($sut->nodes()[1]));
        $this->assertFalse($sut->isLast($sut->nodes()[1]));
    }

    /**
     * @test
     */
    public function it_builds_valid_json_schema()
    {
        $sut = new Breadcrumbs([
            new BasicNode('books', 'Books'),
            new BasicNode('books/sciencefiction', 'Science Fiction'),
            new BasicNode('books/sciencefiction/award-winners', 'Award Winners'),
        ]);

        $result = $sut->getLdJson('https://example.com');

        $this->assertJsonDocumentMatchesSchema($result, [
            'type'       => 'object',
            'properties' => [
                '@context' => [
                    'type' => 'string',
                ],
                '@type' => [
                    'type' => 'string',
                ],
                'itemListElement' => [
                    'type'  => 'array',
                    'items' => [
                        'type'       => 'object',
                        'properties' => [
                            '@type' => [
                                'type' => 'string',
                            ],
                            'position' => [
                                'type' => 'integer',
                            ],
                            'name' => [
                                'type' => 'string',
                            ],
                            'item' => [
                                'type' => 'string',
                            ],
                        ],
                        'required' => [
                            '@type',
                            'position',
                            'name',
                        ],
                    ],
                ],
            ],
            'required' => [
                '@context',
                '@type',
                'itemListElement',
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_builds_valid_json_item_list_element_name_position()
    {
        $sut = new Breadcrumbs([
            new BasicNode('books', 'Books'),
            new BasicNode('books/sciencefiction', 'Science Fiction'),
            new BasicNode('books/sciencefiction/award-winners', 'Award Winners'),
        ]);

        $itemListElement = json_decode($sut->getLdJson('https://example.com'), true)['itemListElement'];

        $this->assertEquals(3, count($itemListElement));

        $this->assertEquals(1, $itemListElement[0]['position']);
        $this->assertEquals(2, $itemListElement[1]['position']);
        $this->assertEquals(3, $itemListElement[2]['position']);

        $this->assertEquals('Books', $itemListElement[0]['name']);
        $this->assertEquals('Science Fiction', $itemListElement[1]['name']);
        $this->assertEquals('Award Winners', $itemListElement[2]['name']);
    }

    /**
     * @test
     */
    public function it_fails_json_ld_creation_when_a_non_valid_url_is_passed()
    {
        $this->expectExceptionMessage('The provided url is not a valid url (not a url).');

        $sut = new Breadcrumbs([
            new BasicNode('books', 'Books'),
        ]);

        $sut->getLdJson('not a url');
    }

    /**
     * @test
     */
    public function it_builds_valid_json_item_list_url()
    {
        $sut = new Breadcrumbs([
            new BasicNode('books', 'Books'),
            new BasicNode('books/sciencefiction', 'Science Fiction'),
            new BasicNode('books/sciencefiction/award-winners', 'Award Winners'),
        ]);

        $itemListElement = json_decode($sut->getLdJson('https://example.com'), true)['itemListElement'];

        $this->assertEquals(
            'https://example.com/books',
            $itemListElement[0]['item']
        );
    }

    /**
     * @test
     */
    public function it_builds_valid_json_item_list_url_with_a_trailing_slash()
    {
        $sut = new Breadcrumbs([
            new BasicNode('books', 'Books'),
            new BasicNode('books/sciencefiction', 'Science Fiction'),
            new BasicNode('books/sciencefiction/award-winners', 'Award Winners'),
        ]);

        $itemListElement = json_decode($sut->getLdJson('https://example.com/'), true)['itemListElement'];

        $this->assertEquals(
            'https://example.com/books',
            $itemListElement[0]['item']
        );
    }

    /**
     * @test
     */
    public function it_builds_valid_json_item_list_url_with_a_leading_slash()
    {
        $sut = new Breadcrumbs([
            new BasicNode('/books', 'Books'),
            new BasicNode('books/sciencefiction', 'Science Fiction'),
            new BasicNode('books/sciencefiction/award-winners', 'Award Winners'),
        ]);

        $itemListElement = json_decode($sut->getLdJson('https://example.com/'), true)['itemListElement'];

        $this->assertEquals(
            'https://example.com/books',
            $itemListElement[0]['item']
        );
    }

    /**
     * @test
     */
    public function it_creates_a_valid_item_with_url_ending_with_a_slash()
    {
        $sut = new Breadcrumbs([
            new BasicNode('books', 'Books'),
            new BasicNode('books/sciencefiction', 'Science Fiction'),
            new BasicNode('books/sciencefiction/award-winners', 'Award Winners'),
        ]);

        $itemListElement = json_decode($sut->getLdJson('https://example.com/'), true)['itemListElement'];

        $this->assertEquals(
            'https://example.com/books',
            $itemListElement[0]['item']
        );
    }
}
