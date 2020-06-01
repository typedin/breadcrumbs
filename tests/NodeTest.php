<?php

namespace Tests\Unit;

use ArgumentCountError;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Typedin\Breadcrumbs\BasicNode;

class NodeTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_when_instanciate_with_the_wrong_argument_count()
    {
        $this->expectException(ArgumentCountError::class);

        new BasicNode();
    }

    /**
     * @test
     */
    public function it_cannot_be_instanciate_with_an_empty_url()
    {
        $url = '';
        $name = 'home';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node url cannot be empty.');

        new BasicNode($url, $name);
    }

    /**
     * @test
     */
    public function it_cannot_be_instanciate_with_an_empty_name()
    {
        $url = '/';
        $name = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node name cannot be empty.');

        new BasicNode($url, $name);
    }
}
