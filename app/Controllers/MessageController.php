<?php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Message, User};

class MessageController extends Controller
{
    public function inbox(): void
    {
        Auth::requireLogin();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $model  = new Message();
        $result = $model->inbox(Auth::id(), $page, 20);
        $unread = $model->unreadCount(Auth::id());

        $this->view($this->view_prefix() . 'messages.inbox', $result + [
            'title'  => 'Inbox',
            'tab'    => 'inbox',
            'unread' => $unread,
        ]);
    }

    public function sent(): void
    {
        Auth::requireLogin();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $model  = new Message();
        $result = $model->sent(Auth::id(), $page, 20);
        $unread = $model->unreadCount(Auth::id());

        $this->view($this->view_prefix() . 'messages.inbox', $result + [
            'title'  => 'Sent Messages',
            'tab'    => 'sent',
            'unread' => $unread,
        ]);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $model  = new Message();
        $thread = $model->thread((int)$id);

        if (empty($thread)) { $this->redirect(APP_URL . '/' . (Auth::isAdmin() ? 'admin' : 'caller') . '/messages'); return; }

        $first  = $thread[0];
        if ($first['receiver_id'] != Auth::id() && $first['sender_id'] != Auth::id()) {
            $this->redirect(APP_URL . '/' . (Auth::isAdmin() ? 'admin' : 'caller') . '/messages');
            return;
        }

        // Mark as read
        foreach ($thread as $msg) {
            if ($msg['receiver_id'] == Auth::id() && !$msg['is_read']) {
                $model->markRead((int)$msg['id'], Auth::id());
            }
        }

        $unread = $model->unreadCount(Auth::id());
        $this->view($this->view_prefix() . 'messages.show', [
            'title'     => 'Message: ' . $first['subject'],
            'thread'    => $thread,
            'messageId' => (int)$id,
            'unread'    => $unread,
        ]);
    }

    public function compose(): void
    {
        Auth::requireLogin();
        $toId    = (int)($_GET['to'] ?? 0);
        $model   = new User();
        $unread  = (new Message())->unreadCount(Auth::id());

        $recipients = Auth::isAdmin() ? $model->callers() :
            $model->all('name');
        $recipients = array_filter($recipients, fn($u) => $u['role'] === (Auth::isAdmin() ? 'caller' : 'admin') || $u['role'] === 'admin');

        $this->view($this->view_prefix() . 'messages.compose', [
            'title'      => 'New Message',
            'recipients' => array_values($recipients),
            'toId'       => $toId,
            'unread'     => $unread,
        ]);
    }

    public function send(): void
    {
        Auth::requireLogin();
        CSRF::check();

        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $subject    = trim($_POST['subject'] ?? '');
        $body       = trim($_POST['body'] ?? '');
        $parentId   = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        if (!$receiverId || !$subject || !$body) {
            Session::flash('error', 'All fields are required.');
            $this->redirect(APP_URL . '/' . (Auth::isAdmin() ? 'admin' : 'caller') . '/messages/compose');
            return;
        }

        (new Message())->send(Auth::id(), $receiverId, $subject, $body, $parentId);
        Session::flash('success', 'Message sent.');
        $this->redirect(APP_URL . '/' . (Auth::isAdmin() ? 'admin' : 'caller') . '/messages/sent');
    }

    public function unreadCount(): void
    {
        Auth::requireLogin();
        $count = (new Message())->unreadCount(Auth::id());
        $this->json(['count' => $count]);
    }

    private function view_prefix(): string
    {
        return Auth::isAdmin() ? 'admin.' : 'caller.';
    }
}
