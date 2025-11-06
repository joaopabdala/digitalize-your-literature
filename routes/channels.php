<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('digitalization-status.{id}', function ($digitalizationBatch, $id) {
    return (int)$digitalizationBatch->id === (int)$id;
});
