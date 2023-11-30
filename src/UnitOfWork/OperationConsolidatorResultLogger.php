<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use Psr\Log\LoggerInterface;
use function basename;
use function implode;
use function sprintf;
use function str_replace;


/**
 * @final
 */
class OperationConsolidatorResultLogger
{
    private const LOG_MESSAGE_OPERATIONS_SEPARATOR = ', ';


    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }


    public function log(OperationConsolidationResult $operationConsolidationResult): void
    {
        $initialOperationsState = [];
        $initialOperations = $operationConsolidationResult->getInitialOperations();
        foreach ($initialOperations as $key => $operation) {
            $initialOperationsState[] = sprintf('(%s) %s', $key, $this->getClassNameBase($operation::class));
        }

        $logMessageParts = [];
        $consolidatedOperationsState = $operationConsolidationResult->getConsolidatedOperationsState();
        foreach ($consolidatedOperationsState as $initialOperationIndex => $consolidatedOperationsStateItem) {
            $operationClassNameBase = $this->getClassNameBase($initialOperations[$initialOperationIndex]::class);
            $logMessageParts[] = sprintf(
                '(%s) %s',
                implode(self::LOG_MESSAGE_OPERATIONS_SEPARATOR, array_values($consolidatedOperationsStateItem)),
                $operationClassNameBase,
            );
        }
        $logMessage = sprintf(
            'UoW Operations [%s] got merged into [%s]',
            implode(self::LOG_MESSAGE_OPERATIONS_SEPARATOR, $initialOperationsState),
            implode(self::LOG_MESSAGE_OPERATIONS_SEPARATOR, $logMessageParts),
        );

        $this->logger->debug($logMessage);
    }


    private function getClassNameBase(string $className): string
    {
        return basename(str_replace('\\', '/', $className));
    }
}
