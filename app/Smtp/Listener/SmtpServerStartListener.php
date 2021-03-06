<?php

/**
 * 用于启服务后初始化数据.
 *
 * @author wuchuheng<wuchuheng@163.com>
 */
namespace App\Smtp\Listener;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use PhpCsFixer\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use \Redis;


class SmtpServerStartListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Redis
     */
    private $Redis;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('sql');
        $this->Redis = $container->get(\Redis::class);
    }

    public function listen(): array
    {
        return [
            AfterWorkerStart::class
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $Event)
    {
        // :xxx 由于多个进程被初始化多次.实际上一次就够了
        $Redis = $this->Redis;
        $smtp_session_prefix = config('smtp_session_prefix');
        $session_keys = $Redis->keys("{$smtp_session_prefix}*");
        foreach ($session_keys as $session_key) {
            $Redis->del($session_key);
        }
    }
}
