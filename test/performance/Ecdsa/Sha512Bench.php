<?php
declare(strict_types=1);

namespace Lcobucci\JWT\Ecdsa;

use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Ecdsa\Sha512;

final class Sha512Bench extends EcdsaBench
{
    protected function signer(): Signer
    {
        return Sha512::create();
    }
}
