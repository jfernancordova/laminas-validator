<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\InArray;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class InArrayTest extends TestCase
{
    /** @var InArray */
    protected $validator;

    protected function setUp() : void
    {
        $this->validator = new InArray(
            [
                 'haystack' => [1, 2, 3],
            ]
        );
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $this->assertEquals([], $this->validator->getMessages());
    }

    /**
     * Ensures that getHaystack() returns expected value
     *
     * @return void
     */
    public function testGetHaystack()
    {
        $this->assertEquals([1, 2, 3], $this->validator->getHaystack());
    }

    public function testUnsetHaystackRaisesException()
    {
        $validator = new InArray();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('haystack option is mandatory');
        $validator->getHaystack();
    }

    /**
     * Ensures that getStrict() returns expected default value
     *
     * @return void
     */
    public function testGetStrict()
    {
        $this->assertFalse($this->validator->getStrict());
    }

    public function testGivingOptionsAsArrayAtInitiation()
    {
        $validator = new InArray(
            [
                 'haystack' => [1, 'a', 2.3],
            ]
        );
        $this->assertTrue($validator->isValid(1));
        $this->assertTrue($validator->isValid(1.0));
        $this->assertTrue($validator->isValid('1'));
        $this->assertTrue($validator->isValid('a'));
        $this->assertFalse($validator->isValid('A'));
        $this->assertTrue($validator->isValid(2.3));
        $this->assertTrue($validator->isValid(2.3e0));
    }

    public function testSettingANewHaystack()
    {
        $this->validator->setHaystack([1, 'a', 2.3]);
        $this->assertEquals([1, 'a', 2.3], $this->validator->getHaystack());
    }

    /**
     * @group Laminas-337
     */
    public function testSettingNewStrictMode()
    {
        $validator = new InArray(
            [
                 'haystack' => ['test', 0, 'A', 0.0],
            ]
        );

        // test non-strict with vulnerability prevention (default choice)
        $validator->setStrict(InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY);
        $this->assertFalse($validator->getStrict());

        $validator->setStrict(InArray::COMPARE_STRICT);
        $this->assertTrue($validator->getStrict());

        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        $this->assertEquals(InArray::COMPARE_NOT_STRICT, $validator->getStrict());
    }

    public function testNonStrictSafeComparisons()
    {
        $validator = new InArray(
            [
                 'haystack' => ['test', 0, 'A', 1, 0.0],
            ]
        );

        $this->assertFalse($validator->getStrict());
        $this->assertFalse($validator->isValid('b'));
        $this->assertFalse($validator->isValid('a'));
        $this->assertTrue($validator->isValid('A'));
        $this->assertTrue($validator->isValid('0'));
        $this->assertFalse($validator->isValid('1a'));
        $this->assertTrue($validator->isValid(0));
    }

    public function testStrictComparisons()
    {
        $validator = new InArray(
            [
                 'haystack' => ['test', 0, 'A', 1, 0.0],
            ]
        );

        // bog standard strict compare
        $validator->setStrict(InArray::COMPARE_STRICT);

        $this->assertTrue($validator->getStrict());

        $this->assertTrue($validator->isValid('A'));
        $this->assertTrue($validator->isValid(0));
        $this->assertFalse($validator->isValid('b'));
        $this->assertFalse($validator->isValid('a'));
        $this->assertFalse($validator->isValid('0'));
        $this->assertFalse($validator->isValid('1a'));
    }

    public function testNonStrictComparisons()
    {
        $validator = new InArray(
            [
                 'haystack' => ['test', 0, 'A', 1, 0.0],
            ]
        );

        // non-numeric strings converted to 0
        $validator->setStrict(InArray::COMPARE_NOT_STRICT);

        $this->assertEquals(InArray::COMPARE_NOT_STRICT, $validator->getStrict());
        $this->assertTrue($validator->isValid('b'));
        $this->assertTrue($validator->isValid('a'));
        $this->assertTrue($validator->isValid('A'));
        $this->assertTrue($validator->isValid('0'));
        $this->assertTrue($validator->isValid('1a'));
        $this->assertTrue($validator->isValid(0));
    }

    public function testNonStrictSafeComparisonsRecurisve()
    {
        $validator = new InArray(
            [
                 'haystack' => [
                     ['test', 0, 'A', 0.0],
                     ['foo', 1, 'a', 'c'],
                 ],
            ]
        );

        $validator->setRecursive(true);

        $this->assertFalse($validator->getStrict());
        $this->assertFalse($validator->isValid('b'));
        $this->assertTrue($validator->isValid('a'));
        $this->assertTrue($validator->isValid('A'));
        $this->assertTrue($validator->isValid('0'));
        $this->assertFalse($validator->isValid('1a'));
        $this->assertTrue($validator->isValid(0));
    }

    public function testStrictComparisonsRecursive()
    {
        $validator = new InArray(
            [
                 'haystack' => [
                     ['test', 0, 'A', 0.0],
                     ['foo', 1, 'a', 'c'],
                 ],
            ]
        );

        // bog standard strict compare
        $validator->setStrict(InArray::COMPARE_STRICT);
        $validator->setRecursive(true);

        $this->assertTrue($validator->getStrict());
        $this->assertFalse($validator->isValid('b'));
        $this->assertTrue($validator->isValid('a'));
        $this->assertTrue($validator->isValid('A'));
        $this->assertFalse($validator->isValid('0'));
        $this->assertFalse($validator->isValid('1a'));
        $this->assertTrue($validator->isValid(0));
    }

    public function testNonStrictComparisonsRecursive()
    {
        $validator = new InArray(
            [
                 'haystack' => [
                     ['test', 0, 'A', 0.0],
                     ['foo', 1, 'a', 'c'],
                 ],
            ]
        );

        // non-numeric strings converted to 0
        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        $validator->setRecursive(true);

        $stringToNumericComparisonAssertion = PHP_MAJOR_VERSION < 8 ? 'assertTrue' : 'assertFalse';

        $this->assertEquals(InArray::COMPARE_NOT_STRICT, $validator->getStrict());
        $this->$stringToNumericComparisonAssertion($validator->isValid('b'));
        $this->assertTrue($validator->isValid('a'));
        $this->assertTrue($validator->isValid('A'));
        $this->assertTrue($validator->isValid('0'));
        $this->$stringToNumericComparisonAssertion($validator->isValid('1a'));
        $this->assertTrue($validator->isValid(0));
    }

    public function testIntegerInputAndStringInHaystack()
    {
        $validator = new InArray(
            [
                 'haystack' => ['test', 1, 2],
            ]
        );

        $validator->setStrict(InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY);
        $this->assertFalse($validator->isValid(0));

        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        $this->assertTrue($validator->isValid(0));

        $validator->setStrict(InArray::COMPARE_STRICT);
        $this->assertFalse($validator->isValid(0));
    }

    public function testFloatInputAndStringInHaystack()
    {
        $validator = new InArray(
            [
                 'haystack' => ['test', 1, 2],
            ]
        );

        $validator->setStrict(InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY);
        $this->assertFalse($validator->isValid(0.0));

        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        $this->assertTrue($validator->isValid(0.0));

        $validator->setStrict(InArray::COMPARE_STRICT);
        $this->assertFalse($validator->isValid(0.0));
    }

    public function testNumberStringInputAgainstNumberInHaystack()
    {
        $validator = new InArray(
            [
                 'haystack' => [1, 2],
            ]
        );

        $validator->setStrict(InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY);
        $this->assertFalse($validator->isValid('1asdf'));

        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        $this->assertTrue($validator->isValid('1asdf'));

        $validator->setStrict(InArray::COMPARE_STRICT);
        $this->assertFalse($validator->isValid('1asdf'));
    }

    public function testFloatStringInputAgainstNumberInHaystack()
    {
        $validator = new InArray(
            [
                 'haystack' => [1.5, 2.4],
            ]
        );

        $validator->setStrict(InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY);
        $this->assertFalse($validator->isValid('1.5asdf'));

        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        $this->assertTrue($validator->isValid('1.5asdf'));

        $validator->setStrict(InArray::COMPARE_STRICT);
        $this->assertFalse($validator->isValid('1.5asdf'));
    }

    public function testSettingStrictViaInitiation()
    {
        $validator = new InArray(
            [
                 'haystack' => ['test', 0, 'A'],
                 'strict'   => true,
            ]
        );
        $this->assertTrue($validator->getStrict());
    }

    public function testGettingRecursiveOption()
    {
        $this->assertFalse($this->validator->getRecursive());

        $this->validator->setRecursive(true);
        $this->assertTrue($this->validator->getRecursive());
    }

    public function testSettingRecursiveViaInitiation()
    {
        $validator = new InArray(
            [
                 'haystack'  => ['test', 0, 'A'],
                 'recursive' => true,
            ]
        );
        $this->assertTrue($validator->getRecursive());
    }

    public function testRecursiveDetection()
    {
        $validator = new InArray(
            [
                 'haystack'  =>
                 [
                     'firstDimension'  => ['test', 0, 'A'],
                     'secondDimension' => ['value', 2, 'a'],
                 ],
                 'recursive' => false,
            ]
        );
        $this->assertFalse($validator->isValid('A'));

        $validator->setRecursive(true);
        $this->assertTrue($validator->isValid('A'));
    }

    public function testEqualsMessageTemplates()
    {
        $validator = $this->validator;
        $this->assertObjectHasAttribute('messageTemplates', $validator);
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }
}
