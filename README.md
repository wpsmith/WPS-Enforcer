WPS-Enforcer
============

Enforcer Class for Abstract Constants &amp; Properties. It checks & ensures that classes contain constants &amp; properties. If a class constant's or property's value is still set as 'abstract', an exception is thrown.


How to Use
----------
1. Require the files in the base class or abstract class.
```php
if ( !class_exists( 'WPS_Enforcer' ) )
    require( 'class-enforcer.php' );
```

2. Place the following inside the class constructor method (typically: function __construct() {}) in any class, usually an abstract class.

    For classes with public properties OR with a public static get_instance() method:
    ```php
    $child = get_called_class();
    WPS_Enforcer::add( __CLASS__, $child );
    ```
    
    For classes with protected or private properties:
    ```
    $child = get_called_class();
    WPS_Enforcer::add( __CLASS__, $child, $this );
    ```

3. In base class, set the value of the constant and/or property to `abstract`.

Example
-------
```php
abstract class MySimpleAbstractClass {
  const FIELDCONST = 'abstract';
  protected $protected_var = 'abstract';
  public $public_var = 'abstract';

  public __construct(){
    $child = get_called_class();
    WPS_Enforcer::add( __CLASS__, $child, $this );
    // do something else
  }
}

class MySimpleExt extends MySimpleAbstractClass{
  const FIELDCONST = 'text';
  protected $protected_var = 5;
  public $public_var = 'something else';
  
  public __construct(){
    
    // do something
    parent::__construct();
  }
}
```

```php
abstract class MyAbstractClass {
  const FIELDCONST = 'abstract';
  protected $protected_var = 'abstract';
  public $public_var = 'abstract';
  public static $instance;
  
  public __construct(){
    $child = get_called_class();
    WPS_Enforcer::add( __CLASS__, $child );
    // do something else
  }
  
  public static getInstance() {
    if (!isset(self::$instance)) {
      $c = __CLASS__;
      $instance = new $c;
    }

    return self::$instance;
  }
}

class MyClassExt extends MyAbstractClass {
  const FIELDCONST = 'text';
  protected $protected_var = 5;
  public $public_var = 'something else';
  
  public init() {
    // do something
  }
}
```
