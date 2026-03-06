<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogReport extends Model
{
    protected $table = 'log_reports';

    protected $fillable = [
        'report_date',
        'data',
    ];

    protected $casts = [
        'report_date' => 'date',
        'data' => 'json',
    ];

    public function getUrlsByRequests()
    {
        return $this->data['urls_by_requests'] ?? [];
    }

    public function getUrlsByTraffic()
    {
        return $this->data['urls_by_traffic'] ?? [];
    }

    public function getReferringDomainsByRequests()
    {
        return $this->data['referring_domains_by_requests'] ?? [];
    }

    public function getReferringDomainsByTraffic()
    {
        return $this->data['referring_domains_by_traffic'] ?? [];
    }

    public function getAsnOrganizationsByRequests()
    {
        return $this->data['asn_organizations_by_requests'] ?? [];
    }

    public function getAsnOrganizationsByTraffic()
    {
        return $this->data['asn_organizations_by_traffic'] ?? [];
    }

    public function getCountriesByRequests()
    {
        return $this->data['countries_by_requests'] ?? [];
    }

    public function getCountriesByTraffic()
    {
        return $this->data['countries_by_traffic'] ?? [];
    }

    public function getLeastCachedUrls()
    {
        return $this->data['least_cached_urls'] ?? [];
    }

    public function getHighestErrorRates()
    {
        return $this->data['highest_error_rates'] ?? [];
    }

    public function getErrorsByPop()
    {
        return $this->data['errors_by_pop'] ?? [];
    }

    public function getErrorsByCountry()
    {
        return $this->data['errors_by_country'] ?? [];
    }

    public function getHttpStatusCodesByRequests()
    {
        return $this->data['http_status_codes_by_requests'] ?? [];
    }

    public function getHttpStatusCodesByTraffic()
    {
        return $this->data['http_status_codes_by_traffic'] ?? [];
    }

    public function getOperatingSystemsByRequests()
    {
        return $this->data['operating_systems_by_requests'] ?? [];
    }

    public function getOperatingSystemsByTraffic()
    {
        return $this->data['operating_systems_by_traffic'] ?? [];
    }

    public function getBrowsersByRequests()
    {
        return $this->data['browsers_by_requests'] ?? [];
    }

    public function getBrowsersByTraffic()
    {
        return $this->data['browsers_by_traffic'] ?? [];
    }
}
