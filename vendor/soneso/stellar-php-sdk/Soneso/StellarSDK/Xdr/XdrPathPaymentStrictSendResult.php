<?php declare(strict_types=1);

// Copyright 2021 The Stellar PHP SDK Authors. All rights reserved.
// Use of this source code is governed by a license that can be
// found in the LICENSE file.

namespace Soneso\StellarSDK\Xdr;

class XdrPathPaymentStrictSendResult
{

    private XdrPathPaymentStrictSendResultCode $code;
    private ?XdrPathPaymentResultSuccess $success = null;
    private ?XdrAsset $noIssuer = null;

    /**
     * @return XdrPathPaymentStrictSendResultCode
     */
    public function getCode(): XdrPathPaymentStrictSendResultCode
    {
        return $this->code;
    }

    /**
     * @return XdrPathPaymentResultSuccess|null
     */
    public function getSuccess(): ?XdrPathPaymentResultSuccess
    {
        return $this->success;
    }

    /**
     * @return XdrAsset|null
     */
    public function getNoIssuer(): ?XdrAsset
    {
        return $this->noIssuer;
    }

    public function encode(): string {
        $bytes = $this->code->encode();
        if ($this->success != null) {
            $bytes .= $this->success->encode();
        } else if ($this->noIssuer != null) {
            $bytes .= $this->noIssuer->encode();
        }
        return $bytes;
    }

    public static function decode(XdrBuffer $xdr) : XdrPathPaymentStrictSendResult {
        $result = new XdrPathPaymentStrictSendResult();
        $result->code = XdrPathPaymentStrictSendResultCode::decode($xdr);
        switch ($result->code->getValue()) {
            case XdrPathPaymentStrictSendResultCode::SUCCESS:
                $result->success = XdrPathPaymentResultSuccess::decode($xdr);
                break;
            case XdrPathPaymentStrictSendResultCode::NO_ISSUER:
                $result->noIssuer = XdrAsset::decode($xdr);
        }
        return $result;
    }
}