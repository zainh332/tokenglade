<?php declare(strict_types=1);

// Copyright 2021 The Stellar PHP SDK Authors. All rights reserved.
// Use of this source code is governed by a license that can be
// found in the LICENSE file.

namespace Soneso\StellarSDK\Xdr;

class XdrRevokeSponsorshipType
{
    const LEDGER_ENTRY = 0;
    const SIGNER = 1;

    private int $value;

    public function __construct(int $value) {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    public function encode(): string {
        return XdrEncoder::integer32($this->value);
    }

    public static function decode(XdrBuffer $xdr) : XdrRevokeSponsorshipType {
        $value = $xdr->readInteger32();
        return new XdrRevokeSponsorshipType($value);
    }
}