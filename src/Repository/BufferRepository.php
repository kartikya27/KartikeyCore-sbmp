<?php

namespace Kartikey\Core\Repository;

use Kartikey\Core\Eloquent\Repository;
use Kartikey\Core\Models\UnifiedBuffer;

class BufferRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return UnifiedBuffer::class;
    }

    public function getPendingItems()
    {
        return UnifiedBuffer::where("status", 0)->get();

        // return $this->model->findWhere(['status' => 0]);
    }
    
    /**
     * Get pending buffer items by type
     *
     * @param string $type The type of buffer items to retrieve
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingItemsByType(string $type)
    {
        return UnifiedBuffer::where("status", 0)
            ->where("type", $type)
            ->get();
    }

    public function markAsProcessed($ids)
    {
        $this->model->where('id', $ids)->update(['status' => 1]);
    }
}
