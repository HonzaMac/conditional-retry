<?php

namespace Bileto\Lib\tests\Utils;

use LogicException;
use PHPUnit\Framework\TestCase;
use Throwable;

class RetryTest extends TestCase
{

    /**
     * @dataProvider retryCountsDataProvider
     */
    public function testFunctional($expectedCalls, $numberOfRetries)
    {
        $retryCount = 0;
        retryConditional(
            function () use (&$retryCount) {
                $retryCount++;
            },
            function () {
                return true;
            },
            $numberOfRetries
        );

        self::assertEquals($expectedCalls, $retryCount);
    }

    public function retryCountsDataProvider()
    {
        return [
            [1, 1],
            [2, 2],
            [100, 100]
        ];
    }

    /**
     * @dataProvider returnValuesDataProvider
     */
    public function testReturnValue($expectedReturnValue, $retries)
    {
        $lastReturnValue = retryConditional(
            function () use ($expectedReturnValue) { return $expectedReturnValue;},
            function () { return true;},
            $retries
        );

        self::assertEquals($expectedReturnValue, $lastReturnValue);
    }

    public function returnValuesDataProvider()
    {
        return [
            'return value after one call, no retry' => ['someValue', 0],
            'return value after one retry' => ['someValue', 1],
        ];
    }

    public function testFirstRetrySuccess()
    {
        $cnt = 0;
        $return = retryConditional(
            function () use (&$cnt) {
                $cnt++;

                return 'someValue';
            },
            function () use ($cnt) {
                if ($cnt === 0) {
                    return true;
                }
                if ($cnt === 1) {
                    return false;
                }

                return true;
            }
        );

        self::assertEquals(1, $cnt);
        self::assertEquals('someValue', $return);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Error
     */
    public function testRetrySuccessWithException()
    {
        retryConditional(
            function () { throw new LogicException('Error');},
            function ($value, $exception) { return $exception instanceof LogicException;}
        );
    }
    /**
     * @expectedException LogicException
     * @expectedExceptionMessage LastError
     */
    public function testChangingExceptionInretryConditional()
    {
        $cnt = 0;
        retryConditional(
            function () use (&$cnt) {
                if ($cnt === 0) {
                    throw new \Exception('Error');
                }
                if ($cnt === 1) {
                    throw new LogicException('LastError');
                }
            },
            function ($value, $exception) use (&$cnt) {
                $cnt++;
                return ($exception instanceof \Exception || $exception instanceOf LogicException);
            }
        ,2);
    }

    public function testChangingReturnValueInretryConditional()
    {
        $cnt = 0;
        $returnValue = retryConditional(
            function () use (&$cnt) {
                if ($cnt === 0) {
                    return 'errorValue';
                }
                if ($cnt === 1) {
                    return 'someValue';
                }
            },
            function () use (&$cnt) {
                $cnt++;
                return true;
            }
        ,2);
        self::assertEquals('someValue', $returnValue);
    }

    public function testNoRepeatingExceptionInAnotherTry()
    {
        $cnt = 0;
        $returnValue = retryConditional(function () use (&$cnt) {
            if ($cnt === 0) {
                throw new LogicException('First run');
            }
            if ($cnt === 1) {
                throw new LogicException('Second run');
            }
            return 'value';
        }, function ($result, Throwable $ex = null) use (&$cnt) {
            if ($cnt === 0) {
                self::assertSame('First run', $ex->getMessage());
            }
            if ($cnt === 1) {
                self::assertSame('Second run', $ex->getMessage());
            }
            if ($cnt === 2) {
                self::assertNull($ex);
            }
            $cnt++;
            return true;
        }, 1000);

        self::assertSame('value', $returnValue);
    }

}
