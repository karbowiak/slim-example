<?php

namespace App\Attributes;

use Attribute;
use JetBrains\PhpStorm\ArrayShape;

#[Attribute]
class UrlAttribute
{
    public function __construct(
        protected string $route,
        #[ArrayShape([
            'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'
        ])]
        protected array $type = ['GET'],
        private array $validTypes = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH']
    ) {
        $this->type = array_map(
            function($t) {
                if (!in_array($t, $this->validTypes, true)) {
                    throw new \Exception('Error, type is not valid, needs to be one of: ' .
                        implode(', ', $this->validTypes) . ' was given: ' . $t);
                }
                return strtoupper($t);
            }, $type
        );
    }

    public function getType(): array {
        return $this->type;
    }

    public function getRoute(): string {
        return $this->route;
    }
}