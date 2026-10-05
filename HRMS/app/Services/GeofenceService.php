<?php

namespace App\Services;

use App\Models\AttendanceLocationModel;

/**
 * Plain-PHP Haversine distance — no MySQL spatial types. Resolves against
 * every active office location for the employee's branch and keeps the
 * nearest one. GPS is never trusted blindly: a request with no coordinates
 * or with poor accuracy is flagged, not silently treated as "inside."
 */
class GeofenceService
{
    private const EARTH_RADIUS_METERS         = 6371000.0;
    private const LOW_ACCURACY_THRESHOLD_M     = 100.0;

    public function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $lat1Rad  = deg2rad($lat1);
        $lat2Rad  = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) ** 2 + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /** @return array{status: string, distanceMeters: ?float, locationId: ?int} */
    public function resolve(?float $lat, ?float $lng, ?float $accuracyMeters, int $branchId): array
    {
        if ($lat === null || $lng === null) {
            return ['status' => 'gps_disabled', 'distanceMeters' => null, 'locationId' => null];
        }

        $locations = (new AttendanceLocationModel(service('tenantContext')->db()))->forBranch($branchId);
        if ($locations === []) {
            return ['status' => 'not_applicable', 'distanceMeters' => null, 'locationId' => null];
        }

        $nearest         = null;
        $nearestDistance = null;
        foreach ($locations as $location) {
            $distance = $this->haversineMeters($lat, $lng, (float) $location['latitude'], (float) $location['longitude']);
            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearest         = $location;
            }
        }

        // Outside the radius is outside, however poor the GPS accuracy — checking
        // accuracy first let anyone with a weak fix (>100m) skip the geofence.
        $status = $nearestDistance <= (float) $nearest['radius_meters'] ? 'inside' : 'outside';

        if ($status === 'inside' && $accuracyMeters !== null && $accuracyMeters > self::LOW_ACCURACY_THRESHOLD_M) {
            $status = 'low_accuracy';
        }

        return ['status' => $status, 'distanceMeters' => round($nearestDistance, 2), 'locationId' => (int) $nearest['id']];
    }
}
