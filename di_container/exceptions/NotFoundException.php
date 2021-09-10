<?php

namespace core\di_container\exceptions;
use Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{

}