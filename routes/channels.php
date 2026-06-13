<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('scans.{scanId}', function ($user, int $scanId) {
    return $user->scans()->whereKey($scanId)->exists();
});
