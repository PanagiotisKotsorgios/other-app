<?php
if (!function_exists('grStatus')) {
    function grStatus(string $s): string {
        return match($s) {
            'new'            => 'Νέο',
            'contacted'      => 'Επικοινωνία',
            'interested'     => 'Ενδιαφέρον',
            'not_interested' => 'Δεν Ενδιαφέρεται',
            'deal_closed'    => 'Συμφωνία Έκλεισε',
            'follow_up'      => 'Επακόλουθη',
            'pending'        => 'Εκκρεμής',
            'approved'       => 'Εγκεκριμένη',
            'rejected'       => 'Απορριφθείσα',
            'in_progress'    => 'Σε Εξέλιξη',
            'completed'      => 'Ολοκληρωμένη',
            'awaiting_assignment' => 'Αναμονή Ανάθεσης',
            'testing'        => 'Δοκιμές',
            'on_hold'        => 'Σε Αναμονή',
            'skipped'        => 'Παραλείφθηκε',
            'draft'          => 'Πρόχειρο',
            'issued'         => 'Εκδόθηκε',
            'sent'           => 'Στάλθηκε',
            'paid'           => 'Πληρωμένο',
            default          => ucfirst(str_replace('_', ' ', $s)),
        };
    }
}
if (!function_exists('grPriority')) {
    function grPriority(string $p): string {
        return match($p) {
            'low'    => 'Χαμηλή',
            'medium' => 'Μέτρια',
            'high'   => 'Υψηλή',
            'urgent' => 'Επείγουσα',
            default  => ucfirst($p),
        };
    }
}
if (!function_exists('grRole')) {
    function grRole(string $r): string {
        return match($r) {
            'admin'     => 'Διαχειριστής',
            'caller'    => 'Τηλεφωνητής',
            'developer' => 'Προγραμματιστής',
            'partner'   => 'Συνεργάτης',
            default     => ucfirst($r),
        };
    }
}
if (!function_exists('grIntType')) {
    function grIntType(string $t): string {
        return match($t) {
            'call'      => 'Τηλεφωνική Κλήση',
            'email'     => 'Email',
            'offer'     => 'Προσφορά',
            'demo'      => 'Επίδειξη',
            'follow_up' => 'Επακόλουθη',
            'messenger' => 'Messenger',
            'whatsapp'  => 'WhatsApp',
            'reminder'  => 'Υπενθύμιση',
            default     => ucfirst(str_replace('_', ' ', $t)),
        };
    }
}
if (!function_exists('grIntResult')) {
    function grIntResult(string $r): string {
        return match($r) {
            'no_answer'      => 'Δεν Απάντησε',
            'callback'       => 'Ζήτησε Επανάκληση',
            'interested'     => 'Ενδιαφέρον',
            'not_interested' => 'Δεν Ενδιαφέρεται',
            'left_message'   => 'Άφησε Μήνυμα',
            'sent'           => 'Στάλθηκε',
            'completed'      => 'Ολοκληρώθηκε',
            default          => ucfirst(str_replace('_', ' ', $r)),
        };
    }
}
if (!function_exists('grExpCat')) {
    function grExpCat(string $c): string {
        return match($c) {
            'hosting'       => 'Φιλοξενία',
            'software'      => 'Λογισμικό',
            'hardware'      => 'Εξοπλισμός',
            'subcontractor' => 'Υπεργολάβος',
            'marketing'     => 'Μάρκετινγκ',
            'salary'        => 'Μισθός',
            'tax'           => 'Φόρος',
            'other'         => 'Άλλο',
            default         => ucfirst($c),
        };
    }
}
if (!function_exists('grCategory')) {
    function grCategory(string $name): string {
        return match(strtoupper($name)) {
            'A' => 'Κατηγορία Α',
            'B' => 'Κατηγορία Β',
            'C' => 'Κατηγορία Γ',
            'D' => 'Κατηγορία Δ',
            default => 'Κατηγορία ' . strtoupper($name),
        };
    }
}
if (!function_exists('grRoleType')) {
    function grRoleType(string $r): string {
        return match($r) {
            'caller'    => 'Τηλεφωνητής',
            'developer' => 'Ανάπτυξη',
            'partner'   => 'Παραπομπή',
            default     => ucfirst($r),
        };
    }
}
