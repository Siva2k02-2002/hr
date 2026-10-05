<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $session;
    protected $helpers = ['form', 'url', 'permission', 'tenant', 'employee', 'leave', 'payroll', 'icon', 'device', 'asset', 'branding'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->session = service('session');
    }

    protected function currentUserId(): ?int
    {
        return session('tenant_user_id');
    }

    protected function currentUserName(): string
    {
        return session('tenant_user_name') ?? '';
    }

    /**
     * Per-IP, per-tenant, per-action rate limit backed by CI4's Throttler (token bucket,
     * $capacity tokens refilling over $seconds). Returns true once the bucket is empty —
     * caller decides how to respond (redirect-with-error for HTML forms, 429 JSON for APIs).
     */
    protected function rateLimited(string $bucket, int $capacity, int $seconds): bool
    {
        $tenantCode = function_exists('tenant') && tenant() ? tenant()->companyCode() : 'notenant';
        $key        = $bucket . '_' . $tenantCode . '_' . md5($this->request->getIPAddress());

        return service('throttler')->check($key, $capacity, $seconds) === false;
    }

    protected function rateLimitedResponse(string $message = 'Too many requests. Please try again shortly.')
    {
        if ($this->request->isAJAX() || str_contains((string) $this->request->getHeaderLine('Accept'), 'application/json')) {
            return $this->response->setStatusCode(429)->setJSON(['status' => 'failed', 'message' => $message]);
        }

        return redirect()->back()->with('error', $message);
    }

    /**
     * The report controllers (Attendance/Leave/Payroll ReportsController) each build their
     * whole result set as a plain PHP array from a hand-rolled query per report type, shared
     * between the on-screen view and the xlsx/csv/pdf export (which needs every row, not a
     * page of them). Report screens had no pagination at all (audit finding RPT-01) — a
     * report with thousands of rows rendered the entire table in one page load. This slices
     * the already-built array for display only; export() must keep calling build() directly
     * and never pass it through here.
     *
     * @return array{0: array, 1: \CodeIgniter\Pager\Pager}
     */
    protected function paginateRows(array $rows, int $perPage = 50): array
    {
        $page   = max(1, (int) ($this->request->getGet('page') ?? 1));
        $pager  = service('pager');
        $pager->store('default', $page, $perPage, count($rows));

        return [array_slice($rows, ($page - 1) * $perPage, $perPage), $pager];
    }
}
