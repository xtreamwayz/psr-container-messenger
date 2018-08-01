<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Exception;

use LogicException;

class RejectMessageException extends LogicException implements ExceptionInterface
{
}
