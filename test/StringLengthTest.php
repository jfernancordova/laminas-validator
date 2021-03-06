<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\StringLength;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class StringLengthTest extends TestCase
{
    /**
     * @var StringLength
     */
    protected $validator;

    /**
     * Creates a new StringLength object for each test method
     *
     * @return void
     */
    protected function setUp() : void
    {
        $this->validator = new StringLength();
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        ini_set('default_charset', 'UTF-8');

        /**
         * The elements of each array are, in order:
         *      - minimum length
         *      - maximum length
         *      - expected validation result
         *      - array of test input values
         */
        $valuesExpected = [
            [0, null, true, ['', 'a', 'ab']],
            [-1, null, true, ['']],
            [2, 2, true, ['ab', '  ']],
            [2, 2, false, ['a', 'abc']],
            [1, null, false, ['']],
            [2, 3, true, ['ab', 'abc']],
            [2, 3, false, ['a', 'abcd']],
            [3, 3, true, ['äöü']],
            [6, 6, true, ['Müller']],
        ];

        foreach ($valuesExpected as $element) {
            $validator = new StringLength($element[0], $element[1]);
            foreach ($element[3] as $input) {
                $this->assertEquals($element[2], $validator->isValid($input));
            }
        }
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
     * Ensures that getMin() returns expected default value
     *
     * @return void
     */
    public function testGetMin()
    {
        $this->assertEquals(0, $this->validator->getMin());
    }

    /**
     * Ensures that getMax() returns expected default value
     *
     * @return void
     */
    public function testGetMax()
    {
        $this->assertEquals(null, $this->validator->getMax());
    }

    /**
     * Ensures that setMin() throws an exception when given a value greater than the maximum
     *
     * @return void
     */
    public function testSetMinExceptionGreaterThanMax()
    {
        $max = 1;
        $min = 2;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The minimum must be less than or equal to the maximum length, but');
        $this->validator->setMax($max)->setMin($min);
    }

    /**
     * Ensures that setMax() throws an exception when given a value less than the minimum
     *
     * @return void
     */
    public function testSetMaxExceptionLessThanMin()
    {
        $max = 1;
        $min = 2;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The maximum must be greater than or equal to the minimum length, but ');
        $this->validator->setMin($min)->setMax($max);
    }

    /**
     * @return void
     */
    public function testDifferentEncodingWithValidator()
    {
        ini_set('default_charset', 'UTF-8');

        $validator = new StringLength(2, 2, 'UTF-8');
        $this->assertEquals(true, $validator->isValid('ab'));

        $this->assertEquals('UTF-8', $validator->getEncoding());
        $validator->setEncoding('ISO-8859-1');
        $this->assertEquals('ISO-8859-1', $validator->getEncoding());
    }

    /**
     * @Laminas-4352
     */
    public function testNonStringValidation()
    {
        $this->assertFalse($this->validator->isValid([1 => 1]));
    }

    public function testEqualsMessageTemplates()
    {
        $validator = $this->validator;
        $this->assertObjectHasAttribute('messageTemplates', $validator);
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables()
    {
        $validator = $this->validator;
        $this->assertObjectHasAttribute('messageVariables', $validator);
        $this->assertEquals(array_keys($validator->getOption('messageVariables')), $validator->getMessageVariables());
    }
}
