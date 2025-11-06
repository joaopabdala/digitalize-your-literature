<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class PageProcessedEvent implements ShouldBroadcastNow
{
    use SerializesModels;

    public int $processedCount;
    public int $totalImages;
    public int $pageId;
    public int $batchId;

    public function __construct(int $batchId, int $processedCount, int $totalImages, int $pageId)
    {
        $this->batchId = $batchId;
        $this->processedCount = $processedCount;
        $this->totalImages = $totalImages;
        $this->pageId = $pageId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('digitalization-status.' . $this->batchId);
    }

    public function broadcastAs(): string
    {
        return 'PageProcessedEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'batchId' => $this->batchId,
            'processedCount' => $this->processedCount,
            'totalImages' => $this->totalImages,
            'pageId' => $this->pageId,
            'percentage' => round(($this->processedCount / $this->totalImages) * 100, 2),
        ];
    }
}
