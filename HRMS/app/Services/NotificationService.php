<?php

namespace App\Services;

use App\Models\NotificationModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Central fan-out point for every user-facing event in the app. Two real
 * channels (in-app + email); SMS/WhatsApp/push are placeholders per the
 * spec — they log what *would* have been sent rather than calling a real
 * gateway, since none is configured or contracted for this deployment.
 * Swapping a placeholder for a real gateway later means implementing just
 * that one private method, not touching any call site.
 *
 * Takes an explicit tenant connection rather than defaulting to service('tenantContext')->db()
 * at construction time, because service('tenantContext')->db() only resolves inside a real
 * tenant HTTP request (TenantResolver having run) — a scheduled command
 * iterating every tenant (see NotificationsDailyReminders) connects to each
 * tenant DB directly and has no TenantContext to read from.
 */
class NotificationService
{
    private BaseConnection $db;
    private NotificationModel $notifications;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db            = $db ?? service('tenantContext')->db();
        $this->notifications = new NotificationModel($this->db);
    }

    /** The in-app inbox row. $url is where clicking the notification should take the user. */
    public function inApp(int $userId, string $type, string $title, ?string $body = null, ?string $url = null, array $data = []): int
    {
        return (int) $this->notifications->insert([
            'user_id' => $userId, 'type' => $type, 'title' => $title, 'body' => $body, 'url' => $url,
            'data' => $data !== [] ? json_encode($data) : null, 'created_at' => date('Y-m-d H:i:s'),
        ], true);
    }

    /** @param array<string,mixed> $viewData */
    public function email(string $to, string $subject, string $view, array $viewData = []): bool
    {
        $email = service('email');
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage(view($view, $viewData));

        $sent = $email->send();
        if (! $sent) {
            log_message('error', 'Notification email "{subject}" to {to} failed: {trace}', [
                'subject' => $subject, 'to' => $to, 'trace' => $email->printDebugger(['headers']),
            ]);
        }

        return $sent;
    }

    public function smsPlaceholder(string $toPhone, string $message): void
    {
        log_message('info', '[SMS placeholder] would send to {phone}: {msg}', ['phone' => $toPhone, 'msg' => $message]);
    }

    public function whatsappPlaceholder(string $toPhone, string $message): void
    {
        log_message('info', '[WhatsApp placeholder] would send to {phone}: {msg}', ['phone' => $toPhone, 'msg' => $message]);
    }

    public function pushPlaceholder(int $userId, string $title, string $body): void
    {
        log_message('info', '[Push placeholder] would send to user {id}: {title} — {body}', ['id' => $userId, 'title' => $title, 'body' => $body]);
    }

    // ---- Named events -------------------------------------------------
    // In-app always fires (cheap, always available). Email fires only when
    // the recipient has a resolvable email address — most of these already
    // fire from the service that owns the underlying record, right after
    // the state change that makes them true, never speculatively.

    public function leaveApplied(int $approverUserId, string $employeeName, string $leaveType, string $fromDate, string $toDate): void
    {
        $this->inApp($approverUserId, 'leave_applied', "{$employeeName} applied for {$leaveType}", "{$fromDate} to {$toDate}", site_url('leave'));
    }

    public function leaveApproved(int $employeeUserId, string $leaveType, string $fromDate, string $toDate): void
    {
        $this->inApp($employeeUserId, 'leave_approved', 'Your leave request was approved', "{$leaveType}, {$fromDate} to {$toDate}", site_url('my-leave'));
    }

    public function leaveRejected(int $employeeUserId, string $leaveType, string $fromDate, string $toDate, ?string $remarks): void
    {
        $this->inApp($employeeUserId, 'leave_rejected', 'Your leave request was rejected', trim("{$leaveType}, {$fromDate} to {$toDate}. " . ($remarks ?? '')), site_url('my-leave'));
    }

    public function attendanceRegularizationApproved(int $employeeUserId, string $date): void
    {
        $this->inApp($employeeUserId, 'regularization_approved', 'Attendance correction approved', "Your regularization request for {$date} was approved.", site_url('my-attendance'));
    }

    public function payrollGenerated(int $hrUserId, int $processedCount, string $period): void
    {
        $this->inApp($hrUserId, 'payroll_generated', "Payroll generated for {$period}", "{$processedCount} employee(s) processed.", site_url('payroll/runs'));
    }

    public function payslipAvailable(int $employeeUserId, string $period, ?string $email = null): void
    {
        $this->inApp($employeeUserId, 'payslip_available', "Payslip available for {$period}", null, site_url('my-payroll/payslips'));

        if ($email) {
            $this->email($email, "Your {$period} payslip is available", 'emails/payslip_available', ['period' => $period]);
        }
    }

    public function attendanceMissed(int $employeeUserId, string $date): void
    {
        $this->inApp($employeeUserId, 'attendance_missed', 'Missed punch detected', "No attendance was recorded for {$date}.", site_url('my-attendance'));
    }

    public function birthdayReminder(int $hrUserId, string $employeeName, string $date): void
    {
        $this->inApp($hrUserId, 'birthday_reminder', "{$employeeName}'s birthday is coming up", $date);
    }

    public function workAnniversary(int $hrUserId, string $employeeName, int $years, string $date): void
    {
        $this->inApp($hrUserId, 'work_anniversary', "{$employeeName}'s {$years}-year work anniversary", $date);
    }

    /** @return list<int> user_ids of everyone holding this permission — used to fan a notification out to "HR"/"Admins" rather than one hardcoded user. */
    public function usersWithPermission(string $permissionSlug): array
    {
        $rows = $this->db->table('user_roles ur')
            ->select('u.id')
            ->join('users u', 'u.id = ur.user_id')
            ->join('role_permissions rp', 'rp.role_id = ur.role_id')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('p.slug', $permissionSlug)
            ->where('u.status', 'active')
            ->get()->getResultArray();

        return array_unique(array_map('intval', array_column($rows, 'id')));
    }
}
