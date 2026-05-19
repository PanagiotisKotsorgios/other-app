<?php
// E:\call_center\app\Controllers\DeveloperController.php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Project, Commission, User, Message};

class DeveloperController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireDeveloper();

        $projModel = new Project();
        $commModel = new Commission();

        $stats     = $projModel->developerStats(Auth::id());
        $projects  = $projModel->forDeveloper(Auth::id(), [], 1, 5);
        $upcoming  = $projModel->upcomingDeadlines(14);
        $commData  = $commModel->forDeveloper(Auth::id(), 1, 5);
        $unread    = (new Message())->unreadCount(Auth::id());

        // Filter upcoming deadlines to only this developer's projects
        $myUpcoming = array_filter($upcoming, fn($p) => $p['developer_id'] == Auth::id());

        $this->view('developer.dashboard', [
            'title'      => 'Developer Dashboard',
            'stats'      => $stats,
            'projects'   => $projects['data'],
            'upcoming'   => array_values($myUpcoming),
            'commData'   => $commData['data'],
            'unread'     => $unread,
        ]);
    }

    public function projects(): void
    {
        // Delegate to ProjectController
        (new ProjectController())->developerIndex();
    }

    public function show(string $id): void
    {
        // Delegate to ProjectController
        (new ProjectController())->developerShow($id);
    }

    public function commissions(): void
    {
        Auth::requireDeveloper();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $model  = new Commission();
        $result = $model->forDeveloper(Auth::id(), $page, 20);
        $unread = (new Message())->unreadCount(Auth::id());

        $this->view('developer.commissions', $result + [
            'title'  => 'My Commissions',
            'unread' => $unread,
        ]);
    }
}
