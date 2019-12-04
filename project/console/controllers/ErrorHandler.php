<?php
namespace console\controllers;

use common\service\exception\ExceptionService;
use yii\base\ErrorException;
use yii\base\UserException;
use yii\console\Exception;
use yii\console\UnknownCommandException;
use yii\helpers\Console;

class ErrorHandler extends \yii\console\ErrorHandler
{
    public function renderException($exception)
    {
        if ($exception instanceof UnknownCommandException) {
            // display message and suggest alternatives in case of unknown command
            $message = $this->formatMessage($exception->getName() . ': ') . $exception->command;
            $alternatives = $exception->getSuggestedAlternatives();
            if (count($alternatives) === 1) {
                $message .= "\n\nDid you mean \"" . reset($alternatives) . '"?';
            } elseif (count($alternatives) > 1) {
                $message .= "\n\nDid you mean one of these?\n    - " . implode("\n    - ", $alternatives);
            }
        } elseif ($exception instanceof Exception && ($exception instanceof UserException || !YII_DEBUG)) {
            $message = $this->formatMessage($exception->getName() . ': ') . $exception->getMessage();
        } elseif (YII_DEBUG) {
            if ($exception instanceof Exception) {
                $message = $this->formatMessage("Exception ({$exception->getName()})");
            } elseif ($exception instanceof ErrorException) {
                $message = $this->formatMessage($exception->getName());
            } else {
                $message = $this->formatMessage('Exception');
            }
            $message .= $this->formatMessage(" '" . get_class($exception) . "'", [Console::BOLD, Console::FG_BLUE])
                . ' with message ' . $this->formatMessage("'{$exception->getMessage()}'", [Console::BOLD]) //. "\n"
                . "\n\nin " . dirname($exception->getFile()) . DIRECTORY_SEPARATOR . $this->formatMessage(basename($exception->getFile()), [Console::BOLD])
                . ':' . $this->formatMessage($exception->getLine(), [Console::BOLD, Console::FG_YELLOW]) . "\n";
            if ($exception instanceof \yii\db\Exception && !empty($exception->errorInfo)) {
                $message .= "\n" . $this->formatMessage("Error Info:\n", [Console::BOLD]) . print_r($exception->errorInfo, true);
            }
            $message .= "\n" . $this->formatMessage("Stack trace:\n", [Console::BOLD]) . $exception->getTraceAsString();
        } else {
            $message = $this->formatMessage('Error: ') . $exception->getMessage();
        }

        //异常日志入库
        ExceptionService::instance()->recordException($exception);

        if (PHP_SAPI === 'cli') {
            Console::stderr($message . "\n");
        } else {
            echo $message . "\n";
        }
    }
}