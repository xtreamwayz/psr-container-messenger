<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Exception;

use LogicException;

class RejectMessageException extends LogicException implements ExceptionInterface
{
}
