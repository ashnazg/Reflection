<?php
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2018 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\Php\Factory;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock as DocBlockDescriptor;
use phpDocumentor\Reflection\File as SourceFile;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Middleware\Middleware;
use phpDocumentor\Reflection\Php\Class_ as ClassElement;
use phpDocumentor\Reflection\Php\File as FileElement;
use phpDocumentor\Reflection\Php\Function_ as FunctionElement;
use phpDocumentor\Reflection\Php\Interface_ as InterfaceElement;
use phpDocumentor\Reflection\Php\NodesFactory;
use phpDocumentor\Reflection\Php\ProjectFactoryStrategy;
use phpDocumentor\Reflection\Php\StrategyContainer;
use phpDocumentor\Reflection\Php\Trait_ as TraitElement;
use PhpParser\Comment as CommentNode;
use PhpParser\Comment\Doc as DocBlockNode;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\Function_ as FunctionNode;
use PhpParser\Node\Stmt\Interface_ as InterfaceNode;
use PhpParser\Node\Stmt\Namespace_ as NamespaceNode;
use PhpParser\Node\Stmt\Trait_ as TraitNode;

/**
 * Test case for \phpDocumentor\Reflection\Php\Factory\File
 * @coversDefaultClass \phpDocumentor\Reflection\Php\Factory\File
 * @covers ::<!public>
 * @covers ::__construct
 */
class FileTest extends TestCase
{
    /**
     * @var m\MockInterface
     */
    private $nodesFactoryMock;

    protected function setUp()
    {
        $this->nodesFactoryMock = m::mock(NodesFactory::class);
        $this->fixture = new File($this->nodesFactoryMock);
    }

    /**
     * @covers ::matches
     */
    public function testMatches()
    {
        $this->assertFalse($this->fixture->matches(new \stdClass()));
        $this->assertTrue($this->fixture->matches(m::mock(SourceFile::class)));
    }

    /**
     * @covers ::create
     */
    public function testFileWithFunction()
    {
        $functionNode = new FunctionNode('myFunction');
        $this->nodesFactoryMock->shouldReceive('create')
            ->with(file_get_contents(__FILE__))
            ->andReturn(
                [
                    $functionNode,
                ]
            );
        $strategyMock = m::mock(ProjectFactoryStrategy::class);
        $containerMock = m::mock(StrategyContainer::class);

        $strategyMock->shouldReceive('create')
            ->with($functionNode, $containerMock, m::any())
            ->andReturn(new FunctionElement(new Fqsen('\myFunction()')));

        $containerMock->shouldReceive('findMatching')
            ->with($functionNode)
            ->andReturn($strategyMock);

        /** @var FileElement $file */
        $file = $this->fixture->create(new SourceFile\LocalFile(__FILE__), $containerMock);

        $this->assertEquals(__FILE__, $file->getPath());
        $this->assertArrayHasKey('\myFunction()', $file->getFunctions());
    }

    /**
     * @covers ::create
     */
    public function testFileWithClass()
    {
        $classNode = new ClassNode('myClass');
        $this->nodesFactoryMock->shouldReceive('create')
            ->with(file_get_contents(__FILE__))
            ->andReturn(
                [
                    $classNode,
                ]
            );
        $strategyMock = m::mock(ProjectFactoryStrategy::class);
        $containerMock = m::mock(StrategyContainer::class);

        $strategyMock->shouldReceive('create')
            ->with($classNode, $containerMock, m::any())
            ->andReturn(new ClassElement(new Fqsen('\myClass')));

        $containerMock->shouldReceive('findMatching')
            ->with($classNode)
            ->andReturn($strategyMock);

        /** @var FileElement $file */
        $file = $this->fixture->create(new SourceFile\LocalFile(__FILE__), $containerMock);

        $this->assertEquals(__FILE__, $file->getPath());
        $this->assertArrayHasKey('\myClass', $file->getClasses());
    }

    /**
     * @covers ::create
     */
    public function testFileWithNamespace()
    {
        $namespaceNode = new NamespaceNode(new Name('mySpace'));
        $namespaceNode->fqsen = new Fqsen('\mySpace');
        $this->nodesFactoryMock->shouldReceive('create')
            ->with(file_get_contents(__FILE__))
            ->andReturn(
                [
                    $namespaceNode,
                ]
            );

        $containerMock = m::mock(StrategyContainer::class);

        /** @var FileElement $file */
        $file = $this->fixture->create(new SourceFile\LocalFile(__FILE__), $containerMock);

        $this->assertEquals(__FILE__, $file->getPath());
        $this->assertArrayHasKey('\mySpace', $file->getNamespaces());
    }

    /**
     * @covers ::create
     */
    public function testFileWithInterface()
    {
        $interfaceNode = new InterfaceNode('myInterface');
        $this->nodesFactoryMock->shouldReceive('create')
            ->with(file_get_contents(__FILE__))
            ->andReturn(
                [
                    $interfaceNode,
                ]
            );
        $strategyMock = m::mock(ProjectFactoryStrategy::class);
        $containerMock = m::mock(StrategyContainer::class);

        $strategyMock->shouldReceive('create')
            ->with($interfaceNode, $containerMock, m::any())
            ->andReturn(new InterfaceElement(new Fqsen('\myInterface')));

        $containerMock->shouldReceive('findMatching')
            ->with($interfaceNode)
            ->andReturn($strategyMock);

        /** @var FileElement $file */
        $file = $this->fixture->create(new SourceFile\LocalFile(__FILE__), $containerMock);

        $this->assertEquals(__FILE__, $file->getPath());
        $this->assertArrayHasKey('\myInterface', $file->getInterfaces());
    }

