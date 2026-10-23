<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\PlanModel;
use App\Models\SubscriptionHistoryModel;
use App\Models\SubscriptionModel;
use App\Services\SubscriptionService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class SubscriptionsController extends BaseController
{
    public function index()
    {
        $model = (new SubscriptionModel())->withCompanyAndPlan();

        $companyId = (string) $this->request->getGet('company_id');
        $status    = (string) $this->request->getGet('status');

        if ($companyId !== '') {
            $model->where('subscriptions.company_id', $companyId);
        }
        if ($status !== '') {
            $model->where('subscriptions.status', $status);
        }

        $subscriptions = $model->orderBy('subscriptions.created_at', 'DESC')->paginate(15, 'subscriptions');

        return view('subscriptions/index', [
            'title'         => 'Subscriptions',
            'subscriptions' => $subscriptions,
            'pager'         => $model->pager,
            'companies'     => (new CompanyModel())->select('id, name')->findAll(),
            'filters'       => ['company_id' => $companyId, 'status' => $status],
        ]);
    }

    public function create()
    {
        return view('subscriptions/form', [
            'title'          => 'New subscription',
            'subscription'   => null,
            'companies'      => (new CompanyModel())->select('id, name')->findAll(),
            'plans'          => (new PlanModel())->where('is_active', 1)->findAll(),
            'preselectCompany' => (int) $this->request->getGet('company_id'),
        ]);
    }

    public function store()
    {
        $rules = [
            'company_id' => 'required|integer',
            'plan_id'    => 'required|integer',
            'starts_at'  => 'required|valid_date',
            'expires_at' => 'required|valid_date',
            'status'     => 'required|in_list[trial,active,expiring,expired,suspended,cancelled]',
        ];

        $valid  = $this->validate($rules);
        $errors = $valid ? [] : $this->validator->getErrors();
        if ($valid && ($rangeError = SubscriptionService::dateRangeError((string) $this->request->getPost('starts_at'), (string) $this->request->getPost('expires_at')))) {
            $errors['expires_at'] = $rangeError;
        }

        $override = $this->request->getPost('employee_limit_override');
        if ($valid && $override !== null && $override !== '' && (! ctype_digit((string) $override) || (int) $override < 1)) {
            $errors['employee_limit_override'] = 'Employee limit override must be a whole number of at least 1.';
        }

        if ($errors === []) {
            try {
                $id = (new SubscriptionService())->create([
                    'company_id'              => (int) $this->request->getPost('company_id'),
                    'plan_id'                 => (int) $this->request->getPost('plan_id'),
                    'starts_at'               => $this->request->getPost('starts_at'),
                    'expires_at'              => $this->request->getPost('expires_at'),
                    'employee_limit_override' => $override !== '' ? (int) $override : null,
                    'status'                  => $this->request->getPost('status'),
                    'remarks'                 => $this->request->getPost('remarks'),
                    'created_by'              => $this->currentUserId(),
                ]);
            } catch (RuntimeException $e) {
                $errors['company_id'] = $e->getMessage();
            }
        }

        if ($errors !== []) {
            return view('subscriptions/form', [
                'title'     => 'New subscription',
                'subscription' => null,
                'companies' => (new CompanyModel())->select('id, name')->findAll(),
                'plans'     => (new PlanModel())->where('is_active', 1)->findAll(),
                'preselectCompany' => (int) $this->request->getPost('company_id'),
                'errors'    => $errors,
            ]);
        }

        return redirect()->to(site_url('subscriptions/' . $id . '/history'))->with('success', 'Subscription created.');
    }

    public function edit($id)
    {
        $subscription = (new SubscriptionModel())->find($id);
        if (! $subscription) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('subscriptions/form', [
            'title'        => 'Edit subscription',
            'subscription' => $subscription,
            'companies'    => (new CompanyModel())->select('id, name')->findAll(),
            'plans'        => (new PlanModel())->findAll(),
        ]);
    }

    public function update($id)
    {
        $subscription = (new SubscriptionModel())->find($id);
        if (! $subscription) {
            throw PageNotFoundException::forPageNotFound();
        }

        $override = $this->request->getPost('employee_limit_override');

        try {
            (new SubscriptionService())->changePlan(
                (int) $id,
                (int) $this->request->getPost('plan_id'),
                $override !== '' ? (int) $override : null
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('subscriptions/' . $id . '/history'))->with('success', 'Subscription updated.');
    }

    public function extend($id)
    {
        $newExpiry = $this->request->getPost('expires_at');
        if (! $newExpiry) {
            return redirect()->back()->with('error', 'A new expiry date is required.');
        }

        try {
            (new SubscriptionService())->extend((int) $id, $newExpiry);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('subscriptions/' . $id . '/history'))->with('success', 'Subscription extended.');
    }

    public function suspend($id)
    {
        try {
            (new SubscriptionService())->changeStatus((int) $id, 'suspended', $this->request->getPost('remarks'));
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('subscriptions/' . $id . '/history'))->with('success', 'Subscription suspended.');
    }

    public function cancel($id)
    {
        try {
            (new SubscriptionService())->changeStatus((int) $id, 'cancelled', $this->request->getPost('remarks'));
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('subscriptions/' . $id . '/history'))->with('success', 'Subscription cancelled.');
    }

    public function history($id)
    {
        $subscription = (new SubscriptionModel())->withCompanyAndPlan()->find($id);
        if (! $subscription) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('subscriptions/history', [
            'title'        => 'Subscription history',
            'subscription' => $subscription,
            'history'      => (new SubscriptionHistoryModel())->forSubscription((int) $id),
        ]);
    }
}
