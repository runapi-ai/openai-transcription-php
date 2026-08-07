<?php

declare(strict_types=1);

namespace RunApi\Core\Tests\Fixtures;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

final class FakeClientException extends RuntimeException implements ClientExceptionInterface
{
}
