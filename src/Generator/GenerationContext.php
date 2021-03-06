<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\Throwable\Exception\Generator\Context\CachedValueNotFound;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\CircularReferenceException;

final class GenerationContext
{
    /**
     * @var bool
     */
    private $isFirstPass;

    /**
     * @var ResolvingContext
     */
    private $resolving;

    /**
     * @var bool
     */
    private $needsCompleteResolution = false;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var bool
     */
    private $retrieveCallResult = false;

    public function __construct()
    {
        $this->isFirstPass = true;
        $this->resolving = new ResolvingContext();
    }

    public function isFirstPass(): bool
    {
        return $this->isFirstPass;
    }

    public function setToSecondPass()
    {
        $this->isFirstPass = false;
    }

    /**
     * @param string $id
     *
     * @throws CircularReferenceException
     */
    public function markIsResolvingFixture(string $id)
    {
        $this->resolving->add($id);
        $this->resolving->checkForCircularReference($id);
    }

    public function markAsNeedsCompleteGeneration()
    {
        $this->needsCompleteResolution = true;
    }

    public function unmarkAsNeedsCompleteGeneration()
    {
        $this->needsCompleteResolution = false;
    }

    public function needsCompleteGeneration(): bool
    {
        return $this->needsCompleteResolution;
    }

    public function cacheValue(string $key, $value)
    {
        $this->cache[$key] = $value;
    }

    public function markRetrieveCallResult()
    {
        $this->retrieveCallResult = true;
    }

    public function unmarkRetrieveCallResult()
    {
        $this->retrieveCallResult = false;
    }

    public function needsCallResult(): bool
    {
        return $this->retrieveCallResult;
    }

    /**
     * @param string $key
     *
     * @throws CachedValueNotFound
     *
     * @return mixed
     */
    public function getCachedValue(string $key)
    {
        if (false === array_key_exists($key, $this->cache)) {
            throw CachedValueNotFound::create($key);
        }

        return $this->cache[$key];
    }
}
