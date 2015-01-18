<?php
/**
 * ShouldPHP
 *
 * @author  Gabriel Jacinto <gamjj74@hotmail.com>
 * @status  dev
 * @link    https://github.com/GabrielJMJ/ShouldPHP
 * @license MIT
 */
 
namespace Gabrieljmj\Should\Condition\TheClass;

use Gabrieljmj\Should\Condition\TheClass\AbstractClassCondition;
use Gabrieljmj\Should\Assert\TheClass\Be\Equals;
use Gabrieljmj\Should\Assert\TheClass\Be\Instance;

class Be extends AbstractClassCondition
{
    public function equals($arg, $message = null)
    {
        $assert = new Equals($this->class, $arg);
        $assert->setMessage($message);
        $this->addAssert($assert);
    }
    
    public function instance($arg, $message = null)
    {
        $assert = new Instance($this->class, $arg);
        $assert->setMessage($message);
        $this->addAssert($assert);
    }
}