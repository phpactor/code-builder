<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection;

use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Builder\ClassBuilder;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;

class WorseBuilderFactory implements BuilderFactory
{
    /**
     * @var Reflector|ClassName
     */
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function fromSource(string $source): SourceCodeBuilder
    {
        $classes = $this->reflector->reflectClassesIn($source);
        $builder = SourceCodeBuilder::create();

        foreach ($classes as $class) {
            if ($class->isClass()) {
                $this->build('class', $builder, $class);
                continue;
            }

            if ($class->isInterface()) {
                $this->build('interface', $builder, $class);
                continue;
            }

            // TODO: traits not currently supported
            // if ($class->isTrait()) {
            //     $this->build('trait', $builder, $class);
            //     continue;
            // }
        }

        return $builder;
    }

    private function build(string $type, SourceCodeBuilder $builder, ReflectionClassLike $reflectionClass)
    {
        $classBuilder = $builder->$type($reflectionClass->name()->short());
        $builder->namespace($reflectionClass->name()->namespace());

        if ($reflectionClass->isClass()) {
            // TODO: Worse reflection doesn't support ->belongingTo properties
            foreach ($reflectionClass->properties()->belongingTo($reflectionClass->name()) as $property) {
                $this->buildProperty($classBuilder, $property);
            }
        }

        foreach ($reflectionClass->methods()->belongingTo($reflectionClass->name()) as $method) {
            $this->buildMethod($classBuilder, $method);
        }
    }

    private function buildProperty(ClassBuilder $classBuilder, ReflectionProperty $property)
    {
        $propertyBuilder = $classBuilder->property($property->name());
        $propertyBuilder->visibility((string) $property->visibility());

        $type = $property->inferredTypes()->best();
        if ($type->isDefined()) {
            $this->resolveClassMemberType($classBuilder, $property->class()->name(), $type);
            $propertyBuilder->type((string) $type->short());
        }
    }

    private function buildMethod(ClassBuilder $classBuilder, ReflectionMethod $method)
    {
        $methodBuilder = $classBuilder->method($method->name());
        $methodBuilder->visibility((string) $method->visibility());

        if ($method->returnType()->isDefined()) {
            $type = $method->returnType();
            $this->resolveClassMemberType($classBuilder, $method->class()->name(), $type);
            $methodBuilder->returnType($type->short());

        }

        foreach ($method->parameters() as $parameter) {
            $this->buildParameter($methodBuilder, $method, $parameter);
        }
    }

    private function buildParameter(MethodBuilder $methodBuilder, ReflectionMethod $method, ReflectionParameter $parameter)
    {
        $parameterBuilder = $methodBuilder->parameter($parameter->name());

        if ($parameter->type()->isDefined()) {
            $type = $parameter->type();
            $this->resolveClassMemberType($methodBuilder->end(), $method->class()->name(), $type);
            $parameterBuilder->type($type->short());
        }

        if ($parameter->default()->isDefined()) {
            $parameterBuilder->defaultValue($parameter->default()->value());
        }
    }

    private function resolveClassMemberType(ClassBuilder $classBuilder, ClassName $classType, Type $type)
    {
        if ($type->isClass() && $classType->namespace() != $type->className()->namespace()) {
            $classBuilder->end()->use((string) $type);
        }
    }
}
