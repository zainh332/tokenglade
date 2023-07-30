<?php declare(strict_types=1);

// Copyright 2021 The Stellar PHP SDK Authors. All rights reserved.
// Use of this source code is governed by a license that can be
// found in the LICENSE file.

namespace Soneso\StellarSDK\Xdr;

class XdrLiquidityPoolDepositResult
{
    private XdrLiquidityPoolDepositResultCode $resultCode;

    /**
     * @return XdrLiquidityPoolDepositResultCode
     */
    public function getResultCode(): XdrLiquidityPoolDepositResultCode
    {
        return $this->resultCode;
    }

    /**
     * @param XdrLiquidityPoolDepositResultCode $resultCode
     */
    public function setResultCode(XdrLiquidityPoolDepositResultCode $resultCode): void
    {
        $this->resultCode = $resultCode;
    }

    public function encode(): string
    {
        return $this->resultCode->encode();
    }

    public static function decode(XdrBuffer $xdr):XdrLiquidityPoolDepositResult {
        $result = new XdrLiquidityPoolDepositResult();
        $resultCode = XdrLiquidityPoolDepositResultCode::decode($xdr);
        $result->resultCode = $resultCode;
        return $result;
    }
}