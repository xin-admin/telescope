<?php

namespace Xin\Telescope\Watchers;

use Illuminate\Bus\Events\BatchDispatched;
use Illuminate\Contracts\Foundation\Application;
use Xin\Telescope\IncomingEntry;
use Xin\Telescope\Telescope;

/**
 * 队列
 */
class BatchWatcher extends Watcher
{
    /**
     * Register the watcher.
     *
     * @param  Application  $app
     * @return void
     */
    public function register($app): void
    {
        $app['events']->listen(BatchDispatched::class, [$this, 'recordBatch']);
    }

    /**
     * Record a job being created.
     *
     * @return IncomingEntry|void
     */
    public function recordBatch(BatchDispatched $event)
    {
        if (! Telescope::isRecording()) {
            return;
        }

        $content = array_merge($event->batch->toArray(), [
            'queue' => $event->batch->options['queue'] ?? 'default',
            'connection' => $event->batch->options['connection'] ?? 'default',
            'allowsFailures' => $event->batch->allowsFailures(),
        ]);

        Telescope::recordBatch(
            $entry = IncomingEntry::make(
                $content,
                $event->batch->id
            )->withFamilyHash($event->batch->id)
        );

        return $entry;
    }
}
