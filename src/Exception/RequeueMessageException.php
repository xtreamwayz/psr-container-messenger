<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Exception;

use LogicException;

class RequeueMessageException extends LogicException implements ExceptionInterface
{
}
