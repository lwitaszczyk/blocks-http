<?php

namespace Blocks\Http\Flow;

use Blocks\Http\Exception\RouteMatcherException;
use Blocks\Http\Request;
use Blocks\Http\Route;
use Blocks\Http\RouteMatcher;

class DefaultRouteMatcher implements RouteMatcher
{
    const VARIABLE_REGEX = <<<'REGEX'
\{
    \s* ([a-zA-Z][a-zA-Z0-9_-]*) \s*
    (?:
        : \s* ([^{}]*(?:\{(?-1)\}[^{}]*)*)
    )?
\}
REGEX;

    const DEFAULT_DISPATCH_REGEX = '[^/]+';

    /**
     * @inheritdoc
     */
    public function matchRequestParameters(Route $route, Request $request, $exact)
    {
        $exact = (bool)$exact;

        $pattern = rtrim($route->getAbsolutePattern(), '/');
        $path = rtrim($request->getPath(), '/');

        $segments = $this->parse($pattern);
        foreach ($segments as $segment) {
            $regex = $this->buildRegexForRoute($segment);
            $regex = $exact ? "~^$regex$~" : "~^$regex~";

            $matches = [];
            if ((bool)preg_match($regex, $path, $matches)) {
                foreach ($segment as $item) {
                    if (is_array($item)) {
                        $name = $item[0];
                        $request->setParameter($name, $matches[$name]);
                    }
                }
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function generateUrl(Route $route, array $params = [])
    {
        $variants = $this->parse(
            $route->getAbsolutePattern()
        );

        foreach ($variants as $routeVariant) {
            $paramsCount = 0;
            foreach ($routeVariant as $segment) {
                if (is_array($segment)) {
                    $paramsCount++;
                }
            }

            if ($paramsCount === count($params)) {
                return $this->generateUrlFromSegments($route, $params, $routeVariant);
            }
        }

        throw new RouteMatcherException(
            sprintf(
                'Can not generate url for action %s', $route->getName()
            )
        );
    }

    /**
     * @param string $pattern
     * @return mixed[]
     * @throws RouteMatcherException
     */
    private function parse($pattern)
    {
        $routeWithoutClosingOptionals = rtrim($pattern, ']');
        $numOptionals = strlen($pattern) - strlen($routeWithoutClosingOptionals);
        // Split on [ while skipping placeholders
        $segments = preg_split('~' . self::VARIABLE_REGEX . '(*SKIP)(*F) | \[~x', $routeWithoutClosingOptionals);
        if ($numOptionals !== count($segments) - 1) {
            // If there are any ] in the middle of the route, throw a more specific error message
            if (preg_match('~' . self::VARIABLE_REGEX . '(*SKIP)(*F) | \]~x', $routeWithoutClosingOptionals)) {
                throw new RouteMatcherException("Optional segments can only occur at the end of a route");
            }
            throw new RouteMatcherException("Number of opening '[' and closing ']' does not match");
        }
        $currentRoute = '';
        $routeSegments = [];
        foreach ($segments as $n => $segment) {
            if ($segment === '' && $n !== 0) {
                throw new RouteMatcherException("Empty optional part");
            }
            $currentRoute .= $segment;
            $routeSegments[] = $this->parsePlaceholders($currentRoute);
        }
        return $routeSegments;
    }

    /**
     * @param string $route
     * @return array
     */
    private function parsePlaceholders($route)
    {
        if (!preg_match_all(
            '~' . self::VARIABLE_REGEX . '~x', $route, $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        )
        ) {
            return [$route];
        }
        $offset = 0;
        $routeData = [];
        foreach ($matches as $set) {
            if ($set[0][1] > $offset) {
                $routeData[] = substr($route, $offset, $set[0][1] - $offset);
            }
            $routeData[] = [
                $set[1][0],
                isset($set[2]) ? trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX
            ];
            $offset = $set[0][1] + strlen($set[0][0]);
        }
        if ($offset != strlen($route)) {
            $routeData[] = substr($route, $offset);
        }
        return $routeData;
    }

    /**
     * @param $routeData
     * @return string
     * @throws RouteMatcherException
     */
    private function buildRegexForRoute(array $routeData = [])
    {
        $regex = '';
        $variables = [];
        foreach ($routeData as $part) {
            if (is_string($part)) {
                $regex .= preg_quote($part, '~');
                continue;
            }

            list($varName, $regexPart) = $part;

            if (isset($variables[$varName])) {
                throw new RouteMatcherException(sprintf(
                    'Cannot use the same placeholder "%s" twice', $varName
                ));
            }

            if ($this->regexHasCapturingGroups($regexPart)) {
                throw new RouteMatcherException(sprintf(
                    'Regex "%s" for parameter "%s" contains a capturing group',
                    $regexPart, $varName
                ));
            }

            $variables[$varName] = $varName;
            $regex .= "(?P<$varName>($regexPart))";
        }

        return $regex;
    }

    /**
     * @param $regex
     * @return bool|int
     */
    private function regexHasCapturingGroups($regex)
    {
        if (false === strpos($regex, '(')) {
            // Needs to have at least a ( to contain a capturing group
            return false;
        }

// Semi-accurate detection for capturing groups
        return preg_match(
            '~
                (?:
                    \(\?\(
                  | \[ [^\]\\\\]* (?: \\\\ . [^\]\\\\]* )* \]
                  | \\\\ .
                ) (*SKIP)(*FAIL) |
                \(
                (?!
                    \? (?! <(?![!=]) | P< | \' )
                  | \*
                )
            ~x',
            $regex
        );
    }

    /**
     * @param Route $route
     * @param mixed[] $params
     * @param $routeVariant
     * @return string
     * @throws RouteMatcherException
     */
    private function generateUrlFromSegments(Route $route, array $params, $routeVariant)
    {
        $url = '';

        foreach ($routeVariant as $routeSegment) {
            if (is_array($routeSegment)) {
                $key = $routeSegment[0];
                if (array_key_exists($key, $params)) {
                    $url .= $params[$key];
                } else {
                    throw new RouteMatcherException(
                        sprintf(
                            'Parameter %s is required for action %s',
                            $key,
                            $route->getName()
                        )
                    );
                }
            } else {
                $url .= $routeSegment;
            }
        }

        return $url;
    }
}
