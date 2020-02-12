<?php declare(strict_types=1);

namespace ReactParallel\FutureToPromiseConverter;

use parallel\Future;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Throwable;

final class FutureToPromiseConverter
{
    private LoopInterface $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function convert(Future $future): PromiseInterface
    {
        return new Promise(function (callable $resolve, callable $reject) use ($future): void {
            $timer = $this->loop->addPeriodicTimer(0.001, function () use (&$timer, $future, $resolve, $reject): void {
                if (! $future->done()) {
                    return;
                }

                if ($timer instanceof TimerInterface) {
                    $this->loop->cancelTimer($timer);
                }

                try {
                    $resolve($future->value());
                } catch (Throwable $throwable) {
                    $reject($throwable);
                }
            });
        });
    }
}
