<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{

    public function index()
    {
        return response()->json(Settings::all());
    }

    public function store(Request $request)
    {
        $setting = new Settings;
        $setting->code_name = request('code_name');
        $setting->code_label = request('code_label');
        $setting->code_value = request('code_value');
        $setting->code_descr = request('code_descr');
        $setting->is_active = request('status');
        $setting->save();
        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $setting,
        ]);
    }


    public function show(Settings $settings)
    {
        //
    }


    public function update(Request $request, Setting $setting)
    {
        $this->validate($request->all, [
            'code_value' => 'required'
        ]);

        $updateData = Settings::update_setting($request, $setting);
        if ($updateData['response'] == 'success') {
            $result = [
                'message' => 'setting updated successfully.',
                'response' => 'success',
            ];
        } else {
            $result = [
                'message' => 'setting updated failed.',
                'response' => 'error',
            ];
        }
        return response()->json($result);
    }

    public function destroy(Settings $settings)
    {
        //
    }
}
