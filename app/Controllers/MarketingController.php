<?php

namespace App\Controllers;

use App\Core\{Controller, Auth};
use App\Models\Message;

class MarketingController extends Controller
{
    public function plan(): void
    {
        Auth::requireAdmin();
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.marketing.plan', [
            'title'  => 'Σχέδιο Marketing & Απόκτησης Πελατών',
            'unread' => $unread,
        ]);
    }
}
