<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyDomainModel extends Model
{
    protected $table         = 'company_domains';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['company_id', 'domain', 'is_primary', 'verified_at'];

    /** Subdomains that can never be assigned to a tenant company. */
    public const RESERVED = ['admin', 'www', 'api', 'app', 'mail', 'ftp', 'ns1', 'ns2', 'static', 'cdn'];

    protected $validationRules = [
        'domain' => 'required|max_length[191]|is_unique[company_domains.domain,id,{id}]',
    ];

    public function isReserved(string $subdomain): bool
    {
        return in_array(strtolower($subdomain), self::RESERVED, true);
    }
}
