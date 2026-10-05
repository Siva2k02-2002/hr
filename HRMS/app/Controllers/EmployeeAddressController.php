<?php

namespace App\Controllers;

use App\Services\EmployeeAddressService;

/**
 * One form on the Personal tab posts both address blocks at once — see
 * employees/_tab_personal.php. The "Same as Permanent" checkbox is a pure
 * client-side convenience (JS copies the field values before submit); the
 * server always just saves whatever it's given for each type.
 */
class EmployeeAddressController extends BaseController
{
    public function save($employeeId)
    {
        $service = new EmployeeAddressService();
        $post    = $this->request->getPost();

        foreach (['permanent', 'current'] as $type) {
            $block = $post[$type] ?? null;
            if (! is_array($block)) {
                continue;
            }

            $service->save((int) $employeeId, $type, [
                'country'       => empty($block['country']) ? null : $block['country'],
                'state'         => empty($block['state']) ? null : $block['state'],
                'city'          => empty($block['city']) ? null : $block['city'],
                'district'      => empty($block['district']) ? null : $block['district'],
                'pincode'       => empty($block['pincode']) ? null : $block['pincode'],
                'address_line1' => empty($block['address_line1']) ? null : $block['address_line1'],
                'address_line2' => empty($block['address_line2']) ? null : $block['address_line2'],
            ]);
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=personal'))->with('success', 'Address updated.');
    }
}
