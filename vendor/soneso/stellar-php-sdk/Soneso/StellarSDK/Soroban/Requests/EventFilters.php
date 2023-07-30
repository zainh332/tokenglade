<?php declare(strict_types=1);

// Copyright 2023 The Stellar PHP SDK Authors. All rights reserved.
// Use of this source code is governed by a license that can be
// found in the LICENSE file.


namespace Soneso\StellarSDK\Soroban\Requests;


class EventFilters extends \IteratorIterator
{

    public function __construct(EventFilter ...$responses)
    {
        parent::__construct(new \ArrayIterator($responses));
    }

    public function current(): EventFilter
    {
        return parent::current();
    }

    public function add(EventFilter $response)
    {
        $this->getInnerIterator()->append($response);
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