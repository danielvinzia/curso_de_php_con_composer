<?php
namespace Neos\Fusion\Tests\Unit\Core;

/*
 * This file is part of the Neos.Fusion package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Eel\EelEvaluatorInterface;
use Neos\Eel\ProtectedContext;
use Neos\Flow\Exception;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Flow\Tests\UnitTestCase;
use Neos\Fusion\Core\ExceptionHandlers\ThrowingHandler;
use Neos\Fusion\Core\Runtime;
use Neos\Fusion\Exception\RuntimeException;

class RuntimeTest extends UnitTestCase
{
    /**
     * if the rendering leads to an exception
     * the exception is transformed into 'content' by calling 'handleRenderingException'
     *
     * @test
     */
    public function renderHandlesExceptionDuringRendering()
    {
        $controllerContext = $this->getMockBuilder(ControllerContext::class)->disableOriginalConstructor()->getMock();
        $runtimeException = new RuntimeException('I am a parent exception', 123, new Exception('I am a previous exception'));
        $runtime = $this->getMockBuilder(Runtime::class)->setMethods(['evaluate', 'handleRenderingException'])->setConstructorArgs([[], $controllerContext])->getMock();
        $runtime->injectSettings(['rendering' => ['exceptionHandler' => ThrowingHandler::class]]);
        $runtime->expects(self::any())->method('evaluate')->will(self::throwException($runtimeException));
        $runtime->expects(self::once())->method('handleRenderingException')->with('/foo/bar', $runtimeException)->will(self::returnValue('Exception Message'));

        $output = $runtime->render('/foo/bar');

        self::assertEquals('Exception Message', $output);
    }

    /**
     * exceptions are rendered using the renderer from configuration
     *
     * if this handler throws exceptions, they are not handled
     *
     * @test
     */
    public function handleRenderingExceptionThrowsException()
    {
        $this->expectException(Exception::class);
        $objectManager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->setMethods(['isRegistered', 'get'])->getMock();
        $controllerContext = $this->getMockBuilder(ControllerContext::class)->disableOriginalConstructor()->getMock();
        $runtimeException = new RuntimeException('I am a parent exception', 123, new Exception('I am a previous exception'));
        $runtime =  new Runtime([], $controllerContext);
        $this->inject($runtime, 'objectManager', $objectManager);
        $exceptionHandlerSetting = 'settings';
        $runtime->injectSettings(['rendering' => ['exceptionHandler' => $exceptionHandlerSetting]]);

        $objectManager->expects(self::once())->method('isRegistered')->with($exceptionHandlerSetting)->will(self::returnValue(true));
        $objectManager->expects(self::once())->method('get')->with($exceptionHandlerSetting)->will(self::returnValue(new ThrowingHandler()));

        $runtime->handleRenderingException('/foo/bar', $runtimeException);
    }

    /**
     * @test
     */
    public function evaluateProcessorForEelExpressionUsesProtectedContext()
    {
        $controllerContext = $this->getMockBuilder(ControllerContext::class)->disableOriginalConstructor()->getMock();

        $eelEvaluator = $this->createMock(EelEvaluatorInterface::class);
        $runtime = $this->getAccessibleMock(Runtime::class, ['dummy'], [[], $controllerContext]);

        $this->inject($runtime, 'eelEvaluator', $eelEvaluator);


        $eelEvaluator->expects(self::once())->method('evaluate')->with('q(node).property("title")', $this->isInstanceOf(ProtectedContext::class));

        $runtime->pushContextArray([
            'node' => 'Foo'
        ]);

        $runtime->_call('evaluateEelExpression', 'q(node).property("title")');
    }

    /**
     * @test
     */
    public function evaluateWithCacheModeUncachedAndUnspecifiedContextThrowsException()
    {
        $this->expectException(\Neos\Fusion\Exception::class);
        $this->expectExceptionCode(1395922119);
        $mockControllerContext = $this->getMockBuilder(ControllerContext::class)->disableOriginalConstructor()->getMock();
        $runtime = new Runtime([
            'foo' => [
                'bar' => [
                    '__meta' => [
                        'cache' => [
                            'mode' => 'uncached'
                        ]
                    ]
                ]
            ]
        ], $mockControllerContext);

        $runtime->evaluate('foo/bar');
    }

    /**
     * @test
     */
    public function renderRethrowsSecurityExceptions()
    {
        $this->expectException(\Neos\Flow\Security\Exception::class);
        $controllerContext = $this->getMockBuilder(ControllerContext::class)->disableOriginalConstructor()->getMock();
        $securityException = new \Neos\Flow\Security\Exception();
        $runtime = $this->getMockBuilder(Runtime::class)->setMethods(['evaluate', 'handleRenderingException'])->setConstructorArgs([[], $controllerContext])->getMock();
        $runtime->expects(self::any())->method('evaluate')->will(self::throwException($securityException));

        $runtime->render('/foo/bar');
    }

    /**
     * @test
     */
    public function runtimeCurrentContextStackWorksSimplePushPop()
    {
        $controllerContext = $this->getMockBuilder(ControllerContext::class)->disableOriginalConstructor()->getMock();
        $runtime = new Runtime([], $controllerContext);

        self::assertSame([], $runtime->getCurrentContext(), 'context should be empty at start.');

        $runtime->pushContext('foo', 'bar');

        self::assertSame(['foo' => 'bar'], $runtime->getCurrentContext(), 'Runtime context has "foo => bar".');

        self::assertSame(['foo' => 'bar'], $runtime->popContext(), 'Runtime context returns "foo => bar" on pop.');

        self::assertSame([], $runtime->getCurrentContext(), 'Runtime context should be empty again at end.');
    }

    /**
     * @test
     */
    public function runtimeCurrentContextStack3PushesAndPops()
    {
        $controllerContext = $this->getMockBuilder(ControllerContext::class)->disableOriginalConstructor()->getMock();
        $runtime = new Runtime([], $controllerContext);

        self::assertSame([], $runtime->getCurrentContext(), 'empty at start');

        $context1 = ['foo' => 'bar'];
        $runtime->pushContext('foo', 'bar');
        self::assertSame($context1, $runtime->getCurrentContext(), 'context1');

        $context2 = ['foo' => 123, 'buz' => 'baz'];
        $runtime->pushContextArray($context2);
        self::assertSame($context2, $runtime->getCurrentContext(), 'context2 (which overrides the only key "foo" of context1)');

        // $context3 = ['bla' => 456];
        $all3MergedContext = ['foo' => 123, 'buz' => 'baz', 'bla' => 456];
        $runtime->pushContext('bla', 456);
        self::assertSame($all3MergedContext, $runtime->getCurrentContext(), 'context1 context2 context3');
        self::assertSame($all3MergedContext, $runtime->popContext(), 'context1 context2 context3');

        self::assertSame($context2, $runtime->getCurrentContext(), 'context2');
        self::assertSame($context2, $runtime->popContext(), 'context2');

        self::assertSame($context1, $runtime->getCurrentContext(), 'context1');
        self::assertSame($context1, $runtime->popContext(), 'context1');

        self::assertSame([], $runtime->getCurrentContext(), 'empty at end');
    }
}
