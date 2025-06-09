<?php

namespace Kartikey\Core\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stegback\Category\Handlers\CategoryHandler;
use Kartikey\Core\Repository\BufferRepository as RepositoryBufferRepository;
use Stegback\Seller\Handlers\BrandHandler;

class ProcessBufferedData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bufferRepository;
    protected $handlers;

    public function handle(RepositoryBufferRepository $bufferRepository, CategoryHandler $categoryHandler, BrandHandler $brandHandler)
    {
        $handlers = [
            'category' => $categoryHandler,
            'brand' => $brandHandler,
        ];

        try {

            $buffers = $bufferRepository->getPendingItems();
            foreach ($buffers as $buffer) {
                $data = $buffer->data;
                $type = $buffer->type;
                foreach ($data as $cat) {
                    if (isset($handlers[$type])) {
                        $handler = $handlers[$type];
                        $handler->handle($cat);
                    }
                }

                $bufferRepository->markAsProcessed($buffer->id);
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
            die;
        }
    }
}