    /**
     * @covers ::create
     */
    public function testFileWithTrait()
    {
        $traitNode = new TraitNode('\myTrait');
        $this->nodesFactoryMock->shouldReceive('create')
            ->with(file_get_contents(__FILE__))
            ->andReturn(
                [
                    $traitNode,
                ]
            );
        $strategyMock = m::mock(ProjectFactoryStrategy::class);
        $containerMock = m::mock(StrategyContainer::class);

        $strategyMock->shouldReceive('create')
            ->with($traitNode, $containerMock, m::any())
            ->andReturn(new TraitElement(new Fqsen('\myTrait')));

        $containerMock->shouldReceive('findMatching')
            ->with($traitNode)
            ->andReturn($strategyMock);

        /** @var FileElement $file */
        $file = $this->fixture->create(new SourceFile\LocalFile(__FILE__), $containerMock);

        $this->assertEquals(__FILE__, $file->getPath());
        $this->assertArrayHasKey('\myTrait', $file->getTraits());
    }

    /**
     * @covers ::create
     */
    public function testMiddlewareIsExecuted()
    {
        $file = new FileElement('aa', __FILE__);
        $this->nodesFactoryMock->shouldReceive('create')
            ->with(file_get_contents(__FILE__))
            ->andReturn([]);

        $middleware = m::mock(Middleware::class);
        $middleware->shouldReceive('execute')
            ->once()
            ->andReturn($file);
        $fixture = new File($this->nodesFactoryMock, [$middleware]);

        $containerMock = m::mock(StrategyContainer::class);
        $result = $fixture->create(new SourceFile\LocalFile(__FILE__), $containerMock);

        $this->assertSame($result, $file);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMiddlewareIsChecked()
    {
        new File($this->nodesFactoryMock, [new \stdClass()]);
    }

    /**
     * @covers ::create
     */
    public function testFileDocBlockWithNamespace()
    {
        $docBlockNode = new DocBlockNode('');
        $docBlockDescriptor = new DocBlockDescriptor('');

        $namespaceNode = new NamespaceNode(new Name('mySpace'));
        $namespaceNode->fqsen = new Fqsen('\mySpace');
        $namespaceNode->setAttribute('comments', [$docBlockNode]);

        $this->nodesFactoryMock->shouldReceive('create')
            ->with(file_get_contents(__FILE__))
            ->andReturn([$namespaceNode]);

        $strategyMock = m::mock(ProjectFactoryStrategy::class);
        $containerMock = m::mock(StrategyContainer::class);

        $strategyMock->shouldReceive('create')
            ->with($docBlockNode, $containerMock, m::any())
            ->andReturn($docBlockDescriptor);

        $containerMock->shouldReceive('findMatching')
            ->with($docBlockNode)
            ->andReturn($strategyMock);

        /** @var FileElement $file */
        $file = $this->fixture->create(new SourceFile\LocalFile(__FILE__), $containerMock);

        $this->assertSame($docBlockDescriptor, $file->getDocBlock());
    }

    /**
     * @covers ::create
     */
    public function testFileDocBlockWithClass()
    {
        $docBlockNode = new DocBlockNode('');
        $docBlockDescriptor = new DocBlockDescriptor('');

        $classNode = new ClassNode('myClass');
        $classNode->setAttribute('comments', [$docBlockNode, new DocBlockNode('')]);

        $this->nodesFactoryMock->shouldReceive('create')
            ->with(file_get_contents(__FILE__))
            ->andReturn([$classNode]);

        $strategyMock = m::mock(ProjectFactoryStrategy::class);
        $containerMock = m::mock(StrategyContainer::class);

        $strategyMock->shouldReceive('create')
            ->once()
            ->with($classNode, $containerMock, m::any())
            ->andReturn(new ClassElement(new Fqsen('\myClass')));
        $containerMock->shouldReceive('findMatching')
            ->with($classNode)
            ->andReturn($strategyMock);

        $strategyMock->shouldReceive('create')
            ->with($docBlockNode, $containerMock, m::any())
            ->andReturn($docBlockDescriptor);

        $containerMock->shouldReceive('findMatching')
            ->with($docBlockNode)
            ->andReturn($strategyMock);

        /** @var FileElement $file */
        $file = $this->fixture->create(new SourceFile\LocalFile(__FILE__), $containerMock);

        $this->assertSame($docBlockDescriptor, $file->getDocBlock());
    }

    /**
     * @covers ::create
     */
    public function testFileDocBlockWithComments()
    {
        $docBlockNode = new DocBlockNode('');
        $docBlockDescriptor = new DocBlockDescriptor('');

        $namespaceNode = new NamespaceNode(new Name('mySpace'));
        $namespaceNode->fqsen = new Fqsen('\mySpace');
        $namespaceNode->setAttribute('comments', [new CommentNode('@codingStandardsIgnoreStart'), $docBlockNode]);

        $this->nodesFactoryMock->shouldReceive('create')
            ->with(file_get_contents(__FILE__))
            ->andReturn([$namespaceNode]);

        $strategyMock = m::mock(ProjectFactoryStrategy::class);
        $containerMock = m::mock(StrategyContainer::class);

        $strategyMock->shouldReceive('create')
            ->with($docBlockNode, $containerMock, m::any())
            ->andReturn($docBlockDescriptor);

        $containerMock->shouldReceive('findMatching')
            ->with($docBlockNode)
            ->andReturn($strategyMock);

        /** @var FileElement $file */
        $file = $this->fixture->create(new SourceFile\LocalFile(__FILE__), $containerMock);

        $this->assertSame($docBlockDescriptor, $file->getDocBlock());
    }
}
