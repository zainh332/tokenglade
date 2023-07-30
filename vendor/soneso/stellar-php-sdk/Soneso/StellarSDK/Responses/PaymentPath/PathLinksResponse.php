<?php declare(strict_types=1);

// Copyright 2021 The Stellar PHP SDK Authors. All rights reserved.
// Use of this source code is governed by a license that can be
// found in the LICENSE file.



namespace Soneso\StellarSDK\Responses\PaymentPath;

use Soneso\StellarSDK\Responses\Link\LinkResponse;

class PathLinksResponse
{
    private LinkResponse $self;

    /**
     * @return LinkResponse
     */
    public function getSelf() : LinkResponse {
        return $this->self;
    }

    protected function loadFromJson(array $json) : void {

        if (isset($json['self'])) $this->self = LinkResponse::fromJson($json['self']);
    }

    public static function fromJson(array $json) : PathLinksResponse {
        $result = new PathLinksResponse();
        $result->loadFromJson($json);
        return $result;
    }
}