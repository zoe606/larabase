<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\FormRequest;

/**
 * Verifies that all API V1 controllers enforce authorization on every public action,
 * either via $this->authorize() in the controller method or authorize() in a Form Request.
 *
 * Exempt controllers are documented below with their security rationale.
 */
$exemptControllers = ['AuthController', 'NotificationController'];

// Resolve controller directory from project root (avoids app_path() at collection time)
$controllerPath = dirname(__DIR__, 2).'/app/Http/Controllers/Api/V1';
$controllerFiles = glob($controllerPath.'/*.php');

foreach ($controllerFiles as $file) {
    $className = pathinfo($file, PATHINFO_FILENAME);

    if (in_array($className, $exemptControllers, true)) {
        continue;
    }

    $fqcn = "App\\Http\\Controllers\\Api\\V1\\{$className}";

    it("enforces authorization on all actions in {$className}", function () use ($fqcn, $file) {
        $reflection = new ReflectionClass($fqcn);

        // Get public methods declared directly on this controller (not inherited)
        $methods = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn (ReflectionMethod $m) => $m->getDeclaringClass()->getName() === $fqcn)
            ->filter(fn (ReflectionMethod $m) => ! str_starts_with($m->getName(), '__'));

        $source = file_get_contents($file);
        $unprotectedMethods = [];

        foreach ($methods as $method) {
            $methodName = $method->getName();

            // Check 1: Controller method contains $this->authorize(
            $methodBody = extractMethodBody($source, $methodName);
            $hasControllerAuth = str_contains($methodBody, '$this->authorize(');

            // Check 2: Method has a Form Request parameter with meaningful authorize()
            $hasFormRequestAuth = false;
            foreach ($method->getParameters() as $param) {
                $type = $param->getType();
                if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                    $paramClass = $type->getName();
                    if (class_exists($paramClass) && is_subclass_of($paramClass, FormRequest::class)) {
                        $frReflection = new ReflectionClass($paramClass);
                        if ($frReflection->hasMethod('authorize')) {
                            $frSource = file_get_contents($frReflection->getFileName());
                            $frAuthBody = trim(extractMethodBody($frSource, 'authorize'));
                            // A meaningful authorize() does more than just "return true;"
                            $hasFormRequestAuth = $frAuthBody !== '' && ! preg_match('/^\s*return\s+true\s*;\s*$/m', $frAuthBody);
                        }
                    }
                }
            }

            if (! $hasControllerAuth && ! $hasFormRequestAuth) {
                $unprotectedMethods[] = $methodName;
            }
        }

        expect($unprotectedMethods)->toBeEmpty(
            "Unprotected methods in {$fqcn}: ".implode(', ', $unprotectedMethods)
            .'. Add $this->authorize() or a Form Request with authorize().'
        );
    });
}

/**
 * Extract the body of a named method from PHP source code.
 */
function extractMethodBody(string $source, string $methodName): string
{
    $pattern = '/function\s+'.preg_quote($methodName, '/').'\s*\([^)]*\)[^{]*\{/';
    if (! preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
        return '';
    }

    $startPos = $matches[0][1] + strlen($matches[0][0]);
    $braceCount = 1;
    $pos = $startPos;
    $length = strlen($source);

    while ($pos < $length && $braceCount > 0) {
        if ($source[$pos] === '{') {
            $braceCount++;
        } elseif ($source[$pos] === '}') {
            $braceCount--;
        }
        $pos++;
    }

    return substr($source, $startPos, $pos - $startPos - 1);
}
