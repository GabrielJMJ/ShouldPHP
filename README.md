ShouldPHP
=========
[![Build Status](https://travis-ci.org/GabrielJMJ/ShouldPHP.svg?branch=dev)](https://travis-ci.org/GabrielJMJ/ShouldPHP)

**ShouldPHP** is (or will be) a library for you, developer, to test your classes and objects and their methods and properties with a simple syntax based on "should". The idea came to my mind after seeing the framework [Should.JS](https://github.com/shouldjs/should.js) for [Node.JS](nodejs.org).

* A poor documentation below
* Maybe has bugs

###Status:
In-development (current branch: *dev*).

###Quick examples of use

```php
#test.php

use Gabrieljmj\Should\Ambient;

$a = new Ambient('test');
$a->theClass('Employee')->should->be->instance('Person');
$a->theProperty('Employee', 'role')->should->be->equal('Conserje');

return $a;
```
Console:
```
php bin/should execute test.php
```
<div style="text-align: center;"><img src="http://i.imgur.com/1n9zBaP.png"/></div>
*Yes, I use Windows. Judge me.*

Several:
```json
//should.json
{
    "ambients": [
        "test.php",
        "test2.php",
        "test3.php",
        "othertests/",
        "Namespace\\YourAmbient"
    ]
}
```
```
php bin/should execute should.json
```

Ambient object (tests suffix: ```test```):
```php
namespace Namespace;

use Gabrieljmj\Should\Ambient\Ambient;

class YourAmbient extends Ambient
{
    public function testInstance()
    {
        $this->theClass(new \stdClass)->should->be->equal(new \stdClass);
    }
}
```

Executing ambient (use ```/``` instead ```\```):
```
php bin/should execute Namespace/YourAmbient
```


Saving logs (command ```-s|--save```):
```
php bin/should execute test.php -s "tests.log"
```

Help:
```
php bin/should help
```

```
                                                                   ____________________
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@======@@@@@@     |                    |
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@|_____/@@@@@@@ ___|                    |
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@  \__    YOU SHOULD...!   |
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@/\@@@@@@/\@@@@@@      |                    |
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@                                     |____________________|
@|               /@@@@@@@@@@@
@\               /@@@@@@@@@@//
@@\             /@@@@@@@@@@@@//
@@@\          /@@@@@@@@@@@@//
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
@@@@@@@@@@@@@@@@@@@@@@----------. @
@@@@@@@@@@@@@@@@@@@@@|           \ @
@@@@@@@@@@@@@@@@@@@@@|            | @
@@@@@@@@@@@@@@@@@@@@@|___________/ @
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
```