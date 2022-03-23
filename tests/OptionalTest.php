<?php

namespace Tests;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use SymfonyBoot\Optional;
use SymfonyBoot\OptionalInvalidStateException;
use Throwable;
use TypeError;

class OptionalTest extends TestCase
{
    public function testEmptyMustBeSingleton(): void
    {
        $this->assertSame(Optional::empty(), Optional::empty());
    }

    public function testEmptyProducesEmptyInstance(): void
    {
        $this->assertFalse(Optional::empty()->isPresent());
        $this->assertTrue(Optional::empty()->isEmpty());
    }

    public function testEmptyValueThrowExceptionWhenCallGet(): void
    {
        $this->expectException(OptionalInvalidStateException::class);
        Optional::empty()->get();
    }

    public function testOfWithNullValueCauseException(): void
    {
        $this->expectException(OptionalInvalidStateException::class);
        Optional::of(null);
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testOfFromNonNullValueProducesNonEmptyOptional($value): void
    {
        $optional = Optional::of($value);

        $this->assertNotSame(Optional::empty(), $optional);
        $this->assertTrue($optional->isPresent());
        $this->assertFalse($optional->isEmpty());
        $this->assertSame($value, $optional->get());
    }

    public function testOfNullableFromNullValueProducesAnEmptyOptional(): void
    {
        $this->assertSame(Optional::empty(), Optional::ofNullable(null));
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testOfNullableFromNonNullValueProducesNonEmptyOptional($value): void
    {
        $optional = Optional::ofNullable($value);

        $this->assertNotSame(Optional::empty(), $optional);
        $this->assertTrue($optional->isPresent());
        $this->assertFalse($optional->isEmpty());
        $this->assertSame($value, $optional->get());
    }

    public function testIfPresentIsNotExecutedIfValueIsNull(): void
    {
        $neverCalled = $this->getInvokableMock();
        $neverCalled->expects($this->never())->method('__invoke');
        Optional::empty()->ifPresent($neverCalled);
    }

    public function testIfPresentThrowExceptionIfActionIsNull(): void
    {
        $this->expectException(TypeError::class);
        Optional::empty()->ifPresent(null);
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testIfPresentIsExecutedWhenValueIsNonNull($value): void
    {
        $calledOnce = $this->getInvokableMock();
        $calledOnce->expects($this->once())->method('__invoke')->with($value);
        Optional::of($value)->ifPresent($calledOnce);
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testIfPresentOrElseExecuteActionWhenValueIsNonNull($value): void
    {
        $action = $this->getInvokableMock();
        $action->expects($this->once())->method('__invoke')->with($value);

        $emptyAction = $this->getInvokableMock();
        $emptyAction->expects($this->never())->method('__invoke');

        Optional::of($value)->ifPresentOrElse($action, $emptyAction);
    }

    public function testIfPresentOrElseExecuteEmptyActionWhenValueIsNull(): void
    {
        $action = $this->getInvokableMock();
        $action->expects($this->never())->method('__invoke');

        $emptyAction = $this->getInvokableMock();
        $emptyAction->expects($this->once())->method('__invoke');

        Optional::empty()->ifPresentOrElse($action, $emptyAction);
    }

    public function testIfPresentOrElseThrowExceptionIfValueIsNonNullAndActionIsNull(): void
    {
        $this->expectException(TypeError::class);
        Optional::of(new stdClass())->ifPresentOrElse(null, $this->getInvokableMock());
    }

    public function testIfPresentOrElseThrowExceptionIfValueIsNullAndEmptyActionIsNull(): void
    {
        $this->expectException(TypeError::class);
        Optional::empty()->ifPresentOrElse($this->getInvokableMock(), null);
    }

    public function testFilterIsNotExecutedIfValueIsNull(): void
    {
        $neverCalled = $this->getInvokableMock();
        $neverCalled->expects($this->never())->method('__invoke');
        $this->assertSame(Optional::empty(), Optional::empty()->filter($neverCalled));
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testFilterProducesEmptyOptionalWhenValueIsFalsy($value): void
    {
        $falseFilter = $this->getInvokableMock();
        $falseFilter->expects($this->once())->method('__invoke')->with($value)->willReturn(false);
        $this->assertSame(Optional::empty(), Optional::of($value)->filter($falseFilter));
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testFilterProducesSameOptionalInstanceWhenValueIsTruly($value): void
    {
        $trueFilter = $this->getInvokableMock();
        $trueFilter->expects($this->once())->method('__invoke')->with($value)->willReturn(true);

        $optional = Optional::of($value);
        $this->assertSame($optional, $optional->filter($trueFilter));
    }

    public function testFilterThrowExceptionIfFilterIsNull(): void
    {
        $this->expectException(TypeError::class);
        Optional::of(new stdClass())->filter(null);
    }

    public function testMappingEmptyOptionalProducesEmptyOptional(): void
    {
        $neverCalled = $this->getInvokableMock();
        $neverCalled->expects($this->never())->method('__invoke');

        $this->assertSame(Optional::empty(), Optional::empty()->map($neverCalled));
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testMapFromNonNullValuesReturnsOptionalWithReturnValueFromMapper($value): void
    {
        $mapperResult = new stdClass();

        $mapper = $this->getInvokableMock();
        $mapper->expects($this->once())->method('__invoke')->with($value)->willReturn($mapperResult);

        $optional = Optional::of($value)->map($mapper);

        $this->assertNotSame(Optional::empty(), $optional);
        $this->assertSame($mapperResult, $optional->get());
    }

    public function testMapFromNonNullValuesProducesEmptyOptionalIfMapperReturnNull(): void
    {
        $mapper = $this->getInvokableMock();
        $mapper->expects($this->once())->method('__invoke')->willReturn(null);

        $this->assertSame(Optional::empty(), Optional::of(new stdClass())->map($mapper));
    }

    public function testMapThrowExceptionIfMapperIsNull(): void
    {
        $this->expectException(TypeError::class);
        Optional::of(new stdClass())->map(null);
    }

    public function testFlatMapFromEmptyOptionalProducesEmptyOptional(): void
    {
        $neverCalled = $this->getInvokableMock();
        $neverCalled->expects($this->never())->method('__invoke');

        $this->assertSame(Optional::empty(), Optional::empty()->flatMap($neverCalled));
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testFlatMapFromNonEmptyOptionalProducesNonEmptyOptional($value): void
    {
        $result = new stdClass();

        $mapper = $this->getInvokableMock();
        $mapper->expects($this->once())->method('__invoke')->with($value)->willReturn($result);

        $this->assertSame($result, Optional::of($value)->flatMap($mapper));
    }

    public function testFlatMapFromNonEmptyOptionalWithNullResultMustThrowException(): void
    {
        $mapper = $this->getInvokableMock();
        $mapper->expects($this->once())->method('__invoke')->willReturn(null);

        $this->expectException(OptionalInvalidStateException::class);

        Optional::of(new stdClass())->flatMap($mapper);
    }

    public function testFlatMapThrowExceptionIfMapperIsNull(): void
    {
        $this->expectException(TypeError::class);
        Optional::of(new stdClass())->flatMap(null);
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testOrFromNonEmptyOptionalReturnSameObject($value): void
    {
        $neverCalled = $this->getInvokableMock();
        $neverCalled->expects($this->never())->method('__invoke');

        $optional = Optional::of($value);
        $this->assertSame($optional, $optional->or($neverCalled));
    }

    public function testOrFromEmptyOptionalReturnOtherOptional(): void
    {
        $called = $this->getInvokableMock();
        $called->expects($this->once())->method('__invoke')->willReturn(Optional::of(new stdClass()));

        $optional = Optional::empty();
        $this->assertNotSame($optional, $optional->or($called));
    }

    public function testOrThrowExceptionIfOptionalIsNotEmptyAndActionIsNull(): void
    {
        $this->expectException(TypeError::class);
        Optional::of(new stdClass())->or(null);
    }

    public function testOrThrowExceptionIfOptionalIsNotEmptyAndActionResultIsNotInstanceOfOptional(): void
    {
        $this->expectException(TypeError::class);

        $action = $this->getInvokableMock();
        $action->expects($this->once())->method('__invoke')->willReturn(new stdClass());

        Optional::empty()->or($action);
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testOrElseRetrievesGivenValueOnEmptyOptional($value): void
    {
        $this->assertSame($value, Optional::empty()->orElse($value));
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testOrElseRetrievesOptionalValueWhenValueIsNonNull($value): void
    {
        $this->assertSame($value, Optional::of($value)->orElse(new stdClass()));
    }

    public function testOrElsePermitsNull(): void
    {
        $this->assertNull(Optional::empty()->orElse(null));
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testOrElseGetReturnActionResultValueOnEmptyOptional($value): void
    {
        $action = $this->getInvokableMock();
        $action->expects($this->once())->method('__invoke')->willReturn($value);

        $this->assertSame($value, Optional::empty()->orElseGet($action));
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testOrElseGetReturnOptionalValueIfValueIsNonNull($value): void
    {
        $action = $this->getInvokableMock();
        $action->expects($this->never())->method('__invoke');

        $this->assertSame($value, Optional::of($value)->orElseGet($action));
    }

    public function testOrElseGetThrowExceptionIfOptionalIsEmptyAndCallableIsNull(): void
    {
        $this->expectException(TypeError::class);
        Optional::empty()->orElseGet(null);
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testOrElseThrowRetrievesGivenValueWhenValueIsAvailable($value): void
    {
        $this->assertSame($value, Optional::of($value)->orElseThrow(new Exception()));
    }

    public function testOrElseThrowThrowsExceptionOnEmptyOptional(): void
    {
        $exception = new Exception();

        try {
            Optional::empty()->orElseThrow($exception);
            $this->fail('No exception was thrown, expected Optional#orElseThrow() to throw one');
        } catch (Throwable $caught) {
            $this->assertSame($exception, $caught);
        }
    }

    public function testOrElseThrowThrowsOptionalInvalidStateException(): void
    {
        $this->expectException(OptionalInvalidStateException::class);
        Optional::empty()->orElseThrow();
    }

    public function testEqualsIsTrueOnlyThisObjectIsSameOrValueIsSame(): void
    {
        $value1 = new stdClass();
        $value2 = new stdClass();

        $this->assertFalse(Optional::of($value1)->equals(Optional::of($value2)));
        $this->assertFalse(Optional::of($value1)->equals($value1));
        $this->assertFalse(Optional::of($value1)->equals('foo'));
        $this->assertTrue(Optional::empty()->equals(Optional::empty()));
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testEqualsIsTrueWhenValueIsSame($value): void
    {
        $this->assertTrue(Optional::of($value)->equals(Optional::of($value)));
    }

    /**
     * @dataProvider getNonNullValues
     */
    public function testEmptyOptionalNeverEqualsNonEmptyOptional($value): void
    {
        $this->assertFalse(Optional::of($value)->equals(Optional::empty()));
        $this->assertFalse(Optional::empty()->equals(Optional::of($value)));
    }

    public function testToString(): void
    {
        $this->assertSame('Optional.empty', (string) Optional::empty());
        $this->assertSame('Optional[foo]', (string) Optional::of('foo'));
    }

    public function getNonNullValues(): array
    {
        return [
            [new stdClass()],
            [Optional::of(1)],
            [''],
            [false],
            [0],
            ['0'],
            [0.0],
            [[]],
            ['test'],
            [123],
            [123.4],
            [['test', 'multi', ['array']]],
        ];
    }

    /**
     * @return MockObject|stdClass
     */
    private function getInvokableMock()
    {
        $invokable = $this->getMockBuilder(stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();
        assert(is_callable($invokable) || $invokable instanceof MockObject);
        return $invokable;
    }
}
