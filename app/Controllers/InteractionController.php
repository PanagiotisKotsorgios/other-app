<?php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Interaction, Business, Service};

class InteractionController extends Controller
{
    public function store(): void
    {
        Auth::requireCaller();
        CSRF::check();

        $businessId = (int)($_POST['business_id'] ?? 0);
        $business   = (new Business())->find($businessId);

        if (!$business) {
            $this->json(['success' => false, 'message' => 'Business not found.'], 404);
            return;
        }

        $data = [
            'business_id'  => $businessId,
            'caller_id'    => Auth::id(),
            'type'         => $_POST['type'] ?? 'call',
            'result'       => $_POST['result'] ?? null,
            'notes'        => trim($_POST['notes'] ?? ''),
            'duration_min' => !empty($_POST['duration_min']) ? (int)$_POST['duration_min'] : null,
            'scheduled_at' => !empty($_POST['scheduled_at']) ? $_POST['scheduled_at'] : null,
        ];

        // Handle proposal upload
        if (!empty($_FILES['proposal_file']['name'])) {
            $file = $_FILES['proposal_file'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
                $this->json(['success' => false, 'message' => 'Only PDF/DOC/DOCX allowed.'], 422);
                return;
            }
            if ($file['size'] > UPLOAD_MAX_SIZE) {
                $this->json(['success' => false, 'message' => 'File too large.'], 422);
                return;
            }
            $filename = uniqid('prop_') . '.' . $ext;
            move_uploaded_file($file['tmp_name'], UPLOAD_PATH . '/proposals/' . $filename);
            $data['proposal_file'] = $filename;
        }

        $serviceIds = array_map('intval', (array)($_POST['services'] ?? []));

        $model = new Interaction();
        $id    = $model->createWithServices($data, $serviceIds);

        // Update business status
        if (!empty($_POST['business_status'])) {
            (new Business())->update($businessId, ['status' => $_POST['business_status']]);
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'id' => $id, 'message' => 'Interaction logged.']);
        } else {
            Session::flash('success', 'Interaction logged.');
            $this->redirect(APP_URL . '/caller/businesses/' . $businessId);
        }
    }

    public function delete(string $id): void
    {
        Auth::requireCaller();
        CSRF::check();

        $model       = new Interaction();
        $interaction = $model->find((int)$id);

        if (!$interaction || ($interaction['caller_id'] != Auth::id() && !Auth::isAdmin())) {
            $this->json(['success' => false, 'message' => 'Forbidden.'], 403);
            return;
        }

        $model->delete((int)$id);

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        } else {
            Session::flash('success', 'Interaction deleted.');
            $this->back();
        }
    }

    private function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }
}
