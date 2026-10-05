<?php

namespace App\Controllers;

use App\Models\CompanyDatabaseConnectionModel;
use App\Models\CompanyDomainModel;
use App\Models\CompanyModel;
use App\Models\CompanyModuleOverrideModel;
use App\Models\LicenseModel;
use App\Models\ModuleModel;
use App\Models\PlanModel;
use App\Models\ProvisioningLogModel;
use App\Services\AuditService;
use App\Services\CompanyProvisioningService;
use App\Services\CompanyService;
use App\Services\LicenseService;
use App\Services\TenantConnectionFactory;
use App\Services\TenantSettingsService;
use RuntimeException;
use Throwable;

class CompaniesController extends BaseController
{
    public function index()
    {
        $model = (new CompanyModel())->withPlan();

        $search = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('status');
        $planId = (string) $this->request->getGet('plan_id');

        if ($search !== '') {
            $model->groupStart()
                ->like('companies.name', $search)
                ->orLike('companies.code', $search)
                ->groupEnd();
        }
        if ($status !== '') {
            $model->where('companies.status', $status);
        }
        if ($planId !== '') {
            $model->where('companies.plan_id', $planId);
        }

        $sort = in_array($this->request->getGet('sort'), ['name', 'code', 'status', 'created_at'], true)
            ? $this->request->getGet('sort') : 'created_at';
        $dir = strtolower((string) $this->request->getGet('dir')) === 'asc' ? 'ASC' : 'DESC';

        $companies = $model->orderBy("companies.{$sort}", $dir)->paginate(15, 'companies');

        return view('companies/index', [
            'title'     => 'Companies',
            'companies' => $companies,
            'pager'     => $model->pager,
            'plans'     => (new PlanModel())->findAll(),
            'filters'   => ['q' => $search, 'status' => $status, 'plan_id' => $planId, 'sort' => $sort, 'dir' => strtolower($dir)],
        ]);
    }

    public function create()
    {
        return view('companies/form', [
            'title'   => 'Add company',
            'plans'   => (new PlanModel())->where('is_active', 1)->findAll(),
            'company' => null,
        ]);
    }

