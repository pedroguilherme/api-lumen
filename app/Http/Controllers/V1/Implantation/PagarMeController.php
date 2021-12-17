<?php

namespace App\Http\Controllers\V1\Implantation;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\PagarMeService;

class PagarMeController extends Controller
{
    /*
     * Apenas para implantação ou mudanças futuras
     * */
    public function storePlans()
    {
        try {
            $plans = Plan::all();
            $pagarMeService = new PagarMeService();
            foreach ($plans as $plan) {
                $result = $pagarMeService->storePlan($plan);
            }
        } catch (\Exception $e) {
            dd($e);
        }
    }

    /*
     * Apenas para implantação ou mudanças futuras
     * */
    public function listPlans()
    {
        try {
            $pagarMeService = new PagarMeService();
            $plansPagarMe = $pagarMeService->getPlans();
            foreach ($plansPagarMe as $planPagarMe) {
                $plan = Plan::where('name', $planPagarMe->name)->first();
                if (!empty($plan)) {
                    $plan->external_plan_id = $planPagarMe->id;
                    $plan->save();
                }
            }
            return response($plansPagarMe, 200);
        } catch (\Exception $e) {
            dd($e);
        }
    }
}
