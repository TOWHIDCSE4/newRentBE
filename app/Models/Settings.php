<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;

    protected $table = 'settings';
    protected $fillable = ['code_name', 'code_label', 'code_value', 'code_descr', 'is_active'];


    public static function update_setting($request, $setting)
    {
        if (empty($setting)) {
            $result = [
                'message' => 'Not found',
                'response' => 'error',
            ];
        }
        $updateData = $setting->update([
            'code_name' => $request->code_name,
            'code_label' => $request->code_label,
            'code_value' => $request->code_value,
            'code_descr' => $request->code_descr,
            'is_active' => $request->activeStatus,
        ]);
     
        if (!empty($updateData)) {
            $result = [
                'message' => 'setting update successfully.',
                'response' => 'success',
            ];
        } else {
            $result = [
                'message' => 'setting updated failed.',
                'response' => 'error',
            ];
        }
        return $result;
    }

    public static function get_code_value_by_name($codeName = [])
    {
        $codeValue   = Setting::select('code_value')
            ->whereIn('code_name', $codeName)
            ->where('is_active', 1)
            ->get()->toarray();
        if ($codeValue) {
            if (count($codeValue) > 1) {
                return $codeValue;
            } else {
                $val = $codeValue[0]['code_value'];
                return $val;
            }
        }
        return null;
    }
}
