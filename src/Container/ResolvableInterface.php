<?php
namespace Compose\Container;


/**
 * Class ServiceInterface
 *
 * This is a type-hint interface for letting the Store know that this class is a Service for the Store
 * This interface does not provide any methods
 *
 * The purpose of this Interface is for Store to recognize the class so that it can try to instantiate or delegate
 */
interface ResolvableInterface {}