<?php

/**
 * WP Smith Enforcer Class
 *
 * @package   WPS_Enforcer
 * @author    Travis Smith
 * @link      http://wpsmith.net
 * @copyright 2013 Travis Smith, WP Smith
 * @license   GPL-2.0+
 */

if ( ! class_exists( 'WPS_Enforcer' ) ) {
/**
 * Enforcer class.
 * Checks & ensures that classes contain constants & properties.
 *
 * If a constant's value is still set as 'abstract', an exception is thrown.
 * If a property's value is still set as 'abstract', an exception is thrown.
 *
 * To use, place inside __construct() in abstract class:
 * For classes with public properties OR with a public static get_instance() method:
 *      $child = get_called_class();
 *      WPS_Enforcer::add( __CLASS__, $child );
 *
 * For classes with protected or private properties:
 *      WPS_Enforcer::add( __CLASS__, $child, $this ); 
 */
class WPS_Enforcer {
    /**
     * Version of the WPS_Enforcer Class.
     *
     * @since 0.2.0
     * @access private
     * @var string $version Version number of WPS_Enforcer.
     */
    private static $version = '0.2.0';
    
    /**
     * Default value of class properties to be enforced.
     *
     * @since 0.2.0
     * @access private
     * @var string $default_property_value Default value of enforced class properties.
     */
    private static $default_property_value = 'abstract';
    
    /**
     * Default value of class constants to be enforced.
     *
     * @since 0.2.0
     * @access private
     * @var string $default_constant_value Default value of enforced class constants.
     */
    private static $default_constant_value = 'abstract';
    
    /**
     * Name of class calling Enforcer.
     *
     * @since 0.2.0
     * @access private
     * @var string $class Default value of enforced class constants.
     */
    private static $class;
    
    /**
     * Name of child class being enforced.
     *
     * @since 0.2.0
     * @access private
     * @var string $forced_class Name of child class being enforced.
     */
    private static $forced_class;
    
    /**
     * Instance of child class being enforced.
     *
     * @since 0.2.0
     * @access private
     * @var object $forced_class_obj Name of child class being enforced.
     */
    private static $forced_class_obj;
    
    /**
     * Adds the Reflection Class & calls the check method
     * on class's the properties & constants.
     * 
     * @since 0.0.1
     *
     * @see WPS_Enforcer::check()
     * @param string $class            Current abstracted class.
     * @param string $forced_class     Child Class being enforced.
     * @param string $forced_class_obj Object of Child Class being enforced.
     */
    public static function add( $class, $forced_class, $forced_class_obj = null ) {
        //* Setup Everything
        self::$class            = $class;
        self::$forced_class     = $forced_class;
        self::$forced_class_obj = $forced_class_obj;
        
        $reflection = new ReflectionClass( $class );
        
        //* Constants
        $forced_constants = $reflection->getConstants();
        WPS_Enforcer::check( $forced_constants, 'constant' );
        
        //* Properties
        if ( !is_null( $forced_class_obj ) || method_exists( $forced_class, 'get_instance' ) )
            $forced_properties = $reflection->getProperties( ReflectionProperty::IS_PUBLIC + ReflectionProperty::IS_PROTECTED );
        else
            $propertiesForced = $reflection->getProperties( ReflectionProperty::IS_PUBLIC );
        WPS_Enforcer::check( $forced_properties, 'property' );
        
    }
    
    /**
     * Sets default properties of property & constant.
     *
     * @param string $property Name of property (property or constant).
     * @param mixed  $value    Value of property (property or constant).
     * @return bool            Whether property was successfully set.
     */
    public static function set_prop( $property, $value ) {
        switch ( $property ) {
            case 'default_constant':
            case 'default_property':
                $property = sprintf( '%s_value', $property );
                break;
            case 'constant':
            case 'property':
                $property = sprintf( 'default_%s_value', $property );
                break;
            case 'default_constant_value':
            case 'default_property_value':
                break;
            default:
                return false;
        }
        
        self::${$property} = $value;
        return true;
    }
    
    /**
     * Checks class to enforce whether a constant or property has been set.
     *
     * @since 0.0.1
     *
     * @param string $c      Abstracted class.
     * @param string $forced Class being enforced.
     * @param string $type   Whether a constant or property.
     */
    public static function check( $forced, $type ) {
        if ( is_null( $forced ) ) return;
        $fc = (string)self::$forced_class;
        foreach ( $forced as $t => $v ) {
            switch ( $type ) {
                case 'constant':
                    //* Constant has to be either a string, float or int
                    if ( self::$default_constant_value == constant( "$fc::$t" ) ) {
                        //* throw Exception: "Undefined [forced constant] in [enforced class name]" 
                        throw new Exception( sprintf( 'Undefined %s in %s noted', $t, $fc ) );
                    }
                    break;
                case 'property':
                    //* Make property accessible
                    $v->setAccessible( true );
                    
                    //* Set default value to null
                    $val = null;
                    //* Preferably, use passed object
                    if( !is_null( self::$forced_class_obj ) )
                        $val = $v->getValue( self::$forced_class_obj );
                    //* if get_instance method exists, then use that
                    elseif ( method_exists( $fc, 'get_instance' ) )
                        $val = $v->getValue( $fc::get_instance() );
                    
                    //* Now check
                    if ( is_null( $val ) || self::$default_property_value === $val ) {
                        //* throw Exception: "Undefined [forced property] in [enforced class name]"
                        throw new Exception( sprintf( 'Undefined $%s in %s noted', $v->name, $fc ) );
                    }
                    break;
            }
        }
    }
}
}