    public function store()
    {
        $rules = [
            'name'           => 'required|min_length[2]|max_length[150]',
            'code'           => 'required|alpha_numeric|max_length[21]|is_unique[companies.code]',
            'subdomain'      => 'required|alpha_dash|max_length[63]',
            'plan_id'        => 'required|integer',
            'status'         => 'required|in_list[trial,active]',
            'employee_limit' => 'required|integer|greater_than[0]',
            'starts_at'      => 'required|valid_date[Y-m-d]',
            'expires_at'     => 'required|valid_date[Y-m-d]',
            'admin_name'     => 'required|min_length[2]|max_length[150]',
            'admin_email'    => 'required|valid_email',
            'contact_email'  => 'permit_empty|valid_email',
        ];

        if (! $this->validate($rules)) {
            return view('companies/form', [
                'title'   => 'Add company',
                'plans'   => (new PlanModel())->where('is_active', 1)->findAll(),
                'company' => null,
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $input = [
            'name'           => $this->request->getPost('name'),
            'code'           => $this->request->getPost('code'),
            'subdomain'      => $this->request->getPost('subdomain'),
            'plan_id'        => (int) $this->request->getPost('plan_id'),
            'status'         => $this->request->getPost('status'),
            'employee_limit' => (int) $this->request->getPost('employee_limit'),
            'starts_at'      => $this->request->getPost('starts_at'),
            'expires_at'     => $this->request->getPost('expires_at'),
            'timezone'       => $this->request->getPost('timezone') ?: 'Asia/Kolkata',
            'currency'       => $this->request->getPost('currency') ?: 'INR',
            'contact_name'   => $this->request->getPost('contact_name'),
            'contact_email'  => $this->request->getPost('contact_email'),
            'contact_phone'  => $this->request->getPost('contact_phone'),
            'country'        => $this->request->getPost('country'),
            'admin_name'     => $this->request->getPost('admin_name'),
            'admin_email'    => $this->request->getPost('admin_email'),
        ];

        try {
            $companyId = (new CompanyProvisioningService())->createCompany($input, (int) $this->currentUserId());
        } catch (RuntimeException $e) {
            return view('companies/form', [
                'title'   => 'Add company',
                'plans'   => (new PlanModel())->where('is_active', 1)->findAll(),
                'company' => null,
                'errors'  => ['subdomain' => $e->getMessage()],
                'old'     => $input,
            ]);
        }

        return redirect()->to(site_url('companies/' . $companyId . '/provisioning'))->with('success', 'Company created. Create its database manually, then enter and test the connection to continue.');
    }

    public function view($id)
    {
        $company = (new CompanyModel())->withPlan()->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $domainModel = new CompanyDomainModel();

        return view('companies/view', [
            'title'   => $company['name'],
            'company' => $company,
            'domain'  => $domainModel->where('company_id', $id)->where('is_primary', 1)->first(),
            'license' => (new LicenseModel())->currentFor((int) $id),
        ]);
    }

    public function renewLicense($id)
    {
        $months = max(1, (int) $this->request->getPost('months'));

        try {
            $current = (new LicenseModel())->currentFor((int) $id);
            $base    = $current && strtotime($current['expires_at']) > time() ? $current['expires_at'] : date('Y-m-d H:i:s');
            $newExpiresAt = date('Y-m-d H:i:s', strtotime("+{$months} months", strtotime($base)));

            (new LicenseService())->renew((int) $id, $newExpiresAt, (int) session('platform_user_id'));
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('companies/' . $id))->with('success', 'License renewed.');
    }

    public function revokeLicense($id)
    {
        $reason = trim((string) $this->request->getPost('reason'));
        if ($reason === '') {
            return redirect()->back()->with('error', 'A reason is required to revoke a license.');
        }

        try {
            $license = (new LicenseModel())->currentFor((int) $id);
            if (! $license) {
                throw new RuntimeException('This company has no license to revoke.');
            }
            (new LicenseService())->revoke((int) $license['id'], (int) session('platform_user_id'), $reason);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('companies/' . $id))->with('success', 'License revoked.');
    }

    public function edit($id)
    {
        $company = (new CompanyModel())->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('companies/form', [
            'title'   => 'Edit company',
            'plans'   => (new PlanModel())->findAll(),
            'company' => $company,
        ]);
    }

    public function update($id)
    {
        $company = (new CompanyModel())->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'name'          => 'required|min_length[2]|max_length[150]',
            'plan_id'       => 'required|integer',
            'employee_limit'=> 'required|integer|greater_than[0]',
            'contact_email' => 'permit_empty|valid_email',
        ];

        if (! $this->validate($rules)) {
            return view('companies/form', [
                'title'   => 'Edit company',
                'plans'   => (new PlanModel())->findAll(),
                'company' => $company,
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        (new CompanyService())->update((int) $id, [
            'name'           => $this->request->getPost('name'),
            'plan_id'        => (int) $this->request->getPost('plan_id'),
            'employee_limit' => (int) $this->request->getPost('employee_limit'),
            'timezone'       => $this->request->getPost('timezone'),
            'currency'       => $this->request->getPost('currency'),
            'contact_name'   => $this->request->getPost('contact_name'),
            'contact_email'  => $this->request->getPost('contact_email'),
            'contact_phone'  => $this->request->getPost('contact_phone'),
            'country'        => $this->request->getPost('country'),
        ]);

        return redirect()->to(site_url('companies/' . $id))->with('success', 'Company updated.');
    }

    public function activate($id)
    {
        (new CompanyService())->setStatus((int) $id, 'active');

        return redirect()->back()->with('success', 'Company activated.');
    }

    public function suspend($id)
    {
        (new CompanyService())->setStatus((int) $id, 'suspended');

        return redirect()->back()->with('success', 'Company suspended.');
    }

    public function archive($id)
    {
        (new CompanyService())->archive((int) $id);

        return redirect()->to(site_url('companies'))->with('success', 'Company archived.');
    }

    public function modules($id)
    {
        $company = (new CompanyModel())->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $moduleModel = new ModuleModel();
        $planModel   = new PlanModel();

        return view('companies/modules', [
            'title'          => 'Modules — ' . $company['name'],
            'company'        => $company,
            'modules'        => $moduleModel->orderBy('name')->findAll(),
            'planModuleIds'  => $planModel->moduleIds($company['plan_id']),
            'overrides'      => (new CompanyModuleOverrideModel())->forCompany((int) $id),
        ]);
    }

    public function updateModules($id)
    {
        $company = (new CompanyModel())->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $moduleModel = new ModuleModel();
        $overrideModel = new CompanyModuleOverrideModel();
        $planModuleIds = (new PlanModel())->moduleIds($company['plan_id']);

        foreach ($moduleModel->findAll() as $module) {
            $moduleId = (int) $module['id'];
            $submitted = $this->request->getPost('module_' . $moduleId); // 'on' | null
            $planDefault = in_array($moduleId, $planModuleIds, true);
            $requestedEnabled = $submitted === 'on';

            // Only store a row when the choice differs from the plan default —
            // an override that matches the default is not an override.
            $overrideModel->setOverride(
                (int) $id,
                $moduleId,
                $requestedEnabled === $planDefault ? null : $requestedEnabled
            );
        }

        return redirect()->to(site_url('companies/' . $id . '/modules'))->with('success', 'Module access updated.');
    }

    /**
     * Per-company TENANT settings (the company_settings row inside that company's
     * own database). Distinct from the platform `companies` record edited by edit().
     */
    public function settings($id)
    {
        $company = (new CompanyModel())->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        try {
            $loaded = (new TenantSettingsService())->load((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('companies/' . $id))->with('error', $e->getMessage());
        } catch (Throwable) {
            return redirect()->to(site_url('companies/' . $id))->with('error', 'Could not load this company\'s settings.');
        }

        return view('companies/settings', [
            'title'    => 'Company Settings — ' . $company['name'],
            'company'  => $company,
            'settings' => $loaded['settings'],
            'missing'  => $loaded['missing'],
            'errors'   => session()->getFlashdata('settings_errors') ?? [],
        ]);
    }

    public function updateSettings($id)
    {
        $company = (new CompanyModel())->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        try {
            $result = (new TenantSettingsService())->update((int) $id, $this->request->getPost());
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('companies/' . $id . '/settings'))->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            log_message('error', 'Tenant settings update failed for company {id}: {msg}', ['id' => (int) $id, 'msg' => $e->getMessage()]);

            return redirect()->to(site_url('companies/' . $id . '/settings'))->withInput()->with('error', 'The settings could not be saved. Nothing was changed.');
        }

        if ($result['errors'] !== []) {
            return redirect()->to(site_url('companies/' . $id . '/settings'))->withInput()
                ->with('settings_errors', $result['errors'])
                ->with('error', 'Please correct the highlighted fields.');
        }

        $message = $result['changed'] === []
            ? 'No changes to save.'
            : 'Company settings updated. Changes take effect for ' . $company['name'] . ' on its next page load.';

        return redirect()->to(site_url('companies/' . $id . '/settings'))->with('success', $message);
    }

    /** Connect & Verify screen: the manually prepared tenant database is tested, verified read-only and saved here. */
    public function provisioning($id)
    {
        $company = (new CompanyModel())->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $conn = (new CompanyDatabaseConnectionModel())->forCompany((int) $id);

        return view('companies/provisioning', [
            'title'   => 'Provisioning — ' . $company['name'],
            'company' => $company,
            // Never includes the password; used only to prefill non-secret fields.
            'conn'    => $conn ? array_intersect_key($conn, array_flip(['db_host', 'db_port', 'db_name', 'db_username'])) : null,
            'suggestedDb' => 'hrms_' . $company['code'],
            'logs'    => (new ProvisioningLogModel())->forCompany((int) $id),
        ]);
    }

    /** "Test Connection": validates the manually supplied tenant DB credentials without saving them. */
    public function testDatabaseConnection($id)
    {
        return $this->databaseConnectionAction((int) $id, false);
    }

    /** "Connect & Verify": re-verifies read-only server-side, saves the encrypted connection and marks the company READY. */
    public function saveDatabaseConnection($id)
    {
        return $this->databaseConnectionAction((int) $id, true);
    }

    private function databaseConnectionAction(int $id, bool $save)
    {
        if (! (new CompanyModel())->find($id)) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'failed', 'message' => 'Company not found.']);
        }

        $input   = (array) ($this->request->getJSON(true) ?? []);
        $service = new CompanyProvisioningService();

        $tables = null;

        try {
            if ($save) {
                $service->saveConnection($id, $input);
            } else {
                $tables = count($service->testSuppliedConnection($id, $input)['present']);
            }
        } catch (RuntimeException $e) {
            // Messages from the service are written to be password-free.
            return $this->response->setJSON(['status' => 'failed', 'message' => $e->getMessage(), 'csrf' => csrf_hash()]);
        } catch (Throwable) {
            return $this->response->setJSON(['status' => 'failed', 'message' => 'Unexpected error while testing the connection.', 'csrf' => csrf_hash()]);
        }

        return $this->response->setJSON(['status' => 'ok', 'message' => $save ? 'Database connection verified. Tenant database is ready.' : "Database connection verified. Tenant schema, company settings and RBAC verified ({$tables} required tables present).", 'csrf' => csrf_hash()]);
    }

    /** Polled by the provisioning page's JS to show the verification steps. */
    public function provisioningStatus($id)
    {
        $company = (new CompanyModel())->find($id);
        if (! $company) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'failed', 'message' => 'Company not found.']);
        }

        return $this->response->setJSON([
            'provisioning_status' => $company['provisioning_status'],
            'provisioning_error'  => $company['provisioning_error'],
            'db_configured'       => (new CompanyProvisioningService())->hasVerifiedConnection((int) $id),
            'logs'               => (new ProvisioningLogModel())->forCompany((int) $id),
        ]);
    }

    /** Section 47: safe database health info only — never credentials. */
    public function database($id)
    {
        $company = (new CompanyModel())->find($id);
        if (! $company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $conn = (new CompanyDatabaseConnectionModel())->forCompany((int) $id);
        $healthy = false;

        if ($conn && $conn['status'] === 'provisioned') {
            try {
                $password = service('encrypter')->decrypt(base64_decode((string) $conn['db_password_enc']));
                $factory  = new TenantConnectionFactory();
                $db       = $factory->build($conn['db_host'], (int) $conn['db_port'], $conn['db_name'], $conn['db_username'], $password);
                $healthy  = $factory->healthCheck($db);
            } catch (Throwable) {
                $healthy = false;
            }
        }

        return view('companies/database', [
            'title'   => 'Database — ' . $company['name'],
            'company' => $company,
            'conn'    => $conn,
            'healthy' => $healthy,
        ]);
    }
}
