<?php
/**
 * ShouldPHP
 *
 * @author Gabriel Jacinto <gamjj74@hotmail.com>
 * @status dev
 * @link   https://github.com/GabrielJMJ/ShouldPHP
 */
 
namespace Test\Gabrieljmj\Should\Condition\TheClass;

use Gabrieljmj\Should\Condition\TheClass\Be;
use Gabrieljmj\Should\Assert\TheClass\Be\Equals;

class BeTest extends \PHPUnit_Framework_TestCase
{
    public function testAddingAssertReallyAdds()
    {
        $i1 = new \stdClass;
        $i2 = new \stdClass;
        $instance = new Be($i1);
        $instance->equals($i2);
        $assert = new Equals($i1, $i2);

        $this->assertTrue(in_array($assert, $instance->getAssertList()));
    }
}