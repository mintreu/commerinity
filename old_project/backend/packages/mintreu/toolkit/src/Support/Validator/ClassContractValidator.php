<?php

namespace Mintreu\Toolkit\Support\Validator;

use Closure;
use Mintreu\Toolkit\Support\Validator\Reports\ClassContractValidatorReport;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;
use InvalidArgumentException;
use RuntimeException;

class ClassContractValidator
{
    /**
     * Validate that a given class matches an interface's method signatures.
     * Always stops on first error for performance.
     */
    public static function validate(string|object $class, string|object $interface): bool
    {
        return self::getCompatibility($class, $interface, stopOnFirstError: true)->valid;
    }

    /**
     * Throw exception unless class matches interface contract.
     * Defaults to stop on first error, can be changed to show all errors.
     */
    public static function throwUnless(string|object $class, string|object $interface, ?string $message = null, bool $stopOnFirstError = true): void
    {
        $compat = self::getCompatibility($class, $interface, $stopOnFirstError);


        if (!$compat->valid) {
            throw new RuntimeException(
                $message ?? self::formatErrorMessage($compat)
            );
        }
    }

    /**
     * Throw exception if class matches interface contract.
     * Defaults to stop on first error, can be changed to show all errors.
     */
    public static function throwIf(string|object $class, string|object $interface, ?string $message = null, bool $stopOnFirstError = true): void
    {
        $compat = self::getCompatibility($class, $interface, $stopOnFirstError);
        if ($compat->valid) {
            throw new RuntimeException($message ?? "Contract unexpectedly matched.");
        }
    }

    /**
     * Get compatibility details between class and interface.
     * Defaults to collect all errors.
     */
    public static function getCompatibility(string|object $class, string|object $interface, bool $stopOnFirstError = false): ClassContractValidatorReport
    {
        [$className, $interfaceName] = [
            self::resolveName($class),
            self::resolveName($interface)
        ];

        $errors = [];

        // Check existence
        if (!class_exists($className)) {
            $errors[] = "Class {$className} does not exist.";
            return new ClassContractValidatorReport(false, $className, $interfaceName, $errors);
        }

        if (!interface_exists($interfaceName)) {
            $errors[] = "Interface {$interfaceName} does not exist.";
            return new ClassContractValidatorReport(false, $className, $interfaceName, $errors);
        }

        $classRef = new ReflectionClass($className);
        $interfaceRef = new ReflectionClass($interfaceName);

        foreach ($interfaceRef->getMethods() as $ifaceMethod) {
            $methodName = $ifaceMethod->getName();

            if (!$classRef->hasMethod($methodName)) {
                $errors[] = "Missing method: {$methodName}()";
                if ($stopOnFirstError) return new ClassContractValidatorReport(false, $className, $interfaceName, $errors);
                continue;
            }

            $classMethod = $classRef->getMethod($methodName);

            // Parameter count
            if ($classMethod->getNumberOfParameters() !== $ifaceMethod->getNumberOfParameters()) {
                $errors[] = "Parameter count mismatch in {$methodName}().";
                if ($stopOnFirstError) return new ClassContractValidatorReport(false, $className, $interfaceName, $errors);
                continue;
            }

            // Parameter name & type check
            foreach ($ifaceMethod->getParameters() as $i => $ifaceParam) {
                $classParam = $classMethod->getParameters()[$i];

                if ($ifaceParam->getName() !== $classParam->getName()) {
                    $errors[] = "Parameter name mismatch in {$methodName}(): expected \${$ifaceParam->getName()}, got \${$classParam->getName()}";
                    if ($stopOnFirstError) return new ClassContractValidatorReport(false, $className, $interfaceName, $errors);
                }

                if (!self::compareTypes($ifaceParam->getType(), $classParam->getType())) {
                    $errors[] = "Parameter type mismatch in {$methodName}(): expected " . self::typeToString($ifaceParam->getType()) . ", got " . self::typeToString($classParam->getType());
                    if ($stopOnFirstError) return new ClassContractValidatorReport(false, $className, $interfaceName, $errors);
                }
            }

            // Return type check
            if (!self::compareTypes($ifaceMethod->getReturnType(), $classMethod->getReturnType())) {
                $errors[] = "Return type mismatch in {$methodName}(): expected " . self::typeToString($ifaceMethod->getReturnType()) . ", got " . self::typeToString($classMethod->getReturnType());
                if ($stopOnFirstError) return new ClassContractValidatorReport(false, $className, $interfaceName, $errors);
            }
        }

        return new ClassContractValidatorReport(empty($errors), $className, $interfaceName, $errors);
    }

    /**
     * Helper to format a detailed error message.
     */
    protected static function formatErrorMessage(ClassContractValidatorReport $report): string
    {
        return "Contract validation failed for class [{$report->className}] against interface [{$report->interfaceName}]:\n" .
            implode("\n", $report->errors) .
            "\nFix the above method mismatches to comply with the interface.";
    }

    /**
     * Resolve class/interface name from string|object|Closure.
     */
    protected static function resolveName(string|object $input): string
    {
        if ($input instanceof Closure) {
            $input = $input();
        }

        if (is_object($input)) {
            return get_class($input);
        }

        if (is_string($input)) {
            return ltrim($input, '\\');
        }

        throw new InvalidArgumentException("Invalid class/interface input.");
    }

    /**
     * Compare two types for compatibility.
     */
    protected static function compareTypes($typeA, $typeB): bool
    {
        if ($typeA === null && $typeB === null) return true;
        if ($typeA === null || $typeB === null) return false;

        return self::typeToString($typeA) === self::typeToString($typeB);
    }

    /**
     * Convert type to string for comparison.
     */
    protected static function typeToString($type): string
    {
        if ($type instanceof ReflectionNamedType) {
            return ($type->allowsNull() ? '?' : '') . $type->getName();
        }
        if ($type instanceof ReflectionUnionType) {
            return implode('|', array_map(fn($t) => self::typeToString($t), $type->getTypes()));
        }
        if ($type instanceof ReflectionIntersectionType) {
            return implode('&', array_map(fn($t) => self::typeToString($t), $type->getTypes()));
        }
        return '';
    }
}


