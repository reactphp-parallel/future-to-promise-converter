<?php declare(strict_types=1);

namespace ReactParallel\Tests\FutureToPromiseConverter;

use parallel\Future;
use parallel\Runtime;
use React\EventLoop\Factory;
use function Safe\sleep;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use ReactParallel\FutureToPromiseConverter\FutureToPromiseConverter;

/**
 * @internal
 */
final class FutureToPromiseConverterTest extends AsyncTestCase
{
    public function testConvertSuccess(): void
    {
        $loop = Factory::create();
        $converter = new FutureToPromiseConverter($loop);
        $runtime = new Runtime(dirname(__DIR__) . '/vendor/autoload.php');

        /** @var Future $future */
        $future = $runtime->run(function (): int {
            sleep(3);

            return 3;
        });

        $loop->run();
        $three = $this->await($converter->convert($future), $loop, 3.3);

        self::assertSame(3, $three);
    }

    public function testConvertFailure(): void
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage('Rethrow exception');

        $loop = Factory::create();
        $converter = new FutureToPromiseConverter($loop);
        $runtime = new Runtime(dirname(__DIR__) . '/vendor/autoload.php');

        /** @var Future $future */
        $future = $runtime->run(function (): void {
            sleep(3);

            throw new \Exception('Rethrow exception');
        });

        $loop->run();
        $three = $this->await($converter->convert($future), $loop, 3.3);

        self::assertSame(3, $three);
    }
}
