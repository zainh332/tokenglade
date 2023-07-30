<?php declare(strict_types=1);

// Copyright 2021 The Stellar PHP SDK Authors. All rights reserved.
// Use of this source code is governed by a license that can be
// found in the LICENSE file.

namespace Soneso\StellarSDK\SEP\Toml;

class Currencies extends \IteratorIterator
{

    public function __construct(Currency ...$currencies)
    {
        parent::__construct(new \ArrayIterator($currencies));
    }

    public function current(): Currency
    {
        return parent::current();
    }

    public function add(Currency $currency)
    {
        $this->getInnerIterator()->append($currency);
    }

    public function count(): int
    {
        return $this->getInnerIterator()->count();
    }

    public function toArray() : array {
        $result = array();
        foreach($this as $value) {
            array_push($result, $value);
        }
        return $result;
    }
}