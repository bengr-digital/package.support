<?php

namespace Bengr\Support\Url;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\RouteUri;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Illuminate\Support\Str;

class UrlHolder
{
    protected string $url;

    protected string $originalUrl;

    protected array $bindingFields = [];

    protected ?CompiledRoute $compiled = null;

    protected array $parameters = [];

    protected array $validators = [];

    protected object $instance;

    public function __construct(string $url)
    {
        $this->originalUrl = $url;
        $this->url = $this->parseUrl($url);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getOriginalUrl(): string
    {
        return $this->originalUrl;
    }

    public function getValidators()
    {
        if (count($this->validators)) {
            return $this->validators;
        }

        return $this->validators = [new UrlValidator];
    }

    public function getCompiled()
    {
        return $this->compiled;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getBindingFields()
    {
        return $this->bindingFields;
    }

    public function getSignatureProperties($conditions = [])
    {
        if (is_string($conditions)) {
            $conditions = ['subClass' => $conditions];
        }

        $properties = (new ReflectionClass($this->instance))->getProperties(ReflectionProperty::IS_PUBLIC);

        return match (true) {
            !empty($conditions['subClass']) => array_filter($properties, fn ($p) => $this->isPropertySubClassOf($p, $conditions['subClass'])),
            default => $properties,
        };
    }

    public function isPropertySubClassOf(ReflectionProperty $property, string $subClass)
    {
        if (!$property->hasType() || !$property->getType() instanceof ReflectionNamedType) return false;

        $propertyClassName = $property->getType()->getName();

        return $propertyClassName
            && (class_exists($propertyClassName) || interface_exists($propertyClassName))
            && (new ReflectionClass($propertyClassName))->isSubclassOf($subClass);
    }

    public function setParameter(string $name, string | object | null $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function parseUrl(string $url)
    {
        $url = trim($url, '/');
        $this->bindingFields = [];

        return tap(RouteUri::parse($url), function ($uri) {
            $this->bindingFields = $uri->bindingFields;
        })->uri;
    }

    public function matches(string $url)
    {
        $this->compileRoute();

        foreach ($this->getValidators() as $validator) {

            if (!$validator->matches($this, $url)) {
                return false;
            }
        }

        return true;
    }

    public function bind(string $url): self
    {
        $this->compileRoute();

        $path = '/' . trim($url, '/');

        preg_match($this->getCompiled()->getRegex(), $path, $matches);

        $this->parameters = $this->matchToKeys(array_slice($matches, 1));

        return $this;
    }

    protected function matchToKeys(array $matches): array
    {
        if (empty($this->getCompiled()->getVariables())) {
            return [];
        }
        $parameters = array_intersect_key($matches, array_flip($this->getCompiled()->getVariables()));

        return array_filter($parameters, function ($value) {
            return is_string($value) && strlen($value) > 0;
        });
    }

    protected function compileRoute(): CompiledRoute
    {
        if (!$this->compiled) {
            $this->compiled = $this->toSymfonyRoute()->compile();
        }

        return $this->compiled;
    }

    protected function toSymfonyRoute(): SymfonyRoute
    {
        return new SymfonyRoute(
            preg_replace('/\{(\w+?)\?\}/', '{$1}', $this->getUrl()),
            [],
            [],
            ['utf8' => true],
            '',
            [],
            []
        );
    }

    public function substituteImplicitBindings(object $instance)
    {
        $this->instance = $instance;
        $parameters = $this->getParameters();

        foreach ($this->getSignatureProperties(['subClass' => UrlRoutable::class]) as $property) {
            if (!$parameterName = static::getParameterName($property->getName(), $parameters)) {
                continue;
            }

            $parameterValue = $parameters[$parameterName];

            if ($parameterValue instanceof UrlRoutable) {
                continue;
            }

            $instance = app($property->getType()->getName());

            $parent = $this->getParentOfParameter($parameterName);
            $routeBindingMethod = in_array(SoftDeletes::class, class_uses_recursive($instance))
                ? 'resolveSoftDeletableRouteBinding'
                : 'resolveRouteBinding';


            if ($parent instanceof UrlRoutable && array_key_exists($parameterName, $this->getBindingFields())) {
                $childRouteBindingMethod = in_array(SoftDeletes::class, class_uses_recursive($instance))
                    ? 'resolveSoftDeletableChildRouteBinding'
                    : 'resolveChildRouteBinding';

                if (!$model = $parent->{$childRouteBindingMethod}(
                    $parameterName,
                    $parameterValue,
                    $this->getBindingFieldFor($parameterName)
                )) {
                    throw (new ModelNotFoundException)->setModel(get_class($instance), [$parameterValue]);
                }
            } elseif (!$model = $instance->{$routeBindingMethod}($parameterValue, $this->getBindingFieldFor($parameterName))) {
                throw (new ModelNotFoundException)->setModel(get_class($instance), [$parameterValue]);
            }
            $this->setParameter($parameterName, $model);
            $this->instance->$parameterName = $model;
        }

        return $this->instance;
    }

    public function getParentOfParameter(string $parameter): ?string
    {
        $key = array_search($parameter, array_keys($this->getParameters()));

        if ($key === 0) {
            return null;
        }

        return array_values($this->getParameters())[$key - 1];
    }

    public function getBindingFieldFor(string $parameter)
    {
        $fields = is_int($parameter) ? array_values($this->getBindingFields()) : $this->getBindingFields();

        return $fields[$parameter] ?? null;
    }

    protected function getParameterName($name, $parameters)
    {
        if (array_key_exists($name, $parameters)) {
            return $name;
        }

        if (array_key_exists($snakedName = Str::snake($name), $parameters)) {
            return $snakedName;
        }
    }
}
