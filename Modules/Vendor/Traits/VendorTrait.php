<?php

namespace Modules\Vendor\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\Vendor\Entities\Rate;
use Modules\Vendor\Entities\Vendor;
use Modules\Vendor\Entities\VendorWorkTime;

trait VendorTrait
{
    public function getVendorTotalRate($modelRelation)
    {
        $rateCount = $modelRelation->count();
        $rateSum = $modelRelation->sum('rating');
        $totalRate = floatval($rateCount) != 0 ? floatval($rateSum) / floatval($rateCount) : 0;
        return $totalRate;
    }

    public function getVendorRatesCount($modelRelation)
    {
        $rateCount = $modelRelation->count();
        return $rateCount;
    }

    public function checkUserRateOrder($id)
    {
        $rate = Rate::where('user_id', auth()->id())
            ->where('order_id', $id)
            ->first();
        return $rate ? true : false;
    }

    public function getOrderRate($id)
    {
        $rate = Rate::where('order_id', $id)->value('rating');
        return $rate ? $rate : 0;
    }

    public function getVendorRate($id)
    {
        $rate = Rate::where('vendor_id', $id)->groupBy('vendor_id')->avg('rating');
        return $rate ? intval($rate) : 0;
    }

    public function isAvailableVendorWorkTime($vendorId, $date = null)
    {
        $date = $date ?? date('Y-m-d H:i');
        // $time = date("h:i A", strtotime($date));
        $dayCode = Str::lower(Carbon::createFromFormat('Y-m-d H:i', $date)->format('D'));
        $workTime = VendorWorkTime::where('vendor_id', $vendorId)->where('day_code', $dayCode)->first();
        if ($workTime) {
            if ($workTime->is_full_day == 1) {
                $check = true;
            } else {
                $check = $this->isTimeBetween($workTime->custom_times);
            }
        } else {
            $check = false;
        }
        return $check;
    }

    private function isTimeBetween($customTimes)
    {
        foreach ($customTimes as $key => $value) {
            $startDate = Carbon::createFromFormat('H:i a', $value['time_from']);
            $endDate = Carbon::createFromFormat('H:i a', $value['time_to']);
            $check = Carbon::now()->between($startDate, $endDate, true);
            if ($check) {
                return true; // In Between
            }
        }
        return false;
    }

    public function checkVendorBusyStatus($vendorId, $date = null)
    {
        $result = [];
        $vendor = Vendor::active()->find($vendorId);
        $checkVendorStatus = $this->isAvailableVendorWorkTime($vendorId);
        if ($vendor) {
            if ($vendor->vendor_status_id == 4) { // busy
                $result = [
                    'status' => __('vendor::webservice.vendors.vendor_statuses.busy'),
                    'flag' => 'busy',
                    'accepting_orders' => false,
                ];
            } else {
                $result = [
                    'status' => $checkVendorStatus == true ? __('vendor::webservice.vendors.vendor_statuses.open') : __('vendor::webservice.vendors.vendor_statuses.closed'),
                    'flag' => $checkVendorStatus == true ? 'open' : 'closed',
                    'accepting_orders' => $checkVendorStatus,
                ];
            }
        }

        return !empty($result) ? $result : [
            'status' => __('vendor::webservice.vendors.vendor_statuses.closed'),
            'flag' => 'closed',
            'accepting_orders' => false,
        ];
    }

    public function calculateVendorCommissions($orderVendors)
    {
        // calculate vendor commissions
        $orderTotal = floatval(getCartTotal());
        $orderVendors = $orderVendors;
        $vendorsTotalCommission = 0;

        $upaymentKnetFixedAppCommission = config('setting.supported_payments.upayment.commissions.knet.fixed_app_commission');
        $upaymentKnetPercentageAppCommission = config('setting.supported_payments.upayment.commissions.knet.percentage_app_commission');
        $upaymentCcFixedAppCommission = config('setting.supported_payments.upayment.commissions.cc.fixed_app_commission');
        $upaymentCcPercentageAppCommission = config('setting.supported_payments.upayment.commissions.cc.percentage_app_commission');

        $upaymentKnetTotalCommission = !is_null($upaymentKnetPercentageAppCommission) ? ($upaymentKnetPercentageAppCommission * $orderTotal) / 100 : 0;
        $upaymentKnetTotalCommission += !is_null($upaymentKnetFixedAppCommission) ? $upaymentKnetFixedAppCommission : 0;
        $upaymentCcTotalCommission = !is_null($upaymentCcPercentageAppCommission) ? ($upaymentCcPercentageAppCommission * $orderTotal) / 100 : 0;
        $upaymentCcTotalCommission += !is_null($upaymentCcFixedAppCommission) ? $upaymentCcFixedAppCommission : 0;

        foreach ($orderVendors as $key => $vendor) {
            $vendorCommission = 0;
            $vendorFixedAppCommission = $vendor->payment_data['fixed_app_commission'] ?? 0;
            $vendorPercentageAppCommission = $vendor->payment_data['percentage_app_commission'] ?? 0;
            $vendorSubTotal = floatval($vendor->pivot->subtotal);
            $vendorCommission = !is_null($vendorPercentageAppCommission) ? (floatval($vendorPercentageAppCommission) * $vendorSubTotal) / 100 : 0;
            $vendorCommission += !is_null($vendorFixedAppCommission) ? floatval($vendorFixedAppCommission) : 0;
            $vendorsTotalCommission += $vendorCommission;
        }

        $knetEquation = $upaymentKnetTotalCommission + floatval(getOrderShipping()) + $vendorsTotalCommission;
        $ccEquation = $upaymentCcTotalCommission + floatval(getOrderShipping()) + $vendorsTotalCommission;
        return [
            'knetEquation' => $knetEquation,
            'ccEquation' => $ccEquation,
        ];
    }
}
