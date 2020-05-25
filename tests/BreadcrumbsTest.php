<?php

namespace Tests\Unit;

use Helmich\JsonAssert\JsonAssertions;
use PHPUnit\Framework\TestCase;
use Typedin\Breadcrumbs\Breadcrumbs;
use TypeError;

/**
 * @author yourname
 */
class BreadcrumbsTest extends TestCase
{
    use JsonAssertions;

    /**
     * @test
     */
    public function it_must_be_instanciated_with_an_array()
    {
        $this->expectException(TypeError::class);
        $sut = new Breadcrumbs('not an array');
    }

    /**
     * @test
     */
    public function it_cannot_be_instanciate_with_twice_the_same_url()
    {
        $this->expectExceptionMessage('You may only create breadcrumbs with unique urls.');
        $sut = new Breadcrumbs([['/', 'home'], ['/', 'not home']]);
    }

    /**
     * @test
     */
    public function it_cannot_be_instanciate_with_twice_the_same_name()
    {
        $this->expectExceptionMessage('You may only create breadcrumbs with unique names.');
        $sut = new Breadcrumbs([['/', 'home'], ['/not/home', 'home']]);
    }

    /**
     * @test
     */
    public function it_gives_back_the_correct_url_and_name_of_a_node()
    {
        $sut = new Breadcrumbs([['/', 'home']]);

        $this->assertEquals('/', $sut->nodes()[0]->url());
        $this->assertEquals('home', $sut->nodes()[0]->name());
    }

    /**
     * @test
     */
    public function it_can_tell_if_the_node_is_first()
    {
        $sut = new Breadcrumbs([['/', 'home'], ['/not/home', 'not home']]);

        $this->assertTrue($sut->isFirst($sut->nodes()[0]));
    }

    /**
     * @test
     */
    public function it_can_tell_if_the_node_is_last()
    {
        $sut = new Breadcrumbs([['/', 'home'], ['/not/home', 'not home'], ['/last/link', 'is last']]);

        $this->assertTrue($sut->isLast($sut->nodes()[2]));
    }

    /**
     * @test
     */
    public function it_builds_valid_json_schema()
    {
        $sut = new Breadcrumbs([['books', 'Books'], ['books/sciencefiction', 'Science Fiction'], ['books/sciencefiction/award-winners', 'Award Winners']]);

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
        $sut = new Breadcrumbs([['books', 'Books'], ['books/sciencefiction', 'Science Fiction'], ['books/sciencefiction/award-winners', 'Award Winners']]);

        $itemListElement = json_decode($sut->getLdJson('https://example.com'), true)['itemListElement'];

        $this->assertEquals(3, count($itemListElement));

        $this->assertEquals(1, $itemListElement[0]['position']);
        $this->assertEquals(3, $itemListElement[2]['position']);

        $this->assertEquals('Books', $itemListElement[0]['name']);
        $this->assertEquals('Science Fiction', $itemListElement[1]['name']);
    }

    /**
     * @test
     */
    public function it_fails_json_ld_creation_when_a_non_valid_url_is_passed()
    {
        $this->expectExceptionMessage('The provided url is not a valid url (not a url).');

        $sut = new Breadcrumbs([['books', 'Books']]);

        $sut->getLdJson('not a url');
    }

    /**
     * @test
     */
    public function it_builds_valid_json_item_list_url()
    {
        $sut = new Breadcrumbs([['books', 'Books'], ['books/sciencefiction', 'Science Fiction'], ['books/sciencefiction/award-winners', 'Award Winners']]);

        $itemListElement = json_decode($sut->getLdJson('https://example.com'), true)['itemListElement'];

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
        $sut = new Breadcrumbs([['books', 'Books'], ['books/sciencefiction', 'Science Fiction'], ['books/sciencefiction/award-winners', 'Award Winners']]);

        $itemListElement = json_decode($sut->getLdJson('https://example.com/'), true)['itemListElement'];

        $this->assertEquals(
            'https://example.com/books',
            $itemListElement[0]['item']
        );
    }
}
