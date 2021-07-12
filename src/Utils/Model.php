<?php

namespace KgBot\Magento\Utils;

use Illuminate\Support\Str;
use ReflectionObject;
use ReflectionProperty;

class Model
{
	protected $entity;
	protected $primaryKey;
	protected $modelClass = self::class;
	protected $fillable   = [];

	public function __construct( $data = [] ) {
		$data          = (array) $data;

		foreach ( $data as $key => $value ) {
			$customSetterMethod = 'set' . ucfirst( Str::camel( $key ) ) . 'Attribute';

			if ( !method_exists( $this, $customSetterMethod ) ) {
				$this->setAttribute( $key, $value );
			} else {
				$this->setAttribute( $key, $this->{$customSetterMethod}( $value ) );
			}
		}
	}

	protected function setAttribute( $attribute, $value ) {
		$this->{$attribute} = $value;
	}

	public function __toString() {
		return json_encode( $this->toArray() );
	}

	public function toArray() {
		$data       = [];
		$class      = new ReflectionObject( $this );
		$properties = $class->getProperties( ReflectionProperty::IS_PUBLIC );

		/** @var ReflectionProperty $property */
		foreach ( $properties as $property ) {
			$data[ $property->getName() ] = $this->{$property->getName()};
		}

		return $data;
	}

	public function getEntity() {
		return $this->entity;
	}

	public function setEntity( $new_entity ) {
		$this->entity = $new_entity;
	}
}