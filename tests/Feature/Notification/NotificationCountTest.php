<?php

use App\Events\NotificationCountUpdated;
use App\Models\User;
use App\Notifications\NewFollowNotification;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
});

it('returns the current unread notifications count', function () {
    $this->user2->notify(new NewFollowNotification($this->user1, $this->user2));

    $response = $this->actingAs($this->user2, 'sanctum')->getJson(route('notifications.unreadCount'));

    $response->assertStatus(200);
    $response->assertJson([
        'data' => ['unread_count' => 1],
    ]);
});

it('broadcasts the updated unread count when a notification is sent', function () {
    Event::fakeExcept([NotificationSent::class]);

    $this->user2->notify(new NewFollowNotification($this->user1, $this->user2));

    Event::assertDispatched(NotificationCountUpdated::class, function ($event) {
        return $event->broadcastWith()['unread_count'] === 1;
    });
});

it('reflects multiple unread notifications in the broadcasted count', function () {
    Event::fakeExcept([NotificationSent::class]);

    $this->user2->notify(new NewFollowNotification($this->user1, $this->user2));
    $this->user2->notify(new NewFollowNotification($this->user1, $this->user2));

    Event::assertDispatched(NotificationCountUpdated::class, function ($event) {
        return $event->broadcastWith()['unread_count'] === 2;
    });
});
