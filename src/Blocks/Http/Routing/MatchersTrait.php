<?php

namespace Blocks\Http\Routing;

use Blocks\Http\Request;

trait MatchersTrait
{

    /**
     * @var Matcher[]
     */
    private $matchers = [];

    /**
     * @param Matcher $matcher
     * @return $this
     */
    public function addMatcher(Matcher $matcher)
    {
        $this->matchers[] = $matcher;
        $matcher->setRoute($this);
        return $this;
    }

    /**
     * @param Matcher[] $matcherList
     * @return $this
     */
    public function addMatchers(array $matcherList = [])
    {
        foreach ($matcherList as $matcher) {
            $this->addMatcher($matcher);
        }
        return $this;
    }

    /**
     * @param Request $request
     * @return Matcher|null
     */
    public function match(Request $request)
    {
        foreach ($this->matchers as $matcher) {
            if (($matcher->matchToAttributes($request)) && $matcher->matchToCurrentPath($request)) {
                return $matcher;
            }
        }
        return null;
    }

    /**
     * @return \Http\Routing\Matcher[]
     */
    protected function getMatchers()
    {
        return $this->matchers;
    }
}
