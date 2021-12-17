<?php

namespace App\Http\Controllers\V1\PagarMe;

use App\Http\Controllers\Controller;
use App\Models\PagarMePostbacks;
use App\Services\PagarMeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostBackController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function teste(Request $request)
    {
        $pagarmeService = new PagarMeService();
        $return = $pagarmeService->payBoleto('12621938');
        return response()->json($return);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function subscription(Request $request)
    {
        $pagarMe = new PagarMePostbacks();
        $pagarMe->type = 'subscription';
        $pagarMe->postback = json_encode($request->all());
        $pagarMe->save();
        return response()->json($pagarMe);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function transaction(Request $request)
    {
        $pagarMe = new PagarMePostbacks();
        $pagarMe->type = 'transaction';
        $pagarMe->postback = json_encode($request->all());
        $pagarMe->save();
        return response()->json($pagarMe);
    }

    /**
     * @param $model
     * @param $model_id
     * @return JsonResponse
     */
    public function getPostBack($model, $model_id)
    {
        $pagarMe = new PagarMeService();
        if ($model == 'teste1234') {
            return response()->json($pagarMe->getPostBack($model, $model_id));
        } else {
            return response()->json([]);
        }
    }
}
