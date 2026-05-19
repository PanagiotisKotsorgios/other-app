<?php

namespace App\Controllers;

use App\Core\{Controller, Auth};
use App\Models\{User, Business, Deal, Commission, Interaction, Message};

class CallerController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireCaller();

        $userModel = new User();
        $stats     = $userModel->callerStats(Auth::id());

        $intModel  = new Interaction();
        $daily     = $intModel->callerStatsByPeriod(Auth::id(), 'daily');
        $weekly    = $intModel->callerStatsByPeriod(Auth::id(), 'weekly');
        $monthly   = $intModel->callerStatsByPeriod(Auth::id(), 'monthly');

        $bizModel  = new Business();
        $recent    = $bizModel->assignedToCaller(Auth::id(), [], 1, 5);

        $dealModel = new Deal();
        $deals     = $dealModel->forCaller(Auth::id(), [], 1, 5);

        $actChart  = $intModel->chartData(30);
        $unread    = (new Message())->unreadCount(Auth::id());

        $this->view('caller.dashboard', [
            'title'    => 'My Dashboard',
            'stats'    => $stats,
            'daily'    => $daily,
            'weekly'   => $weekly,
            'monthly'  => $monthly,
            'recent'   => $recent['data'],
            'deals'    => $deals['data'],
            'actChart' => $actChart,
            'unread'   => $unread,
        ]);
    }
}
